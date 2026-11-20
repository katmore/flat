# flat v2.72.0

The first release of the flat modernization: flat as it stood in 2020, running on
PHP 7.2, with a full test suite and its known bugs fixed. The version number is
`2.<PHP major><PHP minor>.0`, so this is `v2.72.0` for PHP 7.2; the next phases
will be `v2.73.0`, `v2.74.0`, `v2.80.0`, and so on.

## Read this before upgrading from 1.x

This is not a drop-in replacement for `v1.5.5`. It fixes many bugs, and a fix
changes behavior for any code that relied on, or worked around, the old result.

- Composer's `^1.0` will not pick this release up. To use it, require `2.72.*`.
- Every behavior change is listed with what you saw before, what you see now,
  and what to search your own code for, in `docs/modernization/php-7.2/active/CONSUMER-IMPACT.md`.
  Go through it against your application before upgrading.
- The PHP requirement is unchanged: `>=7.2.1`. Nothing here is meant for PHP 8 yet.

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
  than MySQL; config keys that begin with `flat/` resolve; `rand` objects honor
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
