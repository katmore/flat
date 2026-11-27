# flat v1.74.0

The PHP 7.4 release of the flat modernization (`v1.<PHP major><PHP minor>.0`). The public API is the same as `v1.73.2` except
the renames and removals below, which concern classes that could not be loaded or were only names made by `class_alias`. Every
class, method, property and constant was compared with PHP's Reflection (`docs/modernization/php-7.4/notes/API-COMPARISON.md`). 1,637 tests pass on PHP 7.4.33 with 100% class, method and line
coverage, and no PHP notice, warning or deprecation is raised.

## The requirements changed

- **PHP 7.4 or newer.** `composer.json` now requires `php >=7.4.0` (it was `>=7.3.0`). Composer skips this release on a
  PHP 7.3 host and installs `v1.73.x` there, so nothing breaks.
- **`doctrine/annotations ^2.0` is required.** The Authorize.Net SDK uses `jms/serializer`, whose newer releases no
  longer require it and, on PHP below 8.0, fail when the SDK builds its serializer. A fresh install on PHP 7.4 would
  have had a broken Authorize.Net client. Composer prints that the package is abandoned; it has no replacement.

## What changed

- **MongoDB read preferences, no behavior change.** ext-mongodb 1.20 and newer deprecate what the archived
  `alcaeus/mongo-php-adapter` does for every database object. flat now carries a corrected copy of that one trait
  (string modes in place of integer constants) and loads it ahead of the adapter's. Same modes, same tags, no
  deprecation on any extension version.
- One thing you may see: an optimized autoloader (`composer dump-autoload -o`, `-a`) prints "Ambiguous class
  resolution ... the first will be used" for that trait. flat's copy is the one used. To silence the warning, add the
  adapter's file to `exclude-from-classmap` in your `composer.json`; the exact line is in
  `docs/modernization/php-7.4/active/CONSUMER-IMPACT.md`.
- **One unusable class is removed.** `flat\core\util\validate\collection` was an empty class that implemented an
  interface that never existed, so it could not be loaded; the file is gone. Nothing could have used it.
- **The MongoDB SSL classes are renamed to match their paths (breaking rename, no alias).** Seven classes declared
  `flat\db\mongo\ssl...` in files under `db/driver/mongo/ssl*`, so they could not be autoloaded. They are now
  `flat\db\driver\mongo\ssl...`. flat did not use them; code that extended the old names must use the new ones.
- **Five `class_alias` names are gone; the classes keep one name each.** flat no longer uses `class_alias` at all (a rule in
  `AGENTS.md`). Search your code for the old names:
  - `flat\cloud\authorize\subscription\cancelled` is now `...\subscription\canceled`.
  - `flat\core\ssh\exception\input_error\badParam` is now `...\input_error\bad_param`.
  - `flat\data\session\driver\session` and `flat\data\session\driver` are gone; use `flat\data\orm\driver\session` and
    `flat\data\orm\driver`.
  - `flat\core\util\factory\exception\not_match_interface` is gone; use `flat\core\util\validate\factory\exception\not_match_interface`.
- **Two more classes now load under the name their file gives.** `flat\fsm\fsm` is now `flat\fsm` and `flat\orm\invalid_param` is now
  `flat\orm\invalid_field`. Neither could be loaded before and flat did not use them.
- **Four more classes are declared at their own path (breaking renames, no alias).** `flat\api\header\exception` and
  `...\missing_request_field` are now `flat\api\response_header\...`; `flat\cloud\authorize\customer\payment_profile\creditCard` and
  `bankAccount` are now `flat\cloud\authorize\customer\payment\...` (deprecated, going in v2.X). One-line forwarding files that made
  the old names load are deleted.
- **Three cURL bugs fixed**, found by tests that now use a real HTTP server: a request with data now really sends its body (it
  used to send `Content-Length` and no body, so the server waited), `filetime` is the server's `Last-Modified` time (it was always
  -1), and `is_robots_allowed()` keeps the port when it fetches robots.txt. The consumer impact says what to search for.
- `ipinfodb` has a protected `_get_api_base_url()` hook (the constant stays the default); nothing changes unless you override it.
- **Eight files that declared nothing are deleted**: `cloud/disqus.php`, five under `cloud/pci/`, `orm/missing_required_field.php` and
  `event/alert/listener.php` (the retired pthread listener, kept as comments). Nothing used them.
- Nothing else in `src` changed.

Every entry has what you saw before, what you see now, and what to search for in
`docs/modernization/php-7.4/active/CONSUMER-IMPACT.md`.

## Tests and tooling

- PHP 7.4.33 and every tool and image digest are recorded in `docs/modernization/php-7.4/notes/ENVIRONMENT.md`: every
  tool, service and dependency of the test setup is at the newest release PHP 7.4 allows (MongoDB extension 1.20.1,
  `mongodb/mongodb` 1.20.0, `jms/serializer` 3.32.9, `nikic/php-parser` 5.9.0 and more).
- Two tests that captured PHP messages loosely (session storage failure, asset URL failure) now assert the exact
  messages, so a new message from a newer PHP fails them.

## Known limits

- Two tests discard an AWS SDK deprecation notice and one test helper discards the Authorize.Net SDK's, as exceptions
  the maintainer approved (`AGENTS.md`). The first goes with PHP 8.1; the second with the port planned for v2.X
  (`docs/modernization/DEFERRED-V2.md`).
- The four open dependency advisories (`mongodb/mongodb`, `aws/aws-sdk-php`; test environment only) need PHP 8.1 for
  their fixes (`docs/modernization/php-7.4/notes/DEPENDENCY-ALERTS.md`). An application on PHP 8.1 or newer can already
  install the fixed releases; flat's constraints allow them.
- Every class in `src` loads, and the API comparison describes all of them. The API tool discards the Authorize.Net SDK's own deprecation notice
  while describing the two wrappers (an exception approved in `AGENTS.md`); the wrappers go in v2.X.
  `tests/Unit/CodingRulesTest.php` checks that every file declares the class its path names.
