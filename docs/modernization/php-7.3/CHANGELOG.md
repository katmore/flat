# php-7.3 — running change log

A record of every change made on the `php-7.3` branch, in the format of the php-7.2 log
([../php-7.2/CHANGELOG.md](../php-7.2/CHANGELOG.md)). Numbering continues across phases (MOD-188
is the first entry here).

Rules: one entry per logical change, appended as it is made and never rewritten (a correction is
a new entry); each entry says what changed, why, whether behavior changed, how it was verified
and how to undo it. Entries are grouped by kind, and the kind is named in the heading:
documentation, environment, runtime-compatibility fix, behavior change, modernization (no
behavior change).

Baseline (before any change): `docker/bin/test all` on PHP 7.2.34 passed 1,428 tests
(6,333 assertions), 2026-11-20.

## MOD-188 — Documentation: the phase starts; branch model; D1 revised

- Kind: documentation. No code.
- `docs/modernization/README.md` and `AGENTS.md` now say the php-7.3 phase has started
  (maintainer's go-ahead, 2026-11-20). The README's branch model already states that the
  integration branch `modernize-26` was retired and deleted by the maintainer on 2026-11-20;
  `AGENTS.md` and the handoff say the same. The README also lists `v1.72.2` among the php-7.2
  releases.
- The live documents that named PHP 7.2 now name PHP 7.3 (`README.md`, `docs/modernization/TESTING.md`,
  `AGENTS.md`), and the test image is called `flat-tests-php73` in them. `php-7.2/` and `history/`
  are left as written.
- Decision D1 revised in [HANDOFF.md](HANDOFF.md): the root `php` requirement is the lower bound
  `>=7.3.0`, not `7.3.*`, so PHP 7.4+ hosts can still install flat.
- Verified: `docker/bin/check-docs`. Undo: revert the commits.

## MOD-189 — Environment: PHP 7.2.34 to PHP 7.3.33

- Kind: environment (no library behavior change).
- `docker/Dockerfile`: base image `php:7.3-cli` pinned by digest
  (`sha256:bd6f2dc7a4e4537e927355c94a74c3022911aa504278f5f0fd94016c90dfaecc`, PHP 7.3.33); the
  version check in the image now requires exactly `7.3.33`. The extensions are rebuilt from the
  same pinned commits (xdebug 2.9.8, ImageMagick 6 and imagick, mongodb 1.16.2 with libmongoc);
  all of them build and load on 7.3. The Composer image is unchanged.
- `tests/docker/tools/composer.json` and `tests/docker/runtime/composer.json`: the `platform`
  pin is `php` 7.3.33. Both locks were refreshed with `composer update --lock` under that
  platform: no package moved. Composer also rewrote the download URLs of the locked packages and
  one `plugin-api-version`; those two side effects were not kept, so each lock differs from the
  old one only in `content-hash` and `platform-overrides`, the values Composer computed.
- Root `composer.json`: `php` is `>=7.3.0` (was `>=7.2.1`; decision D1, revised).
- New test `tests/Unit/PhpVersionTest.php`: fails unless the suite runs on PHP 7.3 (shown failing
  on the old 7.2 image). There is no version guard in the library.
- Comments and usage text that named PHP 7.2 now name 7.3 (`bin/`, `docker/bin/`, tool headers,
  `PHP version >=7.3` in four source file headers; `src/flat/asset.php` was left untouched).
- Renamed: the test image `flat-tests-php72` is now `flat-tests-php73`
  (`docker/docker-compose.yml`). No file moved.
- Verified: `docker/bin/test all --coverage`, 1,429 tests, 100% classes (331/331), methods
  (1,062/1,062) and lines (8,047/8,047), after MOD-190. Versions: [notes/ENVIRONMENT.md](notes/ENVIRONMENT.md).
- Undo: revert the commit (the old image digest and pins are in the php-7.2 branch).

## MOD-190 — Runtime-compatibility fix: `continue` in a switch (JShrink)

- Kind: runtime-compatibility fix, no behavior change.
- On the first 7.3 run, `JsMinifierTest` failed with `"continue" targeting switch is equivalent
  to "break". Did you mean to use "continue 2"?` (new in PHP 7.3).
  `src/flat/vendor/tedious/JShrink/minifier.php`, `loop()`: the `continue;` after
  `saveRegex()` sits in two nested switches, so it only ever left the inner one, like `break`.
  It is now written `break;`, with a comment. Output is identical by construction.
- Cause fixed, nothing hidden. The question whether the author meant `continue 3` (a behavior
  change) is Q37 in [active/KNOWN-DEFECTS.md](active/KNOWN-DEFECTS.md).
- Verified: the whole suite passes unchanged (1,429 tests).
- Undo: change `break;` back to `continue;` (it fails again on 7.3).

## Found, not changed: the AWS SDK deprecation switch-off in two tests (Q38)

- Not a change. While reading the test setup for 7.3 I found that `AwsConfigTest` and
  `S3MultipartTest` set `AWS_SUPPRESS_PHP_DEPRECATION_WARNING=true`, which switches off a
  deprecation notice the SDK emits for every PHP below 8.1. That is a break of prime directive 2
  that the v1.72.1 audit missed. It cannot be fixed at its cause in this phase, so it is
  recorded with a recommendation as Q38 and left as it is until the owner decides.

## MOD-191 — Modernization (no behavior change): `array_key_first()` in the Mongo `load` options

- Kind: modernization, no behavior change. Record: [notes/MODERNIZATIONS.md](notes/MODERNIZATIONS.md) (M1).
- `src/flat/db/driver/mongo/crud.php`, option `start`: `array_keys($opt['start'])[0]` is now
  `array_key_first($opt['start'])` (PHP 7.3). The line before checks that the option is an array
  with exactly one element, so the key is the same.
- Verified: the tests pass unchanged (1,429 tests, 100% coverage), and the API comparison shows
  no difference ([notes/API-COMPARISON.md](notes/API-COMPARISON.md)).
- Undo: put `array_keys($opt['start'])[0]` back.

## MOD-192 — Verification record (no change to code)

- Kind: verification. Recorded 2026-11-20 on PHP 7.3.33.
- `php -l` with all errors reported, on every PHP file under `src`, `tests`, `bin` and `docker`:
  no warning or deprecation (a positive control, the old JShrink file, shows the `continue`
  warning). Static searches for `compact()`, case-insensitive `define()`, `mb_ereg*` aliases,
  `fgetss` and heredoc markers in `src` found nothing to change.
- API comparison with `v1.72.0`: no difference ([notes/API-COMPARISON.md](notes/API-COMPARISON.md)).
- Mutation sample: 40 mutants, 80% caught ([active/MUTATION-PROBE.md](active/MUTATION-PROBE.md)).
- Consumer check on flat-webapp and its run on 7.3 ([notes/CONSUMER-CHECK.md](notes/CONSUMER-CHECK.md)).
- Dependency audit of both locks: no change, no alert fixable on 7.3
  ([notes/DEPENDENCY-ALERTS.md](notes/DEPENDENCY-ALERTS.md)).
- Prime directive 2 search repeated: the only `error_reporting` change is the approved mask in
  `src/flat/asset.php`; no `@`; the one `--ignore-platform-req=ext-sockets` in the Dockerfile is the
  documented archive-fetch stage (ext-sockets is built in the runtime image, see TESTING.md).
  Two findings recorded as questions, nothing hidden or changed: Q38 and Q39.
- flat-webapp moved to PHP 7.3.33 on its `php-7.3` branch (separate repository; commit message
  "Move to PHP 7.3").

## MOD-193 — Documentation: the branch-model README no longer mentions `modernize-26`

- Kind: documentation, by the maintainer (commit `da67d6c`). MOD-188 says the README states the
  retirement of `modernize-26`; since this change it does not. The README now only describes
  the live model (phase branches, `master` merged by the maintainer). The retirement stays
  recorded in `AGENTS.md` and in [HANDOFF.md](HANDOFF.md).
- Nothing else changed.
