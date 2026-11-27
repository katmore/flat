# flat v1.74.0

The PHP 7.4 release of the flat modernization (`v1.<PHP major><PHP minor>.0`). The public API is the same as `v1.73.2`:
every class, method, property and constant was compared with PHP's Reflection and nothing differs
(`docs/modernization/php-7.4/notes/API-COMPARISON.md`). 1,637 tests pass on PHP 7.4.33 with 100% class, method and line
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
- Seven classes cannot be loaded by the API tool (`docs/modernization/php-7.4/notes/API-COMPARISON.md`).
