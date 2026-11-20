# flat v1.72.1

A patch release of the PHP 7.2 line. The public API is unchanged (only function bodies were
edited, no signature), and the test suite is unchanged in size: 1,428 tests pass with 100%
class, method and line coverage on PHP 7.2.34.

## What changed

The project adopted a rule that nothing may hide an error from PHP. flat's own source was
audited.

- `session::start()` called `@session_start()` in a retry loop (up to 100 attempts), so a
  missing or unwritable session store failed without a trace. It now calls `session_start()`
  plainly, and each failed attempt reports PHP's own warning (for example
  `session_start(): open(...) failed: No such file or directory`). The return value and the
  `no_session_var` exception are unchanged.
- The absolute path code of `asset` keeps its `error_reporting` mask around `realpath()`: an
  exception approved by the maintainer, marked by a comment block in the code.
- A dead commented-out `error_reporting` line was removed from `core/curl/response.php`.
- Outside the library, the test suite and the tools stopped using `@` and `/dev/null` to hide
  output; a failure is now asserted or reported.

## Upgrading from v1.72.0

Nothing to change in your code. If your application turns PHP warnings into exceptions, or
alerts on them, a failed session start now reaches that handler: check that your session
storage is writable. Details are in the consumer-impact document, entry MOD-184.

## Known limits

- A `405` response has no `Allow` header.
- Four dependency advisories in the test environment cannot be fixed on PHP 7.2
  (`docs/modernization/php-7.2/notes/DEPENDENCY-ALERTS.md`).
