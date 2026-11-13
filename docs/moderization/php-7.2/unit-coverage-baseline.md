# PHP 7.2 unit coverage baseline

## Latest verified checkpoint — MOD-109

Repository-owned command: `./tests.sh all --coverage`.
PHP **7.2.34**, real Xdebug: **1,289 tests / 5,195 assertions pass**.

| Metric | Coverage |
| --- | --- |
| Lines | **100% (7,988/7,988)** |
| Methods | **100% (1,059/1,059)** |
| Classes | **100% (330/330)** |

PHPUnit runtime: **18.11 seconds**, 56 MB, plus report generation and Docker
startup/teardown. The owner-approved subclass guard limit removes the former
brute-force test while retaining the production default of 100,000.
Reports are generated under `build/tests/all/` and are not committed.

All test-type modes were verified: unit 1,243 cases; services 46; mongo 39;
rabbitmq 7. The wrapper tears down its containers/network/test volumes, including
failure paths. All 631 PHP source files pass syntax checks. These are real
line/method/class metrics, not a branch-coverage percentage or a claim that all
legacy functional defects are repaired. flat-webapp functional testing remains
pending. Earlier snapshots below retain their historical observations.


## Historical checkpoint — MOD-104

PHP **7.2.34**, real Xdebug coverage; **1,278 tests / 5,166 assertions pass**.

| Metric | Coverage |
| --- | --- |
| Lines | **99.34% (7,995/8,048)** |
| Methods | **99.34% (1,054/1,061)** |
| Classes | **98.79% (327/331)** |

There are 53 uncovered lines in four files: core/ssh.php (19),
db/driver/mongo.php (17), event/alert.php (2), event/alert/listener.php (15).
Further work requires the three behavior decisions recorded in
[pending-behavior-questions.md](pending-behavior-questions.md).
These are line/method/class metrics, not a branch-coverage percentage.
No excluded files, skipped tests or invented coverage hits were added.

Full run: 9.59 minutes / 56 MB. The real 100,000-tick subscription guard test
accounts for most of the runtime under Xdebug. All 631 PHP source files also pass
PHP-7.2 syntax checks. Changes and test findings are recorded through MOD-104 in
[CHANGELOG.md](CHANGELOG.md). Earlier checkpoints follow.


Verified against `modernize-26` commit `ba9f93d` using PHP 7.2.34,
PHPUnit 8.5.56, and Xdebug 2.9.8. Framework source and the existing tests were
unchanged. Deferred assertion-strength findings are in `testing-follow-ups.md`.

## Results

- 45 tests passed, 1,595 assertions; no failed or skipped tests.
- Line coverage: **273 / 12,096 (2.26%)**.
- Method coverage: **41 / 1,061 (3.86%)**.
- Class coverage: **12 / 331 (3.63%)**.
- All **620** PHP files currently under `src/` passed PHP 7.2 syntax checks.
  Earlier session notes reported 632 files; this count is from the checked-out
  commit and the current completed sweep.

The six original targets have 100% line and method coverage:

| File under `src/flat/` | Covered / executable lines |
| --- | ---: |
| `core/uuid.php` | 65 / 65 |
| `core/rand.php` | 23 / 23 |
| `core/rand/str.php` | 60 / 60 |
| `core/text/encode.php` | 38 / 38 |
| `core/encode/csv.php` | 12 / 12 |
| `input/map.php` | 8 / 8 |

## Where the uncovered lines are

The coverage scope remains the repository's existing `src/flat` whitelist. No
files were excluded to raise the percentage. Major groups from Clover:

| Group | Covered lines | Uncovered lines |
| --- | ---: | ---: |
| `core/` | 265 | 4,988 |
| Bundled `vendor/` | 0 | 1,734 |
| `db/` | 0 | 1,595 |
| `cloud/` | 0 | 1,026 |
| `data/` | 0 | 883 |
| `asset.php` | 0 | 592 |
| `api/` | 0 | 337 |

Coverage work should now target uncovered framework code. Substantial isolated
targets include XML encoding (433 uncovered lines), data mapping (388), and core
configuration (256), plus the collection/mapping helpers they use. Inspect their
dependencies, then cover normal paths, boundaries, and exception paths in focused
batches. Record genuinely unreachable paths or prerequisites as blockers rather
than silently excluding them. Leave the deferred UUID/random edge-case audit for
later unless it blocks coverage work.

Database, cloud, SSH, and other service-dependent paths will need suitable test
doubles or the later containerized integration work. The 1,734 lines of bundled
third-party code remain in the total; their testing policy has not been changed.

## How this run was made

The original full image build failed because the environment proxy denied
`archive.debian.org`. Checks also confirmed that `pecl.php.net` and
`api.github.com` are blocked. Those three domains were saved as additions to the
environment draft; saving did not apply them to the runtime or publish anything.

To complete the unit run, an isolated diagnostic harness was prepared outside
the checkout at `/workspace/flat-environment/php72-tools`. All tooling executed
inside Docker. Its test runtime is the official PHP 7.2 CLI image, pinned to
digest `sha256:42ffbc0798e4449bbd1e14fc4dcb87774aa1ad1900a09ef6a965bc0880aa2161`.
Xdebug was compiled from its official 2.9.8 release commit
`3e09864af111d63b7eb486d88bfc6eb7d6429b85`, using the allowed GitHub source
endpoint and verified TLS with the environment CA bundle.

The isolated Composer manifest installs PHPUnit and its dependencies with a
PHP 7.2.34 platform constraint. Composer installation ran in a tooling container;
the tests themselves ran on PHP 7.2.34. The harness supplies PSR-4 autoloading for
the same framework and test paths, overriding only the bootstrap. It does not
install the full runtime dependency set or the MongoDB extension in the PHP 7.2
test image. This validates the existing pure unit suite, not full dependency or
application readiness. No assertions or coverage filters were weakened.

The diagnostic commands from this earlier checkpoint are superseded. The
repository-owned command is now:

```sh
./tests.sh all --coverage
```

See [docker-testing.md](docker-testing.md) for pinned sources, dependency locks, service types,
proxy CA input and automatic teardown. Earlier counts below remain historical.

## Coverage progress: configuration unit tests (MOD-009)

Added `tests/Unit/Core/ConfigTest.php`: 23 test cases covering initialization,
invalid directories, PHP configuration documents, defaults, normalized paths,
key-name bases, cache hits/misses, JSON reference documents, full-value and inline
references, malformed tokens, nested data, enumeration, and exception paths.
Temporary files and static configuration state are isolated per test and restored.

Results on the same PHP 7.2.34 / PHPUnit 8.5.56 / Xdebug 2.9.8 harness:

| Metric | Initial baseline | With configuration tests |
| --- | ---: | ---: |
| Tests passed | 45 | 68 |
| Assertions | 1,595 | 1,653 |
| Line coverage | 2.26% (273/12,096) | **4.04% (485/12,019)** |
| Method coverage | 3.86% (41/1,061) | **6.22% (66/1,061)** |
| Class coverage | 3.63% (12/331) | **6.04% (20/331)** |
| `core/config.php` lines | 0% | **100% (189/189)** |
| `core/config.php` methods | 0% | **100% (17/17)** |

The original six target classes remain at 100% line and method coverage.
No framework source was modified or excluded. The executable-line denominator
changed because the coverage tool replaces estimates for previously unexecuted
files with runtime coverage information when those files are loaded. It is not a
reduction achieved by deleting code. This legacy driver measures line/method
coverage here; these numbers are not branch-coverage measurements.

The tests pin observed behavior where it differs from the docblocks, including
incomplete reference enumeration and top-level-prefix truncation. They do not
establish that those behaviors are intended or authorize fixes. Before extending
lifecycle tests, the maintainer was asked whether `set_base_dir()` is intended
only for startup or must support switching configurations within one process.
Its current cache-reset behavior makes that distinction relevant. No lifecycle
behavior was changed or assumed.

### Confirmed configuration lifecycle

The maintainer confirmed that `config::set_base_dir()` is called **once per
runtime of a PHP script**, at application startup. Switching configuration
bases within one script is not a supported requirement. Retained caches across
reinitialization must not be treated as a defect on that assumption. The test
setup resets/restores static state only to isolate simulated script runs in the
single PHPUnit process; it does not require applications to reinitialize config.

## Coverage progress: mapper unit tests (MOD-011 / MOD-012)

See `mapper-unit-testing.md` for the confirmed contract, per-edit rationale,
findings, and before/after compatibility check. Mapper reaches **100% lines
(165/165) and methods (12/12)**. Configuration and the original six targets remain
at 100%. Full suite: **95 passing tests, 1,722 assertions**. Overall coverage:
**5.34% lines (641/12,001)**, **7.45% methods (79/1,061)**, **7.25% classes
(24/331)**. No source exclusions were introduced. Unreachable source was retained
in comments; private-access failures remain as explicitly requested.

## Continuing progress through MOD-021

Approved fixes and further unit tests are documented in
`utility-and-event-testing.md`, `data-and-xml-testing.md`, and
`collections-routing-cli-testing.md`. The latest complete run executes **310
tests / 2,366 assertions with 2 errors** exposing collection index copying and
routing subclass cache access. Those behavior decisions are pending; this is not
reported as a passing run. Coverage from the run is **20.07% lines**, **24.67%
methods**, **17.22% classes**. The last fully passing complete run was **264 tests
/ 2,236 assertions** before the new collection/routing regressions were added.

## Continuing progress through MOD-025

The MOD-021 collection/routing errors are resolved with explicit maintainer
confirmation. The latest complete PHP 7.2.34 run is **green: 334 tests, 2,439
assertions**, no errors/failures/skips. Framework coverage is **20.89% lines
(2,311/11,062)**, **26.20% methods (278/1,061)**, **19.64% classes (65/331)**.
Collection class, its constraint and validator controllers, routing controller,
and CLI helper now reach 100% measured lines/methods. Detailed behavior changes,
regressions, and remaining key/range decisions are recorded in
`collections-routing-cli-testing.md` and MOD-022–MOD-025.

## Continuing progress through MOD-029

The confirmed collection key/range contracts are implemented, and the collection
controller trait is 100% lines/methods. Sessions/nonces and the HTTP request,
configuration, and response classes also reach 100% measured lines/methods.
Latest complete run: **404 tests / 2,609 assertions, all passing** on PHP 7.2.34.
Framework: **25.25% lines**, **30.70% methods**, **24.17% classes**. See
`session-and-http-testing.md` for coverage evidence, runner inheritance details,
mock boundaries, and the pending request-format behavior question.

### MOD-030 — Approved JSON/raw request fixes

Docker PHP 7.2.34: **418 tests pass / 2,636 assertions**.
Framework lines **25.26% (2,742/10,855)**, methods **30.70% (326/1,062)**,
classes **24.17% (80/331)**. Request remains **100% lines (37/37)** and
**100% methods (2/2)** after repairing JSON content type, strict encoding-failure
handling, raw-body assignment, and falsy supplied bodies. No exclusions added;
HTTP delivery and flat-webapp integration remain unverified.

### MOD-031/032 — Debug, templates, data, and Markdown

PHP 7.2.34: **451 tests / 2,773 assertions**, all passing.
Lines **33.85% (3,409/10,072)**, methods **35.50% (377/1,062)**,
classes **26.28% (87/331)**. Template data reaches 100% lines/methods.
No exclusions. Newly loaded source replaces static executable-line estimates;
therefore the denominator reduction is not source removal.

### MOD-033 through MOD-035 — SQLite, assets, and JS minifier

**540 tests / 3,000 assertions**, all passing on Docker PHP 7.2.34.
Lines **45.28% (4,358/9,624)**, methods **42.84% (455/1,062)**,
classes **29.31% (97/331)**. Core asset controller: **100% (36/36 lines,
11/11 methods)**. Native SQLite tests exposed/fixed prepare_map's rowCount gate.
Unloaded-file estimates continue to be replaced by runtime counts; no exclusions.

### MOD-036 — API coverage and header getters

**579 tests / 3,182 assertions**, all pass on PHP 7.2.34.
Lines **49.45% (4,675/9,454)**, methods **48.49% (515/1,062)**,
classes **35.95% (119/331)**. API status base and response base reach 100%.
Header getters now return their documented arrays; no exclusions added.

### MOD-037 through MOD-041 — Helpers, text, exceptions, templates, debug exits

**649 tests / 3,371 assertions**, all pass on PHP 7.2.34.
Lines **55.35% (5,204/9,402)**, methods **54.14% (575/1,062)**,
classes **38.37% (127/331)**. Debug, core exception, feature-not-ready, UTF-8
converter, library parameters, and form helper now reach 100% lines/methods.
Actual child-process Xdebug execution is merged for termination paths. No exclusions.

### MOD-042 through MOD-045 — Templates, ORM, AWS boundaries, real images

**689 tests / 3,483 assertions**, all pass using PHP 7.2.34 plus real legacy
Imagick and Xdebug. Lines **59.21% (5,486/9,265)**, methods **57.72% (613/1,062)**,
classes **40.79% (135/331)**. Template controller, ORM, AWS base, image wrapper,
and dimensions reach 100% lines/methods. No skipped image paths or fake native
classes. Test runner now uses the isolated image-enabled diagnostic image.

### MOD-046 through MOD-048 — Real legacy MongoDB coverage

**748 tests / 3,625 assertions**, all pass on PHP 7.2.34 with native MongoDB,
Imagick and Xdebug. Lines **66.86% (5,993/8,964)**, methods **60.55% (643/1,062)**,
classes **43.50% (144/331)**. Native/adapter integration uses an isolated local
MongoDB 4.0 service and test-owned databases. No skipped tests or exclusions.

### MOD-049 through MOD-053 — XML, validation, typed data, phones and facade

**822 tests / 3,898 assertions**, all pass on PHP 7.2.34.
Lines **72.03% (6,346/8,810)**, methods **64.22% (682/1,062)**,
classes **48.04% (159/331)**. XML serializer, DOM, phone, cURL facade, typed loader,
and application error summaries reach 100% lines/methods. No skips/exclusions.
