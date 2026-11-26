# php-7.4 phase: handoff

The brief the owner gave at the start of the phase (2026-11-26), written at the end of php-7.3. It is the
work plan. What is true now is in [README.md](README.md).

## State at the start

- The PHP 7.3 phase is complete: released as `v1.73.0`, `v1.73.1` and `v1.73.2`. flat's `master` is `673df9f`,
  which is `v1.73.2`.
- flat-webapp's `php-7.3` branch locks flat `v1.73.2`. The owner merges it into its `master`.
- flat passes 1,624 tests at 100% coverage on PHP 7.3.33; flat-webapp passes 67.
- The owner creates `php-7.4` from `master` in both repositories, after flat-webapp's `php-7.3` is merged.
  Confirm the branches exist. Work and push only there.

## Rules that override everything (`AGENTS.md`)

- `master` is the maintainer's alone. Never push to, merge into or reset it. Branch creation and merging
  follow their go-ahead.
- Never hide a PHP error: no `@`, no changed `error_reporting`, no suppressed notices, warnings or
  deprecations, no skipped or quarantined tests, no ignore lists. Fix every one at its cause. The approved
  exceptions in `AGENTS.md` are left alone.
- The public API stays compatible. Treat the known consumer as if it still matters.
- Docs are for humans: short and plain. A README says what is true now; history goes in the phase
  `CHANGELOG.md`. Never delete a document; move it to `history/` with a banner.

## Read first

- `AGENTS.md`, [../README.md](../README.md), [../TESTING.md](../TESTING.md).
- In [../php-7.3/](../php-7.3/README.md): the README, the end of the change log,
  [the 7.3 handoff](../php-7.3/HANDOFF.md) (its traps still apply),
  [the environment](../php-7.3/notes/ENVIRONMENT.md), [vendored libraries](../php-7.3/notes/VENDORED.md),
  [consumer impact](../php-7.3/active/CONSUMER-IMPACT.md) and
  [known defects](../php-7.3/active/KNOWN-DEFECTS.md).
- flat-webapp's `AGENTS.md` and `CHANGELOG.md`.

## Plan (mirrors php-7.3)

1. Create this folder with the same skeleton. Update the Progress list in
   [../README.md](../README.md) and the phase pointers in `AGENTS.md`.
2. Move the test environment to PHP 7.4.33, the last 7.4 release, pinned by digest with exact versions:
   - `php:7.4-cli`, `sha256:620a6b9f...`
   - `php:7.4-apache` (flat-webapp), `sha256:c9d7e608...`

   The build checks the PHP version and that `error_reporting` equals `E_ALL`.
3. Run the suite on 7.4 first. PHPUnit turns deprecations into failures, so it finds them. Fix each at its
   cause, with runtime fixes in separate commits from behavior changes.
4. Bring every tool and dependency to the newest release 7.4 allows, one at a time: the full suite, one
   commit and one change-log entry each. Record the result in `notes/ENVIRONMENT.md`.
5. Do the same in flat-webapp.
6. At the end: re-run `composer audit --locked` on both test locks, write the phase's dependency-alert note,
   and prepare the release.

## What the 7.4 step unlocks

Computed on 2026-11-26 under PHP 7.4.33 and ext-mongodb 1.21.0.

Newer test dependencies:

- `azuyalabs/yasumi` 2.4.0 to 2.6.0
- `jms/serializer` 3.29.1 to 3.32.9
- `mongodb/mongodb` 1.16.1 to 1.20.0
- `doctrine/deprecations` 1.1.6 to 1.2.0
- `phpstan/phpdoc-parser` 1.33.0 to 2.4.2
- tools lock: `nikic/php-parser` 4.19.5 to 5.9.0; check that PHPUnit 9.6 accepts it

MongoDB extension: 1.16.2 to 1.21.1 (it needs PHP 7.4 or newer; 1.21.2 and later need 8.1). The pin is a
commit hash in `docker/Dockerfile`; change it together with the `ext-mongodb` platform pin in
`tests/docker/runtime/composer.json`.

Stays put:

- PHPUnit 9.6.39: PHPUnit 10 needs PHP 8.1.
- Xdebug 3.1.6: 3.2 and later need PHP 8.0.
- `aws/aws-sdk-php` 3.337.3: newer needs PHP 8.1.
- `authorizenet/authorizenet` 1.9.9: held on purpose; the wrappers are deprecated and go in v2.X.
- The four Dependabot advisories (`mongodb/mongodb`, `aws/aws-sdk-php`): their fixes need PHP 8.1.
  Revisit the dismissed alerts at the end of the phase. The owner dismissed them as "risk is tolerable".

## Leads for PHP 7.4 deprecations (none confirmed at the start)

- A scan of flat's own `src` found no legacy-order `implode()`, `ReflectionType` string use or
  `money_format`.
- Look at `array_key_exists()` calls whose second argument might be an object (`src/flat/orm.php`,
  `src/flat/core/debug.php`).
- Look at unparenthesised nested ternaries (for example `src/flat/tmpl/data.php`). Deprecated in 7.4, an
  error in 8.0.
- The curly-brace string offset (`$text{$i}`) is already gone from flat's own code.
- Re-check the vendored libraries for 7.4; the vendored-libraries note explains how to update them.

## Decisions to put to the owner early

1. PHP requirement: should the root `composer.json` move from `php >=7.3.0` to `>=7.4.0`? Recommend yes, as
   in 7.3. PHP 7.3 hosts keep getting `v1.73.x`. Check how Composer resolves on each host, as in 7.3.
2. Release: the owner tags; the numbering is `v1.74.0`, then patch releases. After the tag, move
   flat-webapp's lock to the published release.

## Process and traps

- Known defects use `active/KNOWN-DEFECTS.md` (next defect KD-107, next question Q51),
  `active/CONSUMER-IMPACT.md` and `CHANGELOG.md` (next entry MOD-280). The change log is never rewritten.
- Regenerate `TEST-INVENTORY.md` whenever tests change, with the command in the phase README; it ends with
  `chmod 644`.
- Coverage stays at 100% of the code that loads.
- `check-docs`: read its whole output. A final "0 problems" line once hid warnings about an unreadable
  inventory file.
- Test data: build objects inside tests, not in data providers; providers run outside coverage.
- libxml: flat's Authorize.Net client constructor leaves libxml in internal-error mode. Tests that create it
  save and restore the mode.
- The shell's safety check refuses `rm` on a variable path. Do not work around it; use fresh `mktemp -d`
  directories.
- Docker on the cloud machine: after an environment change the daemon can fail with a stale containerd.
  Kill `containerd`, then start `dockerd`; the images are kept.
- `docker/bin/test` starts only the services the selected tests need and retries a failed service start.
  The old RabbitMQ failure is fixed (MOD-272).
- Only a local machine with the owner's sandbox keys can run `docker/bin/test sandbox`; the mock-backed
  `docker/bin/test anet` runs everywhere. Keys are never printed or committed.
