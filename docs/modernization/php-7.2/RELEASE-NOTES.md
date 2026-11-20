# flat v2.72.0

The first release of the flat modernization: flat as it stood in 2020, running on
PHP 7.2, with a full test suite and its known bugs fixed. The version number is
`2.<PHP major><PHP minor>.0`, so this is `v2.72.0` for PHP 7.2; the next phases
will be `v2.73.0`, `v2.74.0`, `v2.80.0`, and so on.

## Upgrading from 1.x: a drop-in replacement

v2.72.0 is meant to be a drop-in replacement for `v1.5.5`, and the public API is the same.
Every class, method, property and constant of the two versions was compared with PHP's
Reflection (parameter types, defaults, return types and constant values included): nothing
that worked in `v1.5.5` was removed, and no parameter or return type changed. The only
differences are one unfinished, thread-based alert listener that could not run without the
pthreads extension (it is no longer declared), a few default and constant values changed
by bug fixes (below), and one protected method that became `static`. The comparison is in
`docs/modernization/php-7.2/notes/API-COMPARISON.md`. The PHP requirement is unchanged: `>=7.2.1`.

What did change is behavior, almost all of it bug fixes: things that did not work
now work as documented. That is the point of the release, and it means a result that
code used to get may be different. Most applications will not notice. To be sure,
skim the list below, and if you want detail, every change is listed with what you saw
before, what you see now, and what to search your own code for in
`docs/modernization/php-7.2/active/CONSUMER-IMPACT.md`.

- Composer's `^1.0` will not pick this release up on its own. To use it, require
  `2.72.*`, run your tests, and you are done.
- Nothing here is meant for PHP 8 yet.

## The changes most likely to affect you

- XML encoding: format version 0.3 adds type and key attributes and a working
  decoder (a wire-format change). Strings are labeled `xs:DateTime` only when
  they look like ISO-8601 dates, and `xs:hexBinary` only for whole bytes.
- Collections: iteration keys, `range()`, falsy copied values, and
  constraint and validator enforcement now work as documented.
- Data mapper: `strings_to_scalar(true)` converts only numeric strings; defaults
  and `value_on_empty` apply.
- HTTP and API: JSON requests send `Content-Type: application/json`; an API
  resource that does not implement the requested method now answers
  `405 Method Not Allowed` through the response handler (it used to produce an
  empty 200 in applications whose handler writes the response).
- Templates: without `design/root_dir` configured, the fallback design folder is
  now `app/Resources/design` (it never resolved before).
- Others: shutdown callbacks at the same priority all run; Mongo eager
  connections are pinged and retried; PDO `prepare_map` works on drivers other
  than MySQL; config keys that begin with `flat/` resolve; `alert::trigger()` now throws `BadMethodCallException` straight away (it used to always end in an `Error`); `rand` objects honor
  their bounds when invoked without arguments.

## What else is in it

- 79 known defects found by the new test suite are resolved (fixed, already
  fixed, or confirmed as intended): `docs/modernization/php-7.2/active/KNOWN-DEFECTS.md`.
- 1,428 unit tests that pass on PHP 7.2.34 with 100% class, method and line
  coverage, and a mutation probe that checks the tests can fail.
- A reproducible test setup: `docker/bin/test` runs everything in Docker from a
  fresh checkout; see `docs/modernization/TESTING.md`.
- Every change, with its reason and how to undo it, is in
  `docs/modernization/php-7.2/CHANGELOG.md`.

## Known limits

- A `405` response does not yet carry an `Allow` header listing the supported methods.
- Integration tests against an application (flat-webapp) are not written yet.
- The sample application `flat-webapp` is a separate repository, repaired
  alongside this work on its own `php-7.2` branch.
