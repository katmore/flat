# Functional tests with flat-webapp (plan)

Status: draft for the owner's review. Nothing is built yet.

The unit suite tests flat's classes one at a time. These tests answer a
different question: does a real application built on flat still behave when it
runs through a web server? `flat-webapp` is the boilerplate application for flat,
so it is the natural subject.

## What flat-webapp is

Read from `katmore/flat-webapp` (branches `master`, `devel` and `modernize-26`
all point at commit `7186607`). It requires PHP 7.1.1 or newer and
`katmore/flat ^1.0`, and has no tests of its own.

- Two entry points, `web/api.php` and `web/view.php`. Each reads
  `PATH_INFO`, `REQUEST_METHOD` and `CONTENT_TYPE`, loads
  `app/config/routing/{api,view}.php`, and hands over to a controller.
- `AppWeb\ApiController` (JSON API): builds the input map, lets a GET carry
  a `POST`, `PUT` or `DELETE` override, sets flat's API response handler, and
  routes through four rules (`Helper`, `Dependency`, `Resolve`, and a
  catch-all that answers forbidden for any method).
- `AppWeb\ViewController` (HTML): same input handling, a template display
  handler that checks for stray output and headers, a shutdown handler that
  turns fatal errors into a 500 page, and rules `Helper`, `Tmpl`, `NotFound`.
- `AppWeb\InputTrait`: reads `$_GET`, or the raw body as JSON (by content
  type, or implied) or as a query string.
- `ViewTmpl`: maps `view.php/home` to `app/Resources/design/tmpl/view/home.php`,
  falls back to a not-found template when the design does not exist.
- The shipped `AppRoute/Api/Resolve` folder is empty (only `about.txt`), so
  the API has no resources until an application adds some.

## What these tests would cover in flat

- Routing controller and route rules (weights, iterate, traverse, ignore).
- API controller: methods, response handler, status codes, JSON output.
- Input map and request parsing from a real HTTP request.
- Template controller: design lookup, display handler, restful status codes.
- Config controller with a real config directory (`app/config/flat`).
- Events, resolver and consumer interfaces as the application wires them.
- Error paths: unknown view, unknown API resource, malformed JSON, a fatal error.

## Proposed approach

1. Run the application in the existing Docker test container with PHP's
   built-in web server (`php -S`), which supports `PATH_INFO` for
   `web/api.php/Resource`. Tests send real HTTP requests to it.
2. Get the application at a pinned commit (`7186607`), so the tests are
   reproducible and a fresh checkout plus Docker is enough. Install its
   dependencies with Composer inside the container, pointing `katmore/flat`
   at the checkout under test (a Composer `path` repository), so the tests
   always exercise the working tree.
3. Add small test-only fixtures in this repository (a few API resources
   and view templates), overlaid on the application, because the shipped
   application has no API resources.
4. Keep the tests in their own PHPUnit suite (`functional`) and add a
   `docker/bin/test functional` mode. They stay separate from the unit
   suite, which must keep working with no network.
5. Record the same way as the unit work: findings in the known-defect list,
   behavior changes in the consumer-impact document, inventory generated.

## Test list (first pass)

- View: `view.php` and `view.php/home` return 200 HTML; an unknown view returns
  the not-found page with its status code; trailing and doubled slashes
  resolve like the clean path.
- View errors: a template that echoes before the display handler runs is
  aborted with the "unexpected output" message; a fatal error yields 500 with
  no partial page.
- API: a test resource answers GET, POST, PUT and DELETE with the expected
  status and JSON body; a method with no handler answers forbidden.
- API input: query string on GET; JSON body with and without the JSON content
  type; form-encoded body; malformed JSON gives an error response.
- API method override: GET with a `POST`, `PUT` or `DELETE` parameter acts as
  that method and the parameter is removed from the input.
- API errors: unknown resource, exception with a response, non-empty output
  buffer, headers already sent.
- Config: values from `app/config/flat` reach the templates and routes.

## Open questions

These are also in the known-defects list (questions 29 to 31), each with a
recommendation.

- Where do the tests live? Recommend: in this repository under
  a new Functional folder under the tests folder, with the application fetched at a pinned commit at
  image build time, not copied in.
- Is it acceptable that the image build needs the network once to fetch
  the pinned application and its Composer packages? Recommend: yes, the same
  way the PHP and service images are already fetched; the unit suite
  stays offline.
- Which branch of the application is the baseline? Recommend: the pinned
  commit above, since all three branches are identical today.

Out of scope until the owner agrees: changes to flat-webapp itself, and any
PHP version change.
