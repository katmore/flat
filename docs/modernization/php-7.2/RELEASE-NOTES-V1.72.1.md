# flat v1.72.1

A patch release of the PHP 7.2 line. The public API is unchanged (only function bodies were
edited, no signature), and the test suite is unchanged in size: 1,428 tests pass with 100%
class, method and line coverage on PHP 7.2.34.

## What changed

flat no longer hides errors from PHP. Two places in the library did:

- `session::start()` called `@session_start()` in a retry loop (up to 100 attempts), so a
  missing or unwritable session store failed without a trace. Each failed attempt now reports PHP's own warning (for example
  `session_start(): open(...) failed: No such file or directory`). The return value and the
  `no_session_var` exception are unchanged.
- The asset system-path code lowered `error_reporting` around `realpath()`. It no longer does. A
  path that does not exist still resolves quietly; where `open_basedir` forbids the path, PHP's
  warning now appears.

A dead commented-out `error_reporting` line was removed from `core/curl/response.php`.

Also changed, outside the library: the test suite and the tools stopped using `@` and
`/dev/null` to hide output; a failure is now asserted or reported.

## Upgrading from v1.72.0

Nothing to change in your code. If your application turns PHP warnings into exceptions, or
alerts on them, a failed session start now reaches that handler: check that your session
storage is writable. Details are in the consumer-impact document, entries MOD-180.

## Known limits

- A `405` response has no `Allow` header.
- Four dependency advisories in the test environment cannot be fixed on PHP 7.2
  (`docs/modernization/php-7.2/notes/DEPENDENCY-ALERTS.md`).
