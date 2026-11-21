# flat v1.73.0

The PHP 7.3 release of the flat modernization (`v1.<PHP major><PHP minor>.0`). The public API is
the same as `v1.72.0`: every class, method, property and constant was compared with PHP's
Reflection and nothing differs (`docs/modernization/php-7.3/notes/API-COMPARISON.md`). 1,432 tests
pass on PHP 7.3.33 with 100% class, method and line coverage.

## The PHP requirement changed

`composer.json` now requires `php >=7.3.0` (it was `>=7.2.1`). Composer skips this release on a
PHP 7.2 host and installs `v1.72.x` there, so nothing breaks. On PHP 7.4 or later it installs
`v1.73.x`, which is tested on 7.3 only until later phases are released.

## What changed

- **JavaScript minifier (bug fix).** `\flat\vendor\tedious\JShrink\minifier` lost a character after
  a regex literal whose first character is a quote: `return /'a'/.test(s);` came out as
  `return /'a'/test(s);`. Every character is kept now. Re-minify files that contain such a regex.
- **Two exception messages are complete.** `bad_dim` says why (`bad dimension specified: width must
  be numeric.`) and the mapper's `cannot_get_property` names the property. Same classes, same
  conditions; look for code that matches the old text.
- **Notices and deprecations fixed, no behavior change.** The old test image hid them, so
  `v1.72.x` raised them too in an application running at `E_ALL`: a non-static method called
  statically, undefined variables and indexes, `each()`, `strpos()` with a `false` needle, and
  warnings when XML-encoding a resource. All are fixed at their cause, with the same output.
- One small modernization (`array_key_first()`), with no change a caller can see.

Every entry has what you saw before, what you see now, and what to search for in
`docs/modernization/php-7.3/active/CONSUMER-IMPACT.md`.

## Tests and tooling

- The test image now reports `E_ALL` and PHPUnit fails on notices, warnings and deprecations, so
  none can hide again. Line endings are LF everywhere (`.gitattributes`).
- PHP 7.3.33 and every tool and image digest are recorded in
  `docs/modernization/php-7.3/notes/ENVIRONMENT.md`.

## Known limits

- Two tests discard an AWS SDK deprecation notice and one test helper discards the Authorize.Net
  SDK's, as exceptions the maintainer approved (`AGENTS.md`). The first goes with PHP 8.1; the
  second with the port planned for v2.X (`docs/modernization/DEFERRED-V2.md`).
- The four open dependency advisories (test environment only) need PHP 8.1 for their fixes
  (`docs/modernization/php-7.3/notes/DEPENDENCY-ALERTS.md`).
- Seven classes cannot be loaded, as in `v1.72.0` (listed in
  `docs/modernization/php-7.2/notes/API-COMPARISON.md`).
