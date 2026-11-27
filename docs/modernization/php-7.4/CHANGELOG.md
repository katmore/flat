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
  [active/KNOWN-DEFECTS.md](history/KNOWN-DEFECTS.md).
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
  below PHP 8.0 (see MOD-287). It is Q54 in [active/KNOWN-DEFECTS.md](history/KNOWN-DEFECTS.md). flat-webapp's
  application lock is therefore left as it is.
- Behavior: none.
- Undo: revert flat-webapp's commit.

## MOD-292 — The root `composer.json` requires PHP 7.4 and `doctrine/annotations` (Q51, Q54)

- Kind: dependency declaration; the owner answered Q51 and Q54 "yes" on 2026-11-27 and Q52 "yes" (numbering
  `v1.74.0`, tagged by the owner). Root `composer.json`: `php` is `>=7.4.0` (was `>=7.3.0`) and `doctrine/annotations
  ^2.0` is required (it was not). The consumer-impact entries are in
  [active/CONSUMER-IMPACT.md](history/CONSUMER-IMPACT.md).
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
  optimized autoloaders: [active/CONSUMER-IMPACT.md](history/CONSUMER-IMPACT.md).
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
  not exist) are in [active/KNOWN-DEFECTS.md](history/KNOWN-DEFECTS.md), with Q55 and Q56 for the owner. Both were
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
  [active/KNOWN-DEFECTS.md](history/KNOWN-DEFECTS.md). No code changed.
- Behavior: none.
- Undo: revert the commit.

## MOD-302 — `util\validate\collection` is removed (KD-108, Q56)

- Kind: removal of dead code, decided by the owner on 2026-11-27: the class was an unfinished idea ("over-composed"). The file
  `src/flat/core/util/validate/collection.php` had an empty body and implemented `flat\core\collection\validator`, which does not
  exist; it could never be loaded. Consumer impact: [active/CONSUMER-IMPACT.md](history/CONSUMER-IMPACT.md).
- Behavior: none for any code that could run. The API dump and comparison are regenerated in a later entry.
- Verified: nothing in `src` or the tests references the class. `docker/bin/test all --coverage`: all pass; 100%.
- Undo: revert the commit.

## MOD-303 — The MongoDB SSL classes are renamed to match their paths (KD-107, Q55)

- Kind: bug fix with a rename, decided by the owner on 2026-11-27: correct the declarations and references, no `class_alias`.
  Seven files under `src/flat/db/driver/mongo/` (`ssl.php` and six classes) now declare `flat\db\driver\mongo\ssl...` in place
  of `flat\db\mongo\ssl...`; the `extends` references of the four child files follow. `tests/Unit/Db/Mongo/CaFileTest.php` uses
  the new names and no longer includes files by hand. Consumer impact: [active/CONSUMER-IMPACT.md](history/CONSUMER-IMPACT.md).
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
- Behavior: the old class name no longer exists. See [active/CONSUMER-IMPACT.md](history/CONSUMER-IMPACT.md).
- Undo: revert the commit.

## MOD-307 — `badParam` becomes `bad_param`; the alias is removed (Q58, item 2)

- Kind: rename, decided by the owner on 2026-11-27: the class is `bad_param` (flat's style has no camel case); no alias.
  `src/flat/core/ssh/exception/input_error/bad_param.php` declares `bad_param` and has no `class_alias`.
  `LegacyErrorMessagesTest` no longer includes the file by hand and uses `bad_param` (its method lost "Legacy... DeclaredName").
- Behavior: the old class name `badParam` no longer exists. See [active/CONSUMER-IMPACT.md](history/CONSUMER-IMPACT.md).
- Undo: revert the commit.

## MOD-308 — The session driver is declared as `data\orm\driver\session`; both aliases are removed (Q58, items 3 and 4)

- Kind: rename, decided by the owner on 2026-11-27: declare it as `data\orm\driver\session`, extend the real base class instead of an
  alias, no aliases. `src/flat/data/orm/driver/session.php` has `namespace flat\data\orm\driver; class session extends
  \flat\data\orm\driver`; its `class_alias` is gone. The forwarding files `src/flat/data/session/driver.php` (an alias of the base
  class) and `src/flat/data/session/driver/session.php` are removed. `SessionDriverTest` uses the one name.
- Behavior: the names `flat\data\session\driver` and `flat\data\session\driver\session` no longer exist. See
  [active/CONSUMER-IMPACT.md](history/CONSUMER-IMPACT.md).
- Undo: revert the commit.

## MOD-309 — `not_match_interface` has one name; the last `class_alias` in `src` and the one in the tests are removed (Q58, item 5)

- Kind: rename and test change, decided by the owner on 2026-11-27: no alias. `src/flat/core/util/validate/factory/exception/not_match_interface.php`
  already matched its own file; its `class_alias` for the older namespace `flat\core\util\factory\exception` is removed, and so is the
  forwarding file `src/flat/core/util/factory/exception/not_match_interface.php`. `ValidationTest` loads the one name.
- Also removed: the only `class_alias` in the tests. `RabbitMqTest::testPhpAmqpLib2CallersStillGetTheLegacyConnectionClass` aliased a test
  class as `PhpAmqpLib\Connection\AMQPConnection`, the class php-amqplib 3 removed; it now requires `tests/Support/LegacyAmqpConnection.php`,
  which declares that class under its real name, and checks that name.
- Behavior: the old name `flat\core\util\factory\exception\not_match_interface` no longer exists. See
  [active/CONSUMER-IMPACT.md](history/CONSUMER-IMPACT.md).
- Undo: revert the commit.

## MOD-310 — A rule against `class_alias` in `AGENTS.md`

- Kind: rule, asked for by the owner on 2026-11-27. flat's `AGENTS.md` has a section "Class names: never `class_alias`": never use it
  (no shims, no forwarding files, no exception list); rename the class and every reference and record the rename in the consumer
  impact; declare every class with the namespace and name its path gives; flat's class names are lowercase words joined by
  underscores, never camel case; a test double that needs a third party's class name declares it in a support file. flat-webapp's
  `AGENTS.md` carries the rule too and points to flat's.
- Behavior: none.
- Undo: revert the commit.

## MOD-311 — The API dump and comparison after the renames

- Kind: documentation (generated dump, regenerated with the tool and copied). `notes/API-V1.74.0.json` is made again: 615 entries
  against the 620 of `v1.73.2`. [notes/API-COMPARISON.md](notes/API-COMPARISON.md) lists the five files removed (names that were only aliases
  or forwarding files, plus `validate\collection`), the seven SSL entries that now load, and explains the six methods and one
  constant that appear "added" (they were recorded under the old names while those were aliases). [RELEASE-NOTES.md](RELEASE-NOTES.md)
  lists the old names to search for; the defect list is brought up to date (Q58 answered, KD-109 and Q57 no longer mention aliases).
- Behavior: none.
- Undo: nothing to undo.

## MOD-312 — `flat\fsm\fsm` is declared as `flat\fsm` (KD-109, Q57)

- Kind: rename, decided by the owner on 2026-11-27: the path name, no alias. `src/flat/fsm.php` has `namespace flat; abstract class fsm`
  (it declared `flat\fsm\fsm`; its own header says `\flat\fsm`). New test `tests/Unit/FsmTest.php` loads it by name. The string
  `"flat\\fsm"` in `core/exception/controller.php` is a namespace-prefix list and still matches.
- Behavior: the name `flat\fsm\fsm` no longer exists; nothing used it. See [active/CONSUMER-IMPACT.md](history/CONSUMER-IMPACT.md).
- Undo: revert the commit.

## MOD-313 — `flat\orm\invalid_param` is declared as `flat\orm\invalid_field` (KD-109, Q57)

- Kind: rename, decided by the owner on 2026-11-27: the path name, no alias. `src/flat/orm/invalid_field.php` declares `invalid_field`.
  `LegacyFieldErrorTest` no longer includes the file by hand and loads the class by name in a fresh process.
- Behavior: the name `flat\orm\invalid_param` no longer exists; nothing used it. The API dump is regenerated: 615 entries, nine that now
  load ([notes/API-COMPARISON.md](notes/API-COMPARISON.md)); the entries that declare nothing are 12, counted one by one. See
  [active/CONSUMER-IMPACT.md](history/CONSUMER-IMPACT.md).
- Verified (both): `docker/bin/test all --coverage`: 1,639 tests, 7,229 assertions, all pass; 100% (333 classes, 1,105 methods, 8,872
  lines); no PHP message.
- Undo: revert the commit.

## MOD-314 — The four mismatched classes are declared at their own path; forwarding files, includes and `class_alias` are guarded against (KD-109)

- Kind: renames and a guard, decided by the owner on 2026-11-27 ("the path decides"; "everything should rely on autoload").
  `src/flat/api/response_header/exception.php` and `exception/missing_request_field.php` declare `flat\api\response_header...`;
  `src/flat/cloud/authorize/customer/payment/bankAccount.php` and `creditCard.php` declare `...\customer\payment`. The four one-line
  forwarding files (`api/header/...`, `customer/payment_profile/...`) are deleted. `api/response_header.php`, `ResponseTest`,
  `AuthorizePaymentMethodTest`, `AuthorizeTransactionTest` and a docblock in `customer.php` follow.
- Correction: MOD-301, MOD-312 and MOD-313 and the notes of the same days called these four files "stray duplicates" whose real class
  loads from its own file. That was wrong: the files at the declared names were forwarding stubs, and the code lived in the oddly named
  files. Nothing was deleted on that basis. The defect list and the notes are corrected.
- New test `tests/Unit/CodingRulesTest.php` enforces the rules of `AGENTS.md` over `src`: no `class_alias` (also in tests, `bin`,
  `docker`), every class declared where its path says, and no `require`/`include` except the two loaders of data files
  (`core/config.php`, which includes a config file returning an array, and `core/controller/tmpl.php`, which includes templates).
  `AGENTS.md` now says classes load through the autoloader, never by include.
- Behavior: the four old names no longer exist ([active/CONSUMER-IMPACT.md](history/CONSUMER-IMPACT.md)).
- Undo: revert the commit.

## MOD-315 — Seven files that declared nothing are deleted

- Kind: removal of dead files, decided by the owner on 2026-11-27 ("it's slop"). `src/flat/cloud/disqus.php`, the five 0-byte files
  under `src/flat/cloud/pci/` and `src/flat/orm/missing_required_field.php` (only `<?php`) are deleted. The earlier notes called the six
  0-byte files "deliberately left"; no owner decision to keep them is recorded (MOD-125 only said "not touched").
  `src/flat/event/alert/listener.php` stays: its retirement, with the old scaffolding kept as comments, is the owner's decision of
  MOD-107.
- Behavior: none for any code; nothing referenced these files.
- Verified (MOD-314 and MOD-315): `docker/bin/test all --coverage`: 1,642 tests, 7,232 assertions, all pass; 100% (333 classes, 1,105
  methods, 8,868 lines); no PHP message. The API dump and comparison are regenerated (604 entries;
  [notes/API-COMPARISON.md](notes/API-COMPARISON.md)).
- Undo: revert the commit.

## MOD-316 — The API tool ignores the Authorize.Net SDK's deprecation notice; the two wrappers are no longer "unloadable"

- Kind: tool fix with an approved exception, decided by the owner on 2026-11-27 ("ignore the E_USER_DEPRECATED for that one class").
  `AuthorizeNetARB` and `AuthorizeNetCIM` of flat extend the SDK's deprecated classes, whose files call `trigger_error(...,
  E_USER_DEPRECATED)` when they load. In `tests/tools/apidump.php` the notice went to the same output as the tool's JSON, so the
  tool could not read it and reported both classes as unloadable (a flaw of the tool, not of the classes, and not related to
  PHPUnit's setting). The tool now discards only an `E_USER_DEPRECATED` notice raised from the SDK's `lib/deprecated/` files, in the
  same way as the approved exception of `tests/Support/LegacyAuthorizeSdk.php`; everything else is handled as before. The exception
  carries its comment block and is listed in `AGENTS.md`. Two tests in `ApiDumpTest` prove the scope: the SDK notice is discarded,
  the same notice from another place and another severity from the SDK's folder still make a class unloadable.
- Behavior: none for flat. The API dump describes both wrappers (their methods and parent classes) and none is unloadable.
- Undo: revert the commit.

## MOD-317 — `event/alert/listener.php` is deleted

- Kind: removal of a file that declared nothing, decided by the owner on 2026-11-27 (the retired pthread listener kept as comments:
  "it's slop"). MOD-107 had kept it for reference; git history keeps the old code. Consumer impact:
  [active/CONSUMER-IMPACT.md](history/CONSUMER-IMPACT.md). The test that the class does not exist is unchanged.
- Behavior: none.
- Verified (MOD-316 and MOD-317): `docker/bin/test all --coverage`: all pass; 100% coverage; no PHP message. The API dump is regenerated
  (603 entries, none unloadable, none declaring nothing; [notes/API-COMPARISON.md](notes/API-COMPARISON.md)).
- Undo: revert the commit.

## MOD-318 — The reason for deleting `event/alert/listener.php`, from the owner

- Kind: documentation. The owner confirmed on 2026-11-27 that the pthread `listener` (MOD-317 deleted its comments-only file) was never used in
  production code: the owner did something else, and the class "is missing a step anyway". The full git history agrees that it was
  never more than a skeleton: since the initial commit of 2015 its `run()` held only comments (the old code is in git, for example
  `git show 9bec827:src/flat/event/alert/listener.php`). It stays deleted and is not restored; a working alert listener, if wanted, is a new
  design (not one that needs the pthreads extension, which has no PHP 8 version).
- Behavior: none.
- Undo: nothing to undo.

## MOD-319 — The tests no longer include their support traits by hand

- Kind: test cleanup, asked for by the owner on 2026-11-27 ("get rid of the pointless ones"; "everything should rely on autoload"). 33
  `require_once` lines in 27 test files are removed: the ones for the support traits `LegacyAuthorizeSdk`, `PhpSubprocess`,
  `ConfigSandbox` and `SandboxSupport`, which the autoloader (`flat\test\` maps to `tests/`) already finds. No test name changed.
- Left on purpose, for the owner to decide (the curl helper investigation): the includes of the four support files that define
  namespaced PHP function overrides (`CurlDouble`, `AssetIoBoundary`, `SshBoundary`, `NativeSessionBoundary`), because PHP cannot
  autoload functions; the two conditional includes inside `tests/Sandbox/SandboxSupport.php`; and the bootstraps of the child PHP
  processes and the test environment.
- Behavior: none (tests only).
- Verified: `docker/bin/test all --coverage`: 1,644 tests, 7,248 assertions, all pass; 100% coverage; no PHP message. The test inventory is
  regenerated (the removed lines moved the line numbers); the sandbox tests load.
- Undo: revert the commit.

## MOD-320 — The cURL tests use a real HTTP server; the fake cURL is removed

- Kind: test change, decided by the owner on 2026-11-27 (option B). The tests of `flat\core\curl\response` and of the IP lookup used
  `tests/Support/CurlDouble.php`, which replaced PHP's cURL functions by namespaced functions, invented answers (an unknown URL got a
  fake `200 OK`; `filetime` 123) and kept global state. They now send real requests with real cURL to the WireMock server that is
  already in the test stack, through `tests/Support/HttpMock.php` (group `http`, variable `FLAT_TEST_HTTP_URL`; `tests/tools/services.php`
  maps the group to the existing service, named once). `CurlDouble.php` is deleted. `ResponseTest` has 45 tests (was 25) and
  `IpInfoDbTest` the same behavior as before; failures are real ones (errno 7, 56, 52).
- The real server found three bugs, fixed in MOD-321 (KD-110 to KD-112). The one case real HTTP cannot produce, `curl_getinfo()` returning
  false, keeps a tiny override (`tests/Support/CurlInfoFailure.php`) in its own test run in a fresh process; Q59 asks whether to remove
  that dead branch instead.
- The other three function doubles (`SshBoundary`, `AssetIoBoundary`, `NativeSessionBoundary`) are unchanged. A trial showed that
  defining an override after other tests have run the call does not work (PHP remembers the function a call site resolved to), so they
  keep their includes.
- Behavior: none for flat. `docker/bin/test unit` no longer runs these tests (they need the server); `all` does. See
  [../TESTING.md](../TESTING.md).
- Undo: revert the commit.

## MOD-321 — Three cURL bugs found by the real server are fixed (KD-110, KD-111, KD-112)

- Kind: bug fixes (behavior changes). `src/flat/core/curl/response.php`: a request with data now sends it (`CURLOPT_POSTFIELDS`);
  `CURLOPT_FILETIME` is set, so `filetime` is real; `is_robots_allowed()` keeps the port of the URL. Each has a real-HTTP test that
  failed before. Consumer impact: [active/CONSUMER-IMPACT.md](history/CONSUMER-IMPACT.md).
- Behavior: see above.
- Undo: revert the commit.

## MOD-322 — `ipinfodb::_get_api_base_url()`

- Kind: a seam for the tests. `src/flat/cloud/ipinfodb.php` gets the protected hook `_get_api_base_url()`, returning `api_base_url` by default,
  next to its existing hooks `_get_api_key()` and `_get_mongo_crud()`. The tests point it at the test server.
- Behavior: none unless a subclass overrides it.
- Verified (MOD-320 to MOD-322): `docker/bin/test all --coverage`: 1,663 tests, 7,303 assertions, all pass; 100% (333 classes, 1,106
  methods, 8,872 lines); no PHP message; `docker/bin/test unit`: 1,538 tests pass.
- Undo: revert the commit.

## MOD-323 — The dead `missing_curlinfo` check is removed (Q59)

- Kind: removal of unreachable code, decided by the owner on 2026-11-27. `src/flat/core/curl/response.php` no longer tests
  `curl_getinfo($ch)` for `false` (it cannot be false for a finished transfer), and the class `flat\core\curl\exception\missing_curlinfo` is
  deleted. With them go the last override of a cURL function, `tests/Support/CurlInfoFailure.php`, and its test
  `ResponseTransferInfoTest`. There is no PHP function override for cURL left in the tests. Consumer impact:
  [active/CONSUMER-IMPACT.md](history/CONSUMER-IMPACT.md).
- Behavior: the exception class no longer exists; nothing could throw it.
- Verified: `docker/bin/test all --coverage`: all pass; 100% coverage. The API dump is regenerated (one more name gone;
  [notes/API-COMPARISON.md](notes/API-COMPARISON.md)).
- Undo: revert the commit.

## MOD-324 — The test image has the real ssh2 extension

- Kind: environment, asked for by the owner on 2026-11-27 (the SSH tests faked the extension because "native SSH2 is unavailable"; nobody
  had installed it). `docker/Dockerfile` builds, from pinned sources with their checksums or commits, OpenSSL 3.6.5 (static), libssh2
  1.11.1 (static) and the PECL ssh2 extension 1.4.1 (commit `65c0da1b18373e2286d1569a176101583225a2c0`), which carries its own copy of both: PHP's own
  OpenSSL (1.1.1n) is untouched. No apt: the base image's Debian 11 is end of life. The image's check that its extensions load lists `ssh2`.
  `ssh2` 1.4.1 supports PHP 7.4 (minimum 7.0); the six functions flat uses (`ssh2_connect`, `ssh2_auth_password`, `ssh2_auth_pubkey_file`,
  `ssh2_fingerprint`, `ssh2_exec`, `ssh2_sftp`) are present.
- Next, in their own entries: a real OpenSSH server in the test stack, `SshTest` against it, and `SshBoundary` (the fake) deleted.
- Behavior: none for flat; the existing tests still use their fake and pass.
- Verified: the image builds and loads the extension; `docker/bin/test all --coverage`: 1,662 tests, 7,298 assertions, all pass; 100% coverage.
- Undo: revert the commit.

## MOD-325 — The SSH tests use a real OpenSSH server; the fake ssh2 is removed

- Kind: test change, decided by the owner on 2026-11-27. `tests/Unit/Core/SshTest.php` ran flat's SSH code against `tests/Support/SshBoundary.php`,
  which replaced the `ssh2_*` functions (and `exec`, `function_exists`, `time`) by namespaced functions with scripted answers, because the extension was
  missing from the image (MOD-324). Now: the new service `sshd` (`docker/sshd`, Alpine 3.24 with OpenSSH 10.3, compose file `docker/docker-compose.ssh.yml`,
  group `ssh`, mapped in `tests/tools/services.php`; `docker/bin/test` also builds it, and `PhpSubprocess` takes PHP settings and an
  environment for a child process) and `tests/Unit/Core/SshServerTest.php` (50 tests: password and key logins with real keys, the host fingerprint,
  real commands, background processes, signals, a real zombie for "cannot kill", SFTP of real files, a refused channel on a second port).
  Failures that are hard to cause are caused for real in a fresh PHP process (no `tr` on the PATH, `proc_open` disabled, the extension disabled); one race (the process
  gone between the check and the second signal) uses the class's own `exec_output()` hook. `SshTest` keeps the 15 tests that need no server.
  `SshBoundary.php` is deleted. Four cases of the old file are gone because no real server can produce what they scripted: the two idle-timeout tests
  (they set a fake clock), a data set with a failing `fread`, and a data set with an error on standard error while `openssl` runs (a race). The coverage of `ssh.php` is still 100%.
- Behavior: none for flat. See [../TESTING.md](../TESTING.md).
- Undo: revert the commit.

## MOD-326 — Two SSH bugs found by the real server are fixed (KD-113, KD-114)

- Kind: bug fixes. `src/flat/core/ssh.php`: `tr`'s standard error is discarded in the command that makes the temporary passphrase, and the disconnect callback
  is `static`. Each has a test that failed before. Consumer impact: [active/CONSUMER-IMPACT.md](history/CONSUMER-IMPACT.md).
- Behavior: see above.
- Verified (MOD-325, MOD-326): `docker/bin/test all --coverage`: 1,666 tests, 7,371 assertions, all pass; 100% (332 classes, 1,105 methods, 8,870 lines); no PHP message;
  `docker/bin/test unit`: all pass without the server.
- Undo: revert the commit.

## MOD-327 — The last two function overrides are replaced by real conditions

- Kind: test change, decided by the owner on 2026-11-27. `tests/Support/NativeSessionBoundary.php` (an override of `session_status()`) and
  `tests/Support/AssetIoBoundary.php` (an override of `file_get_contents()`) are deleted, with their `require_once` lines.
  - `SessionDriverTest` now starts a fresh PHP process with `session.save_handler` (and, in a second data set, `session.serialize_handler`) set to a name
    that does not exist. PHP itself then reports `PHP_SESSION_DISABLED`, and flat's `failure` exception is thrown.
  - `AssetTest` now exhausts the process's file descriptors in a fresh PHP process, so a file that exists and is readable cannot be opened. PHP raises its
    own "Too many open files" warning, the test captures it, and flat's `failed to read system resource file` exception is thrown.
- Behavior: none for flat.
- Verified: `docker/bin/test all --coverage`: 1,667 tests, 7,375 assertions, all pass; 100% (332 classes, 1,105 methods, 8,870 lines); no PHP message.
- Undo: revert the commit.

## MOD-328 — The S3 tests use a real S3 emulator; the fake AWS client is removed

- Kind: test change, decided by the owner on 2026-11-27. `AwsTest` ran `flat\cloud\aws\s3` against `ClientDouble`, a class that recorded the calls
  it was given, and `S3MultipartTest` replaced `filesize()` by a namespaced function and gave the SDK a `MockHandler` with scripted replies.
  Now: the new service `s3mock` (Adobe S3Mock 5.2.3, image pinned by digest, `docker/docker-compose.s3.yml`, group `s3`, mapped in
  `tests/tools/services.php`; `docker/bin/test` and `bin/test` know it) and `tests/Support/S3Mock.php` (a real SDK client, a bucket per test).
  `S3MultipartTest` has 11 tests against it: every key form, files, the object parameters, a real multipart upload of a 100 MB sparse file, an
  initiation failure (`NoSuchBucket`), a part failure the emulator refuses (a part number above 10,000) with the abort taking effect, and a failed abort.
  `AwsTest` tests the generic client handling with a plain PHP class (`ArrayObject`) and needs no server. The `filesize()` override and `ClientDouble` are gone.
  Two cases of the old tests are gone because the emulator cannot produce what they scripted: an `AccessDenied` answer to a part and to the initiation.
- Found: two defects, both waiting for the owner: KD-115 and KD-116 (Q60, Q61), each with a `@group known-defect` test.
- Behavior: none for flat.
- Verified: `docker/bin/test all --coverage`: 1,670 tests, 7,394 assertions, all pass; 100% (332 classes, 1,105 methods, 8,870 lines); no PHP message;
  `docker/bin/test unit`: 1,485 tests, all pass without any service.
- Undo: revert the commit.

## MOD-329 — A named argument left out keeps its default; a failed abort no longer replaces the upload failure (KD-115, KD-116)

- Kind: bug fixes, decided by the owner on 2026-11-27 (Q60, Q61). `src/flat/core/mapper.php`: in `args_to_properties()`, when the arguments are named (an
  array with a string key) a name that was left out keeps its default instead of taking the value at its position; positional calls are unchanged.
  `src/flat/cloud/aws/s3.php`: in `file_upload()` the SDK's exception from `abortMultipartUpload()` is caught and dropped, so `upload_failure` holding the
  original exception is thrown. Each has a test that failed before. Consumer impact: [active/CONSUMER-IMPACT.md](history/CONSUMER-IMPACT.md).
- Behavior: see above.
- Verified: `docker/bin/test all --coverage`: 1,673 tests, 7,399 assertions, all pass; 100% (332 classes, 1,105 methods, 8,877 lines); no PHP message. No method
  signature changed, so the API comparison stands.
- Undo: revert the commit.

## MOD-330 — flat-webapp follows the release

- Kind: documentation, after the owner tagged `v1.74.0` on 2026-11-27 (the tag is the merge of this branch into `master`, `2812136`).
  flat-webapp (`php-7.4`): the lock moves to flat v1.74.0 with the constraint `1.74.*` and `php >=7.4.0`, `DependencyLockTest` expects a 1.74.x framework, and
  then `doctrine/deprecations` 1.2.0, `jms/serializer` 3.32.9, `phpstan/phpdoc-parser` 2.4.2, `mongodb/mongodb` 1.20.0 (dropping `jean85/pretty-package-versions` and
  `symfony/polyfill-php73`, adding `psr/log`) and, in its test-tool lock, `nikic/php-parser` 5.9.0: one package and one commit each. The four advisories
  on `aws/aws-sdk-php` and `mongodb/mongodb` still need PHP 8.1. This repository's README, handoff and two notes are updated to say so.
- Behavior: none for flat.
- Verified: flat-webapp's `docker/bin/test` after each step: 67 tests, 203 assertions, all pass, no PHP message in the server log.
- Undo: revert the commit.

## MOD-331 — Internal test services bypass the host's outbound proxy

- Kind: test environment fix, found during the adversarial review. Docker injected proxy variables with only localhost in the bypass list;
  cURL and the AWS SDK sent requests for the internal HTTP services toward the proxy. The initial full run raised service errors and was interrupted.
  `docker/docker-compose.yml` now adds every fixed test service name to `NO_PROXY` and `no_proxy`, preserving host entries.
- Behavior: test networking only; no framework API, application behavior or test assertion changed.
- Verified: repository images built online, then `docker/bin/test all --offline --coverage`: 1,673 tests, 7,415 assertions, all pass;
  100% class, method and line coverage. Docker-only PHP 7.4.33 execution, with the injected proxy settings still enabled.
- Undo: revert the commit.

## MOD-332 — Adversarial review records eleven inherited defects

- Kind: review and documentation. [active/ADVERSARIAL-REVIEW.md](history/ADVERSARIAL-REVIEW.md) records eleven findings (KD-117 to KD-127),
  with native PHP 7.4.33 Docker reproductions, impacts, test gaps and recommendations. Four are P1: three PDO data errors and SSH password
  authentication without server identity verification. Seven are P2: joins, cURL methods/bodies/DOM/robots, negative limits and SSH disconnect.
- Behavior: no production source fix or existing test assertion changed. The defects stay open in
  [active/KNOWN-DEFECTS.md](history/KNOWN-DEFECTS.md); Q62 asks for the host verification policy before a security compatibility change.
  README and session handoff point to the findings. The separate MOD-331 fixes only test-container networking.
- Verified: full suite 1,673 tests / 7,415 assertions / 100% coverage; flat-webapp 67 tests / 203 assertions with a clean PHP server log;
  45 JavaScript programs; fresh API dump (602 entries, no differences); consumer scan (41 files, 24 impact entries, no hits);
  both locked dependency audits (four known runtime advisories, none in tools); documentation check with no problems.
  All PHP and Composer execution was in Docker. The live Authorize.Net sandbox was not run; the full suite uses its documented mock.
- Undo: revert this documentation commit; the recorded defects remain in the source until separately fixed.

## MOD-333 — Correct PDO bindings, criteria, joins and pagination (KD-117 to KD-120, KD-125)

- Kind: inherited bug fixes authorized after the adversarial review. Bound parameters retain independent storage;
  boolean/decimal values no longer become NULL, and decimal criteria are not truncated. Structured joins use their direction
  (LEFT, INNER or RIGHT) and reject invalid directions. Pagination enforces its minimums and identifies the invalid field.
- Consumer impact: [active/CONSUMER-IMPACT.md](history/CONSUMER-IMPACT.md). No public signature changed.
- Verified: new regressions failed against the old source (8 failures); after the fixes, the focused native SQLite suite passes
  in PHP 7.4 Docker: 58 tests, 122 assertions. Test inventory regenerated and documentation checked before committing.
- Owner decision Q62: supplied SSH fingerprints must be verified for password and key authentication; unpinned callers remain allowed.
- Undo: revert the commit.

## MOD-334 — Correct cURL methods, falsy bodies, DOM and robots parsing (KD-121 to KD-124)

- Kind: inherited bug fixes. Explicit methods are applied with or without a body; HEAD does not consume the response body.
  Non-NULL bodies are transmitted even when zero or empty. Lazy DOM access tests the DOM itself. Robots parsing ignores comments,
  unrelated directives and empty rules, accepts whitespace around separators, and treats an absent path as `/`.
- Consumer impact: [active/CONSUMER-IMPACT.md](history/CONSUMER-IMPACT.md). No public signature changed.
- Verified: regressions first reproduced 3 errors and 9 failures against the old source; after the fix the real-HTTP response suite passes
  in PHP 7.4 Docker: 60 tests, 172 assertions. Inventory regenerated and documentation checked before committing.
- Undo: revert the commit.

## MOD-335 — Fix SSH disconnect and verify supplied host fingerprints (KD-126, KD-127)

- Kind: inherited bug and security fixes. disconnect() releases SFTP, disconnects the SSH transport, clears local state and allows reconnecting.
  Destruction no longer opens a remote command channel. A supplied fingerprint is checked before either password or key authentication;
  constructor `fingerprint` and the existing get_auth() mechanism both work. Q62 keeps NULL/empty-string pins optional. Unpinned key logins
  still remember their first fingerprint; unpinned password logins remain unpinned. auth declares the public fingerprint field (default NULL).
- Tests: new regressions exercise real disconnect/reconnect, SFTP release, rejected commands after disconnect, constructor pins, mismatches before
  bad credentials can be sent, malformed pins and unpinned callers. Initial runs also exposed two fixture faults: OpenSSH banned the shared
  client after intentional pre-authentication rejections, and a shell could reap the supposed zombie before exec. The local SSH server disables
  PerSourcePenalties; the child now waits for its parent to become sleep, and its zombie state is asserted before signal testing. No PHP diagnostic
  was suppressed and no test was skipped. Both SSH server images were rebuilt from the repository configuration.
- Consumer impact: [active/CONSUMER-IMPACT.md](history/CONSUMER-IMPACT.md). API comparison: 602 entries; only the declared fingerprint property is added.
  All eleven review defects are marked fixed, Q62 is resolved, current status/handoff and reference notes are updated, and inventory is regenerated.
- Verified: PHP 7.4.33 Docker full suite: 1,703 tests, 7,470 assertions, zero errors/warnings/failures/skips;
  100% coverage (332 classes, 1,105 methods, 8,877 lines). The sample app passed 67 tests / 203 assertions plus its server-log check with this modified
  framework mounted into both containers. All 45 JavaScript programs passed. Documentation checked before committing. No release or PHP version step.
- Undo: revert the commit.

## MOD-336 — Check PHP 7.4 exit readiness and draft v1.74.1 notes

- Kind: review/documentation, no production or test behavior changed. [notes/EXIT-REVIEW.md](notes/EXIT-REVIEW.md) records release/consumer closeout,
  branch ancestry, deferred work and PHP 8.0 handoff considerations. [RELEASE-NOTES-V1.74.1.md](RELEASE-NOTES-V1.74.1.md) is a draft; no release published.
- Found KD-128: the mutation wrapper's copied checkout omits bin/docker, so the unmutated suite fails at CodingRulesTest before a mutant runs.
  Its service mode also omits the newer HTTP/SSH/S3 setup by inspection. This remains open, with a concrete reproduction and required validation.
- Verified: four locked Composer audits rerun in Docker; four unchanged runtime advisories, no tool advisories. GitHub alert-state reads returned
  integration HTTP 403, recorded explicitly. Existing unchanged-source evidence remains 1,703 passing tests and 100% coverage, plus patched-app tests.
  Documentation checked before committing. Neither master branch, PHP runtime, release tag nor alert state was changed.
- Undo: revert this documentation commit.

## MOD-337 — Merge master back into php-7.4 on the owner's instruction

- Kind: branch synchronization. Merged origin/master at 2812136 into php-7.4 after the owner explicitly requested it.
  The merge brings in the PR 6 merge commit that GitHub counted as one commit behind; it changes no file contents compared with its first parent.
- Behavior: none. Master remains at 2812136; no release, runtime step, reset, rebase or force push.
- Verified: merge tree equals its first parent's tree; master is an ancestor of php-7.4. Documentation check passes.
  Updated [notes/EXIT-REVIEW.md](notes/EXIT-REVIEW.md) to distinguish the original ancestry finding from the completed synchronization.
- Undo: no undo required for runtime behavior; this is additive history only.

## MOD-338 — Archive completed phase work and keep active folders current

- Kind: documentation organization. Closed PHP 7.2/7.3 active folders are emptied; completed plans, defect/impact records, reviews, evidence and
  frozen inventories are archived. The PHP 7.4 completed review and old registry/handoff/impact snapshots move to history. Generated inventory
  bodies are preserved; only archive context is prepended. The live defect list contains KD-128 only; the handoff carries unresolved follow-ups.
- Historical entry wording and printed paths are preserved; Markdown link destinations are maintained for moved records. Current links and
  consumer-check's historical default input follow the new locations. No framework behavior changes and KD-128 is still open.
- Verified: documentation link/path check and consumer-check's default scan compared before/after in Docker before committing.
- Undo: reverse the path map and navigation changes.

### Path map for MOD-338

- `docs/modernization/php-7.2/TEST-INVENTORY.md` → `docs/modernization/php-7.2/history/TEST-INVENTORY.md`
- `docs/modernization/php-7.2/active/CONSUMER-IMPACT.md` → `docs/modernization/php-7.2/history/CONSUMER-IMPACT.md`
- `docs/modernization/php-7.2/active/KNOWN-DEFECTS.md` → `docs/modernization/php-7.2/history/KNOWN-DEFECTS.md`
- `docs/modernization/php-7.2/active/MUTATION-PROBE.md` → `docs/modernization/php-7.2/history/MUTATION-PROBE.md`
- `docs/modernization/php-7.2/active/WEBAPP-REVIEW.md` → `docs/modernization/php-7.2/history/WEBAPP-REVIEW.md`
- `docs/modernization/php-7.2/active/WEBAPP-TESTS.md` → `docs/modernization/php-7.2/history/WEBAPP-TESTS.md`
- `docs/modernization/php-7.3/HANDOFF.md` → `docs/modernization/php-7.3/history/HANDOFF.md`
- `docs/modernization/php-7.3/TEST-INVENTORY.md` → `docs/modernization/php-7.3/history/TEST-INVENTORY.md`
- `docs/modernization/php-7.3/active/ADVERSARIAL-REVIEW-HANDOFF.md` → `docs/modernization/php-7.3/history/ADVERSARIAL-REVIEW-HANDOFF.md`
- `docs/modernization/php-7.3/active/ADVERSARIAL-REVIEW.md` → `docs/modernization/php-7.3/history/ADVERSARIAL-REVIEW.md`
- `docs/modernization/php-7.3/active/CONSUMER-IMPACT.md` → `docs/modernization/php-7.3/history/CONSUMER-IMPACT.md`
- `docs/modernization/php-7.3/active/HIDDEN-ERRORS.md` → `docs/modernization/php-7.3/history/HIDDEN-ERRORS.md`
- `docs/modernization/php-7.3/active/KNOWN-DEFECTS.md` → `docs/modernization/php-7.3/history/KNOWN-DEFECTS.md`
- `docs/modernization/php-7.3/active/MUTATION-PROBE.md` → `docs/modernization/php-7.3/history/MUTATION-PROBE.md`
- `docs/modernization/php-7.3/active/WEBAPP-FUNCTIONAL.md` → `docs/modernization/php-7.3/history/WEBAPP-FUNCTIONAL.md`
- `docs/modernization/php-7.3/active/review-evidence/API-COMPARISON.log` → `docs/modernization/php-7.3/history/review-evidence/API-COMPARISON.log`
- `docs/modernization/php-7.3/active/review-evidence/BASELINE.log` → `docs/modernization/php-7.3/history/review-evidence/BASELINE.log`
- `docs/modernization/php-7.3/active/review-evidence/CURL-PROBES.log` → `docs/modernization/php-7.3/history/review-evidence/CURL-PROBES.log`
- `docs/modernization/php-7.3/active/review-evidence/EQUIVALENCE.log` → `docs/modernization/php-7.3/history/review-evidence/EQUIVALENCE.log`
- `docs/modernization/php-7.3/active/review-evidence/ERROR-CANARIES.log` → `docs/modernization/php-7.3/history/review-evidence/ERROR-CANARIES.log`
- `docs/modernization/php-7.3/active/review-evidence/HANDLED-FATAL.log` → `docs/modernization/php-7.3/history/review-evidence/HANDLED-FATAL.log`
- `docs/modernization/php-7.3/active/review-evidence/JAVASCRIPT.log` → `docs/modernization/php-7.3/history/review-evidence/JAVASCRIPT.log`
- `docs/modernization/php-7.3/active/review-evidence/LOCKS.log` → `docs/modernization/php-7.3/history/review-evidence/LOCKS.log`
- `docs/modernization/php-7.3/active/review-evidence/MUTATION-731.log` → `docs/modernization/php-7.3/history/review-evidence/MUTATION-731.log`
- `docs/modernization/php-7.3/active/review-evidence/MUTATION-732.log` → `docs/modernization/php-7.3/history/review-evidence/MUTATION-732.log`
- `docs/modernization/php-7.3/active/review-evidence/TOOL-PROBES.log` → `docs/modernization/php-7.3/history/review-evidence/TOOL-PROBES.log`
- `docs/modernization/php-7.3/active/review-evidence/WEBAPP-FIXES.log` → `docs/modernization/php-7.3/history/review-evidence/WEBAPP-FIXES.log`
- `docs/modernization/php-7.3/active/review-evidence/WEBAPP-INSTALL.log` → `docs/modernization/php-7.3/history/review-evidence/WEBAPP-INSTALL.log`
- `docs/modernization/php-7.4/active/ADVERSARIAL-REVIEW.md` → `docs/modernization/php-7.4/history/ADVERSARIAL-REVIEW.md`
- `docs/modernization/php-7.4/active/CONSUMER-IMPACT.md` → `docs/modernization/php-7.4/history/CONSUMER-IMPACT.md`
- `docs/modernization/php-7.4/active/KNOWN-DEFECTS.md` → `docs/modernization/php-7.4/history/KNOWN-DEFECTS.md`
- `docs/modernization/php-7.4/active/SESSION-HANDOFF.md` → `docs/modernization/php-7.4/history/SESSION-HANDOFF.md`

## MOD-339 — Repair both mutation-runner modes (KD-128)

- Kind: test-tool fix. The temporary checkout includes bin/docker as required by CodingRulesTest. Service mode loads the current HTTP/Authorize.Net,
  SSH and S3 Compose fragments alongside MongoDB/RabbitMQ, supplying all environment/volume inputs. PHP runs as the host UID/GID (1000:1000 for root
  hosts). Startup failures print diagnostics and cleanup failures propagate instead of being discarded. No production source or mutation algorithm changed.
- Verified in Docker: unit-only and full-service baselines pass; each targeted one-mutant probe catches the removed rand type guard, with one valid
  mutant killed, zero timeouts and zero survivors. Invalid arguments exit 2 after cleanup. Temporary copies were removed and source stayed unchanged.
  Commands and results: [history/MUTATION-RUNNER.md](history/MUTATION-RUNNER.md). No test was skipped or diagnostic suppressed.
- Archive: `docs/modernization/php-7.4/active/KNOWN-DEFECTS.md` snapshot moved to
  `docs/modernization/php-7.4/history/MUTATION-RUNNER.md`; the active file is now a short current-status index with no open defects.
  Updated handoff, overview, testing workflow and exit review. Test cases did not change, so the existing generated inventory remains valid.
- Undo: revert the commit.

## MOD-340 — Make completion-time archiving mandatory for future agents

- Kind: agent/documentation instructions, requested by the owner. Root AGENTS.md makes archiving a completion/commit/handoff requirement;
  docs/modernization/README.md holds the detailed workflow. Active folders retain unfinished work and pending release checks only;
  closed-phase active folders contain only their placeholder. Unresolved follow-ups must be carried forward before old reports are archived.
- Records retain their narrative, labels and printed paths; Markdown link destinations follow moved files. Raw evidence and generated inventory
  bodies are preserved. The completion check includes navigation, the change-log path map and the full Docker documentation-check output.
- Verified: current active folders audited against the rule and documentation checked before committing. No runtime behavior or test cases changed.
- Undo: revert the instruction update.
