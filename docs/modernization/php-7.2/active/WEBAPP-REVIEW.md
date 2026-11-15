# flat-webapp review (2026-11-15)

A read-through of `katmore/flat-webapp` (commit `7186607`, the tip of its
`master`, `modernize-26` and the new `php-7.2` branch) and a trial run, done
before building any functional tests on it. The owner suspected it was only
ever half finished. It was.

## How it was run

- A scratch copy of the application, a stand-in autoloader with the same
  PSR-4 maps Composer would produce (flat from this checkout, `AppRoute\`
  and `AppWeb\` from the application), and PHP's built-in server inside the
  PHP 7.2.34 test container. Nothing was changed in either repository.
- Requests: `view.php`, `view.php/home`, `api.php`, `api.php/Foo`.

## Results as shipped

- Views are broken. `view.php` and `view.php/home` answer HTTP 500 with the
  message "no resolution for resource" (raised by `ViewController`). No
  route class can be found.
- The API only ever answers forbidden. `api.php` and `api.php/Foo` return
  403 `{"message":"forbidden resource","data":null}`. That is the catch-all
  rule working; the real route namespace cannot be found either, and the
  shipped `Resolve` folder holds no resources anyway.

## Why: a half-finished rename

The application was cut out of another project ("SpyDash", "MiceSpy Dash")
and only partly renamed to `App...`.

- `app/config/routing/view.php` and `api.php` return the namespaces
  `SpyDashRoute\View\` and `SpyDashRoute\Api\`. Composer maps only
  `AppRoute\` and `AppWeb\`, so those classes can never load.
- `app/AppRoute/View/Meta.php`, `Tmpl.php` and `NotFound.php` declare
  `namespace SpyDashRoute\View` and extend `SpyDashWeb\...` classes, which do
  not exist (the real ones are in `AppWeb`).
- The templates `view.php`, `widget/script.php`, `error/minimal/*.php` import
  `SpyDashWeb\...` and `SpyDashRoute\...` classes.
- `app/config/flat/App/AssetUrl.php` and `ComponentsUrl.php` still say
  `SpyDash` in their comments.

## Other defects found

- `AppWeb\Asset` calls `config::get()` without importing `\flat\core\config`
  and reads `self::fallback_base` although the constant is `FALLBACK_BASE`.
  Nothing uses this class yet, so it fails only when someone does.
- `widget/script.php` loads `js/WebApp.js`; the file is `AppWeb.js`.
- The view template needs jQuery and Croppie from `web/components`, but
  `bower.json` lists only Bootstrap. `ComponentsUrl` also points its system
  path at `web/components` relative to the wrong folder (one `..` short).
- `view.php` still has the old product name and navigation links
  ("MiceSpy Dash", "Batch Cropper"); `home.php` says "My Web App".
- `NotFoundTmpl.php` and `AppRoute/View/NotFound.php` carry a header that
  says "NO LICENSE GRANTED", contradicting the MIT/GPL licence of the project.
  They also reference `\flat\app\event\error` and `\flat\app\event\meta\app`,
  which do not exist in flat (the error page code checks with `class_exists`
  first, so it degrades quietly).
- Line endings are CRLF in the PHP files; `ErrorTrait` and `HtmlErrorTrait`
  read `error_get_last()['type']` without checking for `null`, which is a
  notice on PHP 7.4 and later (a later phase will meet this).
- The README paths (`/webapp/app/AppRoute/Api`, `AppRoute\Api\Resolve`)
  describe a layout that no longer matches the code.
- `composer.json` asks for `katmore/flat ^1.0`; flat's tags run 1.0.x to 1.4.6,
  so Composer would install an old release, never the php-7.2 work.
- No tests, no Composer lock (ignored), and the `.gitignore` hides every
  dotfile except itself and `.bowerrc`, which does not exist.

## What works

- The API entry point, input parsing, response handler, and the forbidden
  fallback all execute under PHP 7.2.34 with this checkout of flat.
- The error page machinery runs and produces a readable failure.

## Consequence for the functional tests

The application has to be repaired before it can be a test subject: with the
namespaces fixed, views and the API need a real resource to exercise. See
[WEBAPP-TESTS.md](WEBAPP-TESTS.md) and questions 32 and 33 in
[KNOWN-DEFECTS.md](KNOWN-DEFECTS.md).

## Repairs made (2026-11-15)

The owner asked to make flat-webapp work with flat first, and to put
functional tests on hold until the owner has run it. All changes are on the
`php-7.2` branch of `katmore/flat-webapp`, in separate commits:

- Finished the rename: routing config, route classes, templates and entry-point
  comments now use `AppRoute` and `AppWeb`. Removed the MiceSpy name, its
  navigation links and the Croppie dependency from the view; the script
  file name is `AppWeb.js`.
- `design.php` now sets `root_dir` (the key flat reads) to the folder that
  contains `tmpl`; `ComponentsUrl` points at `web/components`; `AppWeb\Asset`
  imports `config` and uses the right constant; `.bowerrc` puts Bower packages
  in `web/components`.
- The not-found page used two flat classes that do not exist; it now uses
  `AppWeb\ViewMetaEvent` and a new `AppWeb\ErrorEvent`.
- Added an example API resource, `Hello`, because the API had none.
- Added Docker tooling: the `serve` script in the docker folder installs dependencies with
  Composer and Bower in containers and serves the application with Apache and
  PHP 7.2 at `http://localhost:8080`. `composer.json` now requires flat from its
  `php-7.2` branch and pins the platform PHP version. Details are in that
  repository's README.

Checked from a fresh clone: home view 200, unknown view 404, `Hello` 200,
unknown API resource 403, Bootstrap and the application's CSS served, and
`--flat` (a local flat checkout) works.

Not repaired, because they are not on the path a visitor takes: the "branded"
error templates still name nonexistent `\flat\app\...` classes; the "NO LICENSE
GRANTED" headers (question 33); CRLF line endings; the `error_get_last()` null
reads that matter from PHP 7.4.

## Findings about flat itself

- `\flat\core\controller\tmpl::get_design_root_dir()` falls back to the
  folder `app/Resources/design/tmpl`, but the design lookup then adds its own
  `tmpl` prefix, giving `.../design/tmpl/tmpl/...`. Without configuration no
  template can be found. flat-webapp works around it by setting
  `design/root_dir`. Recorded as KD-78 (question 34).
- An API resource that implements only `GET` answers `POST`, `PUT` and
  `DELETE` with an empty `200`, not a method-not-allowed response. Recorded as
  KD-79 (question 35).

