# flat v1.72.1

A patch release of the PHP 7.2 line. The public API is unchanged (only function bodies were
edited, no signature), and the test suite is unchanged in size: 1,428 tests pass with 100%
class, method and line coverage on PHP 7.2.34.

## What changed

Library behavior is the same as v1.72.0. Nothing a consumer sees changes.

- The project adopted a rule that nothing may hide an error from PHP. flat's own source was
  audited. Two places of the original code hide notices and warnings: the absolute path code of
  `asset` (around `realpath()`) and the session start retry loop (around `session_start()`). The
  maintainer approved keeping both. Each now carries a comment block that says so; the session
  start uses the same `error_reporting` mask as the asset code, restored right after the loop,
  where it used `@` before.
- A dead commented-out `error_reporting` line was removed from `core/curl/response.php`.
- Outside the library, the test suite and the tools stopped using `@` and `/dev/null` to hide
  output; a failure is now asserted or reported.

## Upgrading from v1.72.0

Nothing to change in your code.

## Known limits

- A `405` response has no `Allow` header.
- Four dependency advisories in the test environment cannot be fixed on PHP 7.2
  (`docs/modernization/php-7.2/notes/DEPENDENCY-ALERTS.md`).
