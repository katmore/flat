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
