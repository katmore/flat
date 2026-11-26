# Session handoff, php-7.4

Written 2026-11-26, to continue the phase in another session. The plan and rules are in
[../HANDOFF.md](../HANDOFF.md); this page only says where things stand. When it is used up, move it to
`history/`.

## Branches

- flat: the owner created `php-7.4` at `673df9f` (`master`, `v1.73.2`). All work so far is pushed there.
- flat-webapp: the owner created `php-7.4` at `bcb91ec` (its `master`). Nothing has been changed on it.

## Done

- MOD-280: the phase folder; php-7.3 marked complete; phase pointers in `AGENTS.md`.
- MOD-281: the test image is PHP 7.4.33.

## Results

- PHP 7.3.33, at `673df9f`, with coverage: 1,624 tests, 6,954 assertions, all pass; 100% (332 classes,
  1,099 methods, 8,821 lines).
- PHP 7.4.33: 1,624 tests, 6,902 assertions, 3 failures (all `Tools/ApiDumpTest`).
- `php -l` on all 754 tracked PHP files under 7.4.33: no messages. This includes the vendored libraries, so no
  compile-time deprecation (nested ternary, curly-brace offset) is left. Run-time deprecations show only where
  a test reaches the code.
- Both image digests in the handoff match `php:7.4-cli` and `php:7.4-apache`.
- Not run on 7.4: coverage, `docker/bin/test-javascript`, `docker/bin/test sandbox`.

## Next

1. Fix the one failure cause: `type_string()` in `tests/tools/apidump.php` casts a `ReflectionType` to string.
   The new text must match what 7.3 produced, or the comparison with earlier dumps
   (`docs/modernization/php-7.3/notes/API-V1.73.2.json`) will report false differences. Dump the same source
   and compare to prove it.
2. Move the platform pins in both `tests/docker/*/composer.json` to 7.4.33, re-lock, and update the lines in
   [../../TESTING.md](../../TESTING.md) that say the locks are for PHP 7.3.
3. Run `docker/bin/test all --coverage` and `docker/bin/test-javascript`.
4. Check what tests may not reach. `array_key_exists()` on an object is deprecated: review its calls in
   `src/flat` (`orm.php`, `core/debug.php`, `core/controller/api.php`, `core/util/validate.php`,
   `core/util/validate/value.php`, and the others a search finds). Then read the PHP manual's
   [incompatible changes](https://www.php.net/manual/en/migration74.incompatible.php) and
   [deprecations](https://www.php.net/manual/en/migration74.deprecated.php) against `src`.
5. Continue with step 4 of the plan (tools and dependencies, one at a time), then flat-webapp.
6. Write `notes/ENVIRONMENT.md`.

## Waiting for the owner

Q51 (the `php` requirement) and Q52 (release numbering) in [KNOWN-DEFECTS.md](KNOWN-DEFECTS.md). Neither
blocks the next steps. Leave the root `composer.json` alone until Q51 is answered.
