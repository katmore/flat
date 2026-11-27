# Change log, php-7.4 phase

Every change of this phase: what, why, effect on behavior, how it was verified, and how to undo it. Numbering
continues from the php-7.3 phase.

## MOD-280 — The php-7.4 phase folder

- Kind: documentation. `docs/modernization/php-7.4/` has the same skeleton as the earlier phases; the owner's
  brief for the phase is saved there as `HANDOFF.md`. The Progress list of `docs/modernization/README.md`
  marks php-7.3 complete and php-7.4 in progress. `AGENTS.md` points to this phase: its work plan, the PHP
  version (7.4) and the branch to push to. No rule changed.
- The owner created flat's `php-7.4` branch at `673df9f` (`master`, `v1.73.2`).
- Behavior: none.
- Verified: on `673df9f` before any change, `docker/bin/test all --coverage` passed 1,624 tests and 6,954
  assertions on PHP 7.3.33; `docker/bin/check-docs`.
- Undo: revert the commit.

## MOD-281 — The test image moves to PHP 7.4.33

- Kind: environment. `docker/Dockerfile` builds on `php:7.4-cli`, pinned
  `sha256:620a6b9f4d4feef2210026172570465e9d0c1de79766418d3affd09190a7fda5`, and checks for PHP 7.4.33. The
  image is `flat-tests-php74`. `tests/Unit/PhpVersionTest.php` requires 7.4. Documents and script comments that
  name the runtime say 7.4. Extensions, locks, Composer platform pins (still 7.3.33) and the root
  `composer.json` are unchanged.
- Behavior: none (no library code changed).
- Verified: the image builds and its check passes (PHP 7.4.33, PCRE 10.35, `error_reporting` `E_ALL`).
  `docker/bin/test all`: 1,624 tests, 6,902 assertions, **3 failures**, all in `Tools/ApiDumpTest`, because
  `tests/tools/apidump.php` uses the deprecated `ReflectionType::__toString()`; not fixed yet. `php -l` on all
  754 tracked PHP files reports nothing (a sample with a nested ternary and a curly-brace offset does report).
  Coverage and `docker/bin/test-javascript` were not run on 7.4.
- Undo: revert the commit.

## MOD-282 — The API dump tool reads types without the deprecated string cast

- Kind: runtime compatibility (test tooling). `type_string()` in `tests/tools/apidump.php` cast a
  `ReflectionType` to a string, which PHP 7.4 deprecates. It now calls `ReflectionNamedType::getName()`, which
  gives the same text on PHP 7.1 and newer (the leading `?` for a nullable type is still added by the tool).
- Behavior: none for flat; the tool's output is unchanged.
- Verified: under PHP 7.4.33 the fixed tool dumped `src` and `compare` against
  `../php-7.3/notes/API-V1.73.2.json` (made on 7.3.33) reports no differences. The two files differ in one
  stored message only: the fatal error text for `flat\core\util\validate\collection`, a class that cannot
  load (already so in the 7.3 dump), which PHP 7.4 words "Uncaught Error: Interface ... not found". No
  declaration differs. `docker/bin/test all` on 7.4.33: 1,624 tests, 6,935 assertions, all pass, no notice or deprecation. (7.3.33
  counted 6,954 assertions; the 19 fewer are not explained yet and are listed in the session handoff.)
- Undo: revert the commit.

## MOD-283 — The Composer platform pins move to PHP 7.4.33

- Kind: environment. `config.platform.php` is `7.4.33` in `tests/docker/tools/composer.json` and
  `tests/docker/runtime/composer.json`; both locks were refreshed with `composer update --lock`, which changes
  only the content hash, the recorded platform and Composer's plugin API version. No package moved: the
  newer releases that 7.4 allows come in the next entries, one at a time. The `TESTING.md` lines that said
  the locks were for PHP 7.3 now say 7.4.
- Behavior: none.
- Verified: the image rebuilds from the locks and `docker/bin/test all` passes with the same counts as MOD-282.
  `docker/bin/check-docs`.
- Undo: revert the commit.

## MOD-284 — flat on PHP 7.4.33: full verification and source review

- Kind: verification; no file of `src` changed.
- Run on 7.4.33 (the pins of MOD-283): `docker/bin/test all --coverage`: 1,624 tests, 6,954 assertions, all
  pass, no notice, warning or deprecation; coverage 100% (332 classes, 1,099 methods, 8,821 lines), the same
  as 7.3.33. (Without `--coverage` the count is 6,935: 19 assertions sit in tests that run only under
  coverage.) `docker/bin/test-javascript`: 45 minifier programs preserve their JavaScript results.
- Source review against the PHP manual's
  [incompatible changes](https://www.php.net/manual/en/migration74.incompatible.php) and
  [deprecations](https://www.php.net/manual/en/migration74.deprecated.php), for what the tests may not reach:
  - every `array_key_exists()` and `key_exists()` call in `src` (`orm.php`, `asset.php`, `tmpl/data.php`,
    `core/config.php`, `core/debug.php`, `core/controller/api.php`, `core/controller/asset/`,
    `core/util/validate.php`, `core/util/validate/value.php`, `.../preg_error.php`) takes an array as its
    second argument (type-hinted, guarded by `is_array`, or a literal), never an object;
  - no use of `ReflectionType` as a string, `money_format`, `hebrevc`, `convert_cyr_string`, `ezmlm_hash`,
    `restore_include_path`, `fgetss`, `get_magic_quotes_*`, `FILTER_SANITIZE_(STRING|STRIPPED)`, `(real)`,
    `is_real`, `each`, `create_function`, Reflection `export()` or a legacy-order `implode()`;
  - the one nested ternary, `tmpl/data.php:86`, is parenthesized;
  - `php -l` on all tracked PHP files was clean (MOD-281).
- Behavior: none.
- Undo: nothing to undo.

## MOD-285 — The MongoDB extension moves to 1.19.4

- Kind: environment. `docker/Dockerfile` builds ext-mongodb 1.19.4 (commit
  `bcb0a495a568243b331692443a04cdcc9cc9c205`, with libmongoc `32898b16f8236470ad5610bf0f0b0071bee6a1b4`) in place
  of 1.16.2; the `ext-mongodb` pin in `tests/docker/runtime/composer.json` is `1.19.4` and the lock is
  refreshed (hash and platform only).
- Correction to the phase brief ([../php-7.4/HANDOFF.md](HANDOFF.md)): it says 1.21.1 runs on PHP 7.4. It does
  not: from 1.21.0 the extension's `config.m4` requires PHP 8.1 (the build stops with "Need a PHP version
  >= 8.1.0"). The newest release that builds on 7.4 is 1.20.1.
- Why not 1.20.1: from 1.20.0 the extension deprecates an integer `ReadPreference` mode and
  `ReadPreference::getMode()`. `alcaeus/mongo-php-adapter` 1.2.5 (newest, archived) uses both in every
  database object it creates. With 1.20.1, 47 tests error and 1 fails on those deprecations (all in
  `Db/Mongo`). The cause is outside flat and cannot be fixed there; nothing is hidden. See Q53 in
  [active/KNOWN-DEFECTS.md](active/KNOWN-DEFECTS.md).
- Behavior: none for flat's code.
- Verified: `docker/bin/test all --coverage` on the 1.19.4 image: 1,624 tests, 6,954 assertions, all pass;
  100% coverage (332 classes, 1,099 methods, 8,821 lines). With 1.20.1 the same run: 47 errors, 1 failure.
- Undo: revert the commit.

## MOD-286 — `mongodb/mongodb` 1.16.1 to 1.19.1

- Kind: environment (test dependency). `tests/docker/runtime/composer.lock`: `mongodb/mongodb` 1.19.1, the
  newest release that accepts PHP 7.4 and the installed extension 1.19.4 (1.20 needs extension 1.20 or
  newer, MOD-285). `jean85/pretty-package-versions` and `symfony/polyfill-php73` left the lock because the
  library no longer needs them; `psr/log` 1.1.4 entered it.
- Composer 2.11 refuses releases that have security advisories. Every `mongodb/mongodb` release that runs on
  PHP 7.4 has them (the fixed 1.21.5 needs PHP 8.1), as does the 1.16.1 it replaces. The lock was resolved
  with `composer update --no-security-blocking` on the command line; no ignore entry is committed. These are
  the advisories the owner dismissed as "risk tolerable" (php-7.3 dependency-alert note). The library is a
  test dependency.
- Behavior: none for flat's code.
- Verified: `docker/bin/test all --coverage`: 1,624 tests, 6,954 assertions, all pass; 100% coverage.
- Undo: revert the commit.

## MOD-287 — `jms/serializer` 3.32.9 and its dependencies

- Kind: environment (test dependency). `tests/docker/runtime/composer.lock`: `jms/serializer` 3.29.1 to
  3.32.9, `doctrine/deprecations` 1.1.6 to 1.2.0 and `phpstan/phpdoc-parser` 1.33.0 to 2.4.2 (the 7.4 step
  unlocked them). `doctrine/annotations` 2.0.2 and `psr/cache` stay locked as before.
- The new `jms/serializer` no longer requires `doctrine/annotations`; on PHP below 8.0 it throws
  "either ... the doctrine/annotations package installed, or use PHP 8.0 or later" (the Authorize.Net SDK
  builds a serializer). So `tests/docker/runtime/composer.json` now lists `doctrine/annotations ^2.0`
  explicitly. The package is marked abandoned on Packagist, as it was before.
- Behavior: none for flat's code.
- Verified: without the explicit requirement 39 tests error and 2 fail (all Authorize.Net); with it
  `docker/bin/test all --coverage`: 1,624 tests, 6,954 assertions, all pass; 100% coverage.
- Undo: revert the commit.

## MOD-288 — `azuyalabs/yasumi` 2.4.0 to 2.6.0

- Kind: environment (test dependency). `tests/docker/runtime/composer.lock`: `azuyalabs/yasumi` 2.6.0 (the
  7.4 step unlocked it; the manifest constraint `^2.4` already allows it).
- Behavior: none for flat's code.
- Verified: `docker/bin/test all --coverage`: 1,624 tests, 6,954 assertions, all pass; 100% coverage.
- Undo: revert the commit.

## MOD-289 — `nikic/php-parser` 4.19.5 to 5.9.0 (tools lock)

- Kind: environment (test tool). `tests/docker/tools/composer.lock`: `nikic/php-parser` v5.9.0, which needs
  PHP 7.4. PHPUnit 9.6 and its `phpunit/php-code-coverage` 9.2 accept it (`^4.18 || ^5.0`); only coverage uses it.
- Effect: the coverage report counts 8,824 executable lines instead of 8,821 (the new parser splits three
  constructs differently); coverage is still 100%.
- Behavior: none for flat's code.
- Verified: `docker/bin/test all --coverage`: 1,624 tests, 6,954 assertions, all pass; 100% (332 classes,
  1,099 methods, 8,824 lines).
- Undo: revert the commit.

## MOD-290 — The environment note for PHP 7.4

- Kind: documentation. `notes/ENVIRONMENT.md` records the test environment on 7.4: what is at its newest
  release and, for each held tool, the reason. A check of the other pins found nothing newer that runs on 7.4:
  ImageMagick 7.1.2-36 and imagick 3.8.1 are the newest releases, and Xdebug 3.1.6 is the last for PHP 7.4.
- Behavior: none.
- Undo: revert the commit.

## MOD-291 — flat-webapp on PHP 7.4.33; Q54 found on the way

- Kind: environment, in flat-webapp (its own `CHANGELOG.md`, section "PHP 7.4 changes"). The web and test images
  are `php:7.4-apache` and `php:7.4-cli`, pinned by digest to 7.4.33; the platform pins and both locks follow
  (hash and platform only). The application code is unchanged. 67 tests and 203 assertions pass; the server
  log has no PHP message.
- Found: a fresh resolution on PHP 7.4 drops `doctrine/annotations`, which flat's Authorize.Net wrappers need
  below PHP 8.0 (see MOD-287). It is Q54 in [active/KNOWN-DEFECTS.md](active/KNOWN-DEFECTS.md). flat-webapp's
  application lock is therefore left as it is.
- Behavior: none.
- Undo: revert flat-webapp's commit.

## MOD-292 — The root `composer.json` requires PHP 7.4 and `doctrine/annotations` (Q51, Q54)

- Kind: dependency declaration; the owner answered Q51 and Q54 "yes" on 2026-11-27 and Q52 "yes" (numbering
  `v1.74.0`, tagged by the owner). Root `composer.json`: `php` is `>=7.4.0` (was `>=7.3.0`) and `doctrine/annotations
  ^2.0` is required (it was not). The consumer-impact entries are in
  [active/CONSUMER-IMPACT.md](active/CONSUMER-IMPACT.md).
- Behavior: a PHP 7.3 host keeps installing `v1.73.x`; a fresh install on PHP 7.4 now has a working
  Authorize.Net SDK.
- Not verified yet: Docker was unavailable when this was committed, so neither the full suite, `check-docs`
  nor a resolution under a simulated PHP 7.4.33 host has run on it. A later entry records that run.
- Undo: revert the commit.

## MOD-293 — MOD-292 verified

- Kind: verification of MOD-292; no file of `src` or `tests` changed.
- A fresh resolution of the root `composer.json` under a simulated PHP 7.4.33 host (ext-mongodb 1.19.4)
  succeeds and locks `doctrine/annotations` 2.0.2, `jms/serializer` 3.32.9 and `mongodb/mongodb` 1.19.1.
  `docker/bin/test all --coverage`: 1,624 tests, 6,954 assertions, all pass; 100% (332 classes, 1,099 methods,
  8,824 lines). `docker/bin/check-docs`: 0 problems. The consumer-impact entry of Q54 now says "Verified: yes".
- Behavior: none.
- Undo: nothing to undo.

## MOD-294 — The Composer image rebuild of 2026-11-27

- Kind: tooling. `docker/Dockerfile` pins `composer:2.11` to the rebuild of 2026-11-27
  (`sha256:5b2137f112f939a92788ceb5e55f54859d9dbf467a44037cf7760bdda949f365`). It is still Composer 2.11.3, on
  updated base layers; the old pin was the build of 2026-11-24.
- The service audit of the same day found every other pin current: `mongo:9.0` (9.0.8), `rabbitmq:4.4-alpine`
  (4.4.1), `wiremock/wiremock:3.13.2` (the newest release; 4.0 is beta only), `node:26-slim` (26.13.1; 27 is
  alpha), `php:7.4-cli` and `php:7.4-apache` (7.4.33, final): each pinned digest is what its tag points at.
  ImageMagick 7.1.2-36 and imagick 3.8.1 are the newest releases; Xdebug 3.1.6 is the last for PHP 7.4.
- Behavior: none.
- Verified: `docker/bin/test all --coverage`: 1,624 tests, 6,954 assertions, all pass; 100% coverage. flat-webapp
  uses the same pin and passes its 67 tests.
- Undo: revert the commit.

## MOD-295 — A corrected copy of the Mongo adapter's read-preference trait (Q53)

- Kind: runtime compatibility, ahead of the extension move (MOD-296), so it is tested on the extension still in
  the image. `src/flat/vendor/alcaeus/mongo-php-adapter/Helper/ReadPreference.php` is the adapter's trait
  (1.2.5) with string modes in place of the deprecated integer constants and `getMode()`; the mode is read through
  `bsonSerialize()`. The root `composer.json` and `tests/docker/runtime/composer.json` map the trait's namespace
  to it ([notes/VENDORED.md](notes/VENDORED.md)).
- Decided by the owner on 2026-11-27: fix it at its cause now, with a corrected copy of the one class.
- Behavior: none (same modes, same tags, same slave-okay results). Consumers see one cosmetic change with
  optimized autoloaders: [active/CONSUMER-IMPACT.md](active/CONSUMER-IMPACT.md).
- Verified: 13 new tests (`tests/Unit/Db/Mongo/ReadPreferenceTest.php`; every mode, tags, the array form, slave-okay,
  the warnings, no PHP message, and that flat's copy is the file that loads). `docker/bin/test all --coverage`: 1,637
  tests, 7,010 assertions, all pass; 100% (333 classes, 1,105 methods, 8,880 lines). A Composer install of flat
  with the adapter loads flat's copy with the normal, optimized and authoritative autoloaders.
- Undo: revert the commit.

## MOD-296 — The MongoDB extension moves to 1.20.1

- Kind: environment. `docker/Dockerfile` builds ext-mongodb 1.20.1 (commit
  `7dc124cb54ca21cd9987bc9cd08a73dd52e60f97`, with libmongoc `97f166d8d784d6096d48ba288f98b48028cdfe8b`) in place of
  1.19.4; the `ext-mongodb` pin in `tests/docker/runtime/composer.json` is `1.20.1` and the lock is refreshed (hash
  and platform only). 1.20.1 is the newest release that builds on PHP 7.4: 1.21.0 needs 8.1 (see MOD-285).
  MOD-285 held the extension at 1.19.4; MOD-295 removed the reason (Q53).
- Behavior: none for flat's code.
- Verified: with the extension at 1.20.1 and the corrected trait, `docker/bin/test all --coverage`: 1,637 tests,
  7,010 assertions, all pass, no PHP message; 100% (333 classes, 1,105 methods, 8,880 lines). Before MOD-295 the
  same extension produced 47 errors and 1 failure on the adapter's deprecations.
- Undo: revert the commit (and the extension falls back to 1.19.4, where MOD-295 is not needed but harmless).

## MOD-297 — `mongodb/mongodb` 1.19.1 to 1.20.0

- Kind: environment (test dependency). `tests/docker/runtime/composer.lock`: `mongodb/mongodb` 1.20.0, the newest
  release that accepts PHP 7.4 (`^7.4 || ^8.0`); it needs extension 1.20, which MOD-296 installed. The lock was
  resolved with `--no-security-blocking`, for the reason given in MOD-286: every release that runs on PHP 7.4 has the
  advisories whose fixes need PHP 8.1.
- Behavior: none for flat's code.
- Verified: `docker/bin/test all --coverage` (with the MongoDB service): 1,637 tests, 7,010 assertions, all pass, no
  PHP message; 100% coverage.
- Undo: revert the commit.

## MOD-298 — Review of every PHP message under 7.4; two tests made exact

- Kind: verification and test hardening; no file of `src` changed. Asked by the owner on 2026-11-27: look over
  everything for deprecation notices and warnings that appear only because the suite runs on PHP 7.4.
- Found and fixed earlier in the phase: the `ReflectionType` string cast in `tests/tools/apidump.php` (MOD-282) and the
  Mongo adapter's use of what ext-mongodb 1.20 deprecates (MOD-295, MOD-296). Nothing else appears.
- Checked: the whole output of `docker/bin/test all --coverage` (no deprecation, warning, notice, risky or skipped
  test); the output and error stream of every child PHP process the tests start (temporary logging, then reverted:
  nothing PHP-related); `php -l` on every tracked PHP file (MOD-281); the mutation probe and `bin/consumer-check`
  on 7.4 (both run clean); flat-webapp's server log check (clean).
- Reviewed every test that installs its own error handler. Most assert an exact list or pass other messages on to
  PHPUnit. Two were loose and are now exact: `SessionTest::testFailedStorageRetriesAndReturnsNoId` asserts the
  200 messages (100 attempts, each the open failure and the read failure) and `AssetTest::testStringConversionReportsUrlFailureAsNotice`
  asserts the single `E_USER_NOTICE`. A new message from a newer PHP would now fail them.
- Behavior: none.
- Verified: `docker/bin/test all --coverage`: 1,637 tests, 7,209 assertions, all pass; 100% (333 classes, 1,105
  methods, 8,880 lines).
- Undo: revert the commit.

## MOD-299 — Release preparation

- Kind: documentation and verification; no file of `src` or `tests` changed. New: [RELEASE-NOTES.md](RELEASE-NOTES.md),
  [notes/API-COMPARISON.md](notes/API-COMPARISON.md) with the dump `notes/API-V1.74.0.json`,
  [notes/CONSUMER-CHECK.md](notes/CONSUMER-CHECK.md) and [notes/DEPENDENCY-ALERTS.md](notes/DEPENDENCY-ALERTS.md).
- API: 620 classes, interfaces and traits compared with the `v1.73.2` dump: no differences.
- Audit (`composer audit --locked`): the same four advisories as at the end of php-7.3, in flat's runtime lock and
  flat-webapp's application lock; the tools locks have none. No fixed release is reachable on PHP 7.4 (all need 8.1).
- Consumer check: no hit in flat-webapp's `app/` and `web/`. flat-webapp on this branch's code (scratch copy):
  66 of 67 tests pass and the server log is clean; the one failure is its own `1.73.x` lock guard, expected.
- Behavior: none.
- Undo: nothing to undo.

## MOD-300 — The unloadable classes are recorded as defects

- Kind: documentation. KD-107 (the MongoDB SSL classes declare `flat\db\mongo\ssl...` in `db/driver/mongo/ssl...` and
  cannot be autoloaded) and KD-108 (`util\validate\collection` implements `flat\core\collection\validator`, which does
  not exist) are in [active/KNOWN-DEFECTS.md](active/KNOWN-DEFECTS.md), with Q55 and Q56 for the owner. Both were
  already listed as "cannot be loaded" since php-7.2, but never as defects. No code changed.
- Also found by checking every file of `src/flat` for a declared name that differs from its path: nine more files, which
  are already handled by `class_alias` or documented in the php-7.2 API comparison, or have no use; they were not
  looked into one by one.
- Behavior: none.
- Undo: revert the commit.

## MOD-301 — Correction to MOD-300: the other mismatched files

- Kind: documentation (a correction; MOD-300 stays as written). MOD-300 said the nine other files whose declared name differs
  from their path were "already handled ... or have no use". That was not checked. They were then loaded by name under
  PHP 7.4: three are working aliases, four are stray duplicates of classes that load from their own files, and two
  (`flat\fsm`, `flat\orm\invalid_field`) load under neither name. The result is KD-109 and Q57 in
  [active/KNOWN-DEFECTS.md](active/KNOWN-DEFECTS.md). No code changed.
- Behavior: none.
- Undo: revert the commit.

## MOD-302 — `util\validate\collection` is removed (KD-108, Q56)

- Kind: removal of dead code, decided by the owner on 2026-11-27: the class was an unfinished idea ("over-composed"). The file
  `src/flat/core/util/validate/collection.php` had an empty body and implemented `flat\core\collection\validator`, which does not
  exist; it could never be loaded. Consumer impact: [active/CONSUMER-IMPACT.md](active/CONSUMER-IMPACT.md).
- Behavior: none for any code that could run. The API dump and comparison are regenerated in a later entry.
- Verified: nothing in `src` or the tests references the class. `docker/bin/test all --coverage`: all pass; 100%.
- Undo: revert the commit.

## MOD-303 — The MongoDB SSL classes are renamed to match their paths (KD-107, Q55)

- Kind: bug fix with a rename, decided by the owner on 2026-11-27: correct the declarations and references, no `class_alias`.
  Seven files under `src/flat/db/driver/mongo/` (`ssl.php` and six classes) now declare `flat\db\driver\mongo\ssl...` in place
  of `flat\db\mongo\ssl...`; the `extends` references of the four child files follow. `tests/Unit/Db/Mongo/CaFileTest.php` uses
  the new names and no longer includes files by hand. Consumer impact: [active/CONSUMER-IMPACT.md](active/CONSUMER-IMPACT.md).
- Behavior: the classes behave as before; only their names change, and they now load by name. The old names no longer exist.
- Verified: new test `testEverySslClassLoadsByNameWithoutIncludingFiles` (a clean process loads all seven and finds no old
  name). `docker/bin/test all --coverage`: 1,638 tests, 7,225 assertions, all pass; 100% (333 classes, 1,105 methods,
  8,880 lines). The API dump and comparison are regenerated in the next entry.
- Undo: revert the commit.

## MOD-304 — A note on the five `class_alias` calls

- Kind: documentation. [notes/ALIASES.md](notes/ALIASES.md) lists each alias in `src/flat`: where it is, what it points at, who
  uses each name, where the php-7.2 change log (MOD-091, MOD-092, MOD-093, MOD-097) describes it, and what removing it would mean.
  Written for the owner, who dislikes the pattern (Q58). No code changed.
- Behavior: none.
- Undo: revert the commit.

## MOD-305 — The API dump and comparison after MOD-302 and MOD-303

- Kind: documentation (generated dump, regenerated with the tool and copied). `notes/API-V1.74.0.json` is made again; the
  comparison with the `v1.73.2` dump shows exactly two changes, both to classes that could not be loaded: `validate\collection`
  removed (619 entries instead of 620) and seven MongoDB SSL entries that now load. Nothing else differs. The unloadable
  classes are now two (the Authorize.Net wrappers). [notes/API-COMPARISON.md](notes/API-COMPARISON.md) and
  [RELEASE-NOTES.md](RELEASE-NOTES.md) say so; the entries that "declare nothing" were counted one by one.
- Behavior: none.
- Undo: nothing to undo.

## MOD-306 — `subscription\cancelled` becomes `subscription\canceled`; the alias is removed (Q58, item 1)

- Kind: rename, decided by the owner on 2026-11-27: the file's spelling is the name; no alias. `class cancelled` is now `class canceled`
  in `src/flat/cloud/authorize/subscription/canceled.php`; the `class_alias` line and the forwarding file `cancelled.php` are removed.
  `AuthorizeDataTest` uses the one name (its method lost "LegacySpellings"). The exception message is unchanged.
- Behavior: the old class name no longer exists. See [active/CONSUMER-IMPACT.md](active/CONSUMER-IMPACT.md).
- Undo: revert the commit.

## MOD-307 — `badParam` becomes `bad_param`; the alias is removed (Q58, item 2)

- Kind: rename, decided by the owner on 2026-11-27: the class is `bad_param` (flat's style has no camel case); no alias.
  `src/flat/core/ssh/exception/input_error/bad_param.php` declares `bad_param` and has no `class_alias`.
  `LegacyErrorMessagesTest` no longer includes the file by hand and uses `bad_param` (its method lost "Legacy... DeclaredName").
- Behavior: the old class name `badParam` no longer exists. See [active/CONSUMER-IMPACT.md](active/CONSUMER-IMPACT.md).
- Undo: revert the commit.
