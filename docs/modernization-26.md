# modernize-26 — running change log

Purpose: a running, human-readable record of every change made on the
`modernize-26` branch, written so commit messages can be composed from it
and so any change can be traced or reverted if issues arise later.

Rules for this log:

- One entry per logical change, appended as the change is made, never
  rewritten after the fact. Corrections get a new entry.
- Each entry states: what changed, why, whether behavior changed, how it
  was verified, and how to undo it.
- Suggested commit messages are included per entry. The branch is only
  committed when the maintainer asks; until then this log is the record.
- Changes to framework code under `src/` are conservative by default:
  unreachable code is commented out in place with a
  `CONSERVATIVELY DISABLED` marker and the original code kept verbatim,
  not deleted, so it stays visible for later review.

Baseline facts (established before any change):

- `master` and `modernize-26` started identical at commit `65fdc24`
  ("Replace all kind of dashes", 2020-03-09). 0 ahead / 0 behind, empty diff.
- Version targeted by the code: `composer.json` requires `php >=7.2.1`;
  file docblocks still say PHP >= 7.1. Baseline runtime is therefore
  PHP 7.2, pinned to 7.2.34 in Docker (end-of-life, deliberate).
- All tooling runs in Docker containers defined in this repo. Nothing is
  installed on the host. Service containers (MongoDB, MySQL, S3 emulation,
  RabbitMQ) are Phase 2 and do not exist yet.
- Test philosophy: characterization tests pin current behavior. Expected
  values come from external specifications or independent implementations
  where possible, never from the code under test alone. Suspect behavior
  is flagged in tests, not silently endorsed and not fixed without an
  explicit decision recorded here.

---

## MOD-001 — Docker PHP 7.2 test container

- Files: `docker/Dockerfile`, `docker/docker-compose.yml`,
  `docker/entrypoint.sh`, `.dockerignore`, `.gitignore`
  (added `!/.dockerignore` exception).
- What: `php:7.2-cli` image with Buster apt pointed at
  `archive.debian.org` (Buster is archived upstream), PECL
  `mongodb-1.5.5`, Composer 2.2 LTS copied in. Compose defines a single
  `test` service; `vendor/` and the Composer cache are named Docker
  volumes. The entrypoint runs `composer update` in-container on first
  use if `vendor/bin/phpunit` is missing.
- Why: run the framework on the PHP version it last targeted, in
  isolation, with no host installs.
- Behavior change: none. No `src/` changes.
- Verified: image builds; container reports PHP 7.2.34;
  `mongodb` extension loaded; Composer 2.2.31.
- Run tests with:
  `docker compose -f docker/docker-compose.yml run --rm test`
- Undo: delete `docker/`, `.dockerignore`, and the `.gitignore` line.
- Suggested commit: `Add Docker PHP 7.2 test container (docker/)`

## MOD-002 — PHPUnit 8.5 harness

- Files: `composer.json`, `phpunit.xml.dist`.
- What: added `require-dev: phpunit/phpunit ^8.5` (last PHPUnit series
  supporting PHP 7.2), `autoload-dev` mapping `flat\test\` to `tests/`,
  a `test` Composer script, and `phpunit.xml.dist` defining the `unit`
  testsuite with `src/flat` as the coverage whitelist.
  Runtime `require` block, including `php >=7.2.1`, is untouched.
- Why: begin unit testing without changing what dependent apps install.
- Behavior change: none for library consumers (dev-only additions).
- Verified: `composer update` resolves 49 packages on PHP 7.2 in the
  container; PHPUnit 8.5.56 installed.
- Undo: remove the added `composer.json` keys and `phpunit.xml.dist`.
- Suggested commit: `Add PHPUnit 8.5 test harness (dev-only)`

## MOD-003 — Tier 1 unit tests (36 tests)

- Files: `tests/Unit/Core/UuidTest.php`,
  `tests/Unit/Core/Encode/CsvTest.php`,
  `tests/Unit/Core/Text/EncodeTest.php`,
  `tests/Unit/Core/RandTest.php`, `tests/Unit/Core/RandStrTest.php`,
  `tests/Unit/Input/MapTest.php`.
- What: characterization tests for `\flat\core\uuid`,
  `\flat\core\encode\csv`, `\flat\core\text\encode`, `\flat\core\rand`,
  `\flat\core\rand\str`, `\flat\input\map`. UUID v3/v5 vectors were
  computed with Python's `uuid` module (independent oracle), not from
  this code. Random-producing code is tested by properties (bounds,
  charset, length, version/variant bits) over many draws.
- Why: pin current behavior before any modernization, so regressions
  are caught as diffs against this baseline.
- Behavior change: none.
- Flagged suspect behavior (asserted as-is, labeled in the tests):
  - `\flat\core\uuid::__invoke()` ignores its arguments and always
    returns a v4 UUID.
  - `\flat\core\encode\csv::serialize()` / `::unserialize()` are
    unimplemented and return `null`.
  - `\flat\core\text\encode::unserialize()` throws
    `feature_not_ready` (likely intentional).
  - Observed, not yet test-pinned: `(new \flat\core\rand(5,10))()`
    ignores constructor bounds because `__invoke()` defaults to
    `0..getrandmax()`.
- Verified: `OK (36 tests, 1578 assertions)` in the container.
  Mutation spot-check (in a throwaway container copy, repo untouched):
  changing the UUID v3 version nibble `0x3000` to `0x4000` made exactly
  the 2 v3 tests fail, proving the tests are not vacuous.
  Full `src/` syntax sweep: all 632 PHP files `php -l` clean on PHP 7.2.
- Undo: delete `tests/`.
- Suggested commit: `Add Tier 1 characterization unit tests (36 tests)`

## MOD-004 — Coverage driver in the test image

- File: `docker/Dockerfile`.
- What: PECL Xdebug 2.9.8 is built into the image but not enabled for
  normal runs. Coverage runs load it explicitly:
  `docker compose -f docker/docker-compose.yml run --rm test php -d zend_extension=xdebug.so vendor/bin/phpunit --testsuite unit --coverage-text`
- Why: measure what the tests actually cover.
- Behavior change: none to the framework or default test runs.
- Measured at this point: whole framework 2.13% of lines
  (258/12,110); the six Tier 1 classes between 76.67% and 100%.
- Undo: remove the Xdebug `RUN` block from `docker/Dockerfile`.
- Suggested commit: `Build Xdebug into test image for coverage runs`

## MOD-005 — Coverage top-up tests (+9 tests, 45 total)

- Files: `tests/Unit/Core/UuidTest.php` (`__toString`),
  `tests/Unit/Core/Text/EncodeTest.php` (object input, non-printable
  string), `tests/Unit/Core/RandTest.php` (bad `max`: non-integer,
  greater than `getrandmax()`), `tests/Unit/Core/RandStrTest.php`
  (`generate()`/`__invoke()` called directly, `get_string($len)`,
  options-as-list, `use_loweralpha => false`, non-string charpool).
- Why: cover every line that is reachable on PHP 7.2. Pursuing the
  remaining lines is what exposed MOD-006/MOD-007 — unreachable lines
  in this codebase are a reliable signal of something odd.
- Behavior change: none.
- Verified: `OK (45 tests, 1595 assertions)` (count held after MOD-007).
- Undo: remove the added test methods (listed above).
- Suggested commit: `Top up Tier 1 coverage to reachable-line ceiling`

## MOD-006 — `rand`: pre-PHP-7 fallbacks commented out

- File: `src/flat/core/rand.php` (`_get()`, `bytes()`).
- What: the `if (\PHP_VERSION>=7)` scaffolding was removed; the
  `random_int()` / `random_bytes()` calls are now unconditional. The
  pre-PHP-7 `openssl_random_pseudo_bytes` fallback bodies are commented
  out in place under a `CONSERVATIVELY DISABLED` marker, verbatim,
  per maintainer instruction (conservative over clean, just here).
- Why: the fallbacks were unreachable on every supported PHP version
  (>= 7.2) and were the only thing keeping the class below full
  coverage.
- Behavior change: none on PHP >= 7. On PHP < 7 the class would now
  fail (function not found) instead of using the fallback; PHP < 7 is
  outside the declared `>=7.2.1` requirement and outside the baseline.
- Verified: suite green; class at 100% lines / 100% methods.
- Undo: restore the `if` scaffolding and uncomment the two fallback
  bodies (original code is intact in the comments and in git history).
- Suggested commit: `Comment out unreachable pre-PHP-7 fallbacks in core/rand`

## MOD-007 — Remaining unreachable blocks commented out

Three blocks, same `CONSERVATIVELY DISABLED` treatment as MOD-006
(commented in place, verbatim, with the reason recorded in the comment):

- `src/flat/core/uuid.php`, `uuid::get()` — the `else` throwing
  `invalid_version` could never be entered: it requires a non-empty
  namespace, and a non-empty namespace is the condition selecting the
  other branch.
- `src/flat/core/rand/str.php`, `str::get_string()` — the
  `bad_config` catch guarded a `rand` construction that cannot throw
  `bad_param`: the constructor guarantees at least `min_valid_chars`
  (2) chars, so `$validmax` is always valid. The live construction
  below it validates on its own. (The now-redundant `bad_config`
  exception class is left in place.)
- `src/flat/core/text/encode.php`, `text\encode::data_to_text()` —
  the `if ($text===NULL) return " (unprintable value)";` branch could
  never be selected: on PHP >= 7.2 `json_encode()` returns `false`,
  never `null`, on failure. The unconditional return below it is the
  original `else` body, unchanged. Note this means the
  "(unprintable value)" message was dead in practice: unprintable
  input yields a single space, which the tests characterize.
- Behavior change: none on any reachable path on PHP >= 7.2. The only
  theoretical difference is for inputs that would have made
  `json_encode()` return `null`, which does not occur on this PHP line.
- Verified: `OK (45 tests, 1595 assertions)`; all six Tier 1 classes
  at 100% lines / 100% methods (`uuid`, `rand`, `rand\str`,
  `text\encode`, `encode\csv`, `input\map`). Whole-framework line
  coverage 2.26% (273/12,096).
- Undo: uncomment the three blocks and restore their `if`/`else` or
  `try`/`catch` scaffolding (originals intact in the comments).
- Suggested commit: `Comment out unreachable blocks in uuid, rand/str, text/encode`

---

## Not yet done (awaiting maintainer approval at the Phase 1 gate)

- Phase 2a: routing/controller/config contract tests (the flat-webapp
  surface: `route\factory`, `route\rule`, `core\controller\route`,
  `core\config`) with fixture classes.
- Phase 2b: service containers (MongoDB, MySQL, MinIO for S3,
  RabbitMQ, HTTP stub) and the integration testsuite against them.
- PHP version ladder (7.2 -> newer), one step at a time, re-running the
  suite at each step. The `CONSERVATIVELY DISABLED` blocks are
  deletion candidates only when a later entry here records that
  decision.
- Known quirks awaiting deliberate decisions, not yet fixed:
  `uuid::__invoke()` ignoring arguments, `csv` serializer no-ops,
  `rand::__invoke()` ignoring constructor bounds.
