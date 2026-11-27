# Consumer check against flat-webapp

2026-11-27, on the PHP 7.4.33 test image, with flat-webapp's `php-7.4` branch.

## The scan

`bin/consumer-check` with this phase's impact list ([../active/CONSUMER-IMPACT.md](../active/CONSUMER-IMPACT.md), 3
entries) over `app/` and `web/`: 41 files scanned, no entry has a hit. The `php` entry is too generic to search; its
meaning (flat now needs PHP 7.4 or newer) is a Composer matter and does not touch the application's code.

## flat-webapp on the 7.4 code

flat-webapp locks the published `v1.73.2`. To see how it behaves on this phase's code, a scratch copy of flat-webapp took
flat from a local copy of this branch (`katmore/flat` 1.74.0, from a path repository) and ran its own
`docker/bin/test` on PHP 7.4.33:

- A fresh resolution moved `jms/serializer` to 3.32.9 and `mongodb/mongodb` to 1.20.0, and kept `doctrine/annotations`
  because flat now requires it.
- 66 of 67 tests pass and the server log has no PHP warning, notice or deprecation. The one failure is
  `DependencyLockTest`, which requires the locked framework to be `1.73.x`: it will be updated when flat-webapp's lock
  moves to the published 7.4 release.

The consumer check against the owner's closed-source application is still the owner's task.

## After the release

flat-webapp locks the published `v1.74.0` (its `DependencyLockTest` expects 1.74.x). The packages of its application lock
(`doctrine/deprecations`, `jms/serializer`, `phpstan/phpdoc-parser`, `mongodb/mongodb`) and of its test-tool lock (`nikic/php-parser`)
were moved to the newest releases PHP 7.4 allows, one at a time; its 67 tests pass after each and its server log has no PHP message.
The details are in flat-webapp's `CHANGELOG.md`.

## Adversarial fixes (MOD-333 to MOD-335)

The sample app at `9e6fb73` was tested with the modified framework bind-mounted
into its vendor directory in both the web and test containers. Its tracked lock
and application source were unchanged. The Compose functional suite passed
67 tests and 203 assertions; the PHP server-log check passed. This run exercises
the patched source, rather than only the published v1.74.0 package.
