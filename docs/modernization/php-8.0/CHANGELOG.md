# PHP 8.0 change log

## MOD-341 — Start the PHP 8.0 phase (2026-11-27)

Owner authorized PHP 8.0 and aggressive compatible syntax, tooling and dependency
updates. Fetched the existing php-8.0 branch at 11b5381 and checked it out.
Added the phase work list and carried unresolved mutation and private checks forward.
Master is untouched. Validation is recorded as each migration change lands.

## MOD-342 — Use HTTPS for the SSH fixture mirror (2026-11-27)

The HTTP mirror returned 403 before the baseline could run. Use HTTPS and the
existing optional build secret for proxy trust; no certificate verification is disabled.
The secret is read only during package installation and is not copied into an image.

## MOD-343 — Pin the PHP 8.0 runtime (2026-11-27)

The Docker target is PHP 8.0.30, pinned by manifest digest. Both test Composer
platforms move to 8.0.30 without changing package versions. Composer regenerated
the lock metadata and download URLs. The application requirement becomes >=8.0.0.
The build continues to verify the exact PHP version, E_ALL and every extension.
Full-suite results will be recorded after the image builds.

## MOD-344 — Preserve positional event callbacks (2026-11-27)

Runtime compatibility, no intended behavior change. PHP 8 treats string keys
passed to call_user_func_array as named parameters. Normalize only the outer
handler argument list with array_values; aggregated payload keys remain intact.
The original tests exposed seven errors and one variadic-key mismatch on PHP 8.
Five regression cases cover absent, call, event, stored and aggregated data.

### MOD-344 validation

The event/debug/facade regression selection passes unchanged on both PHP 7.4.33
and PHP 8.0.30: 43 tests, 116 assertions. The inherited PHP 7.4 unit baseline,
before the fix, passed 1,496 tests and 6,823 assertions. A fresh API baseline
contains 602 loadable entries. The inventory was regenerated for the five new cases.
