# php-7.2 — running change log

Purpose: a running, human-readable record of every change made on the
`php-7.2` branch, written so commit messages can be composed from it
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

## MOD-008 — Reproduce the PHP 7.2 baseline and prioritize coverage gaps

- Files: `docs/unit-coverage-baseline.md`, `docs/testing-follow-ups.md`.
- What: independently reproduced the existing 45 tests and measured coverage;
  recorded the uncovered-code inventory and deferred assertion-strength findings.
- Why: resume the interrupted testing work with verified numbers and prioritize
  increasing coverage over investigation of already-covered behavior.
- Behavior change: none. Framework source, tests, dependency declarations, and
  the coverage whitelist are unchanged. The earlier untracked release-plan
  document is preserved.
- Verified: PHP 7.2.34, PHPUnit 8.5.56, Xdebug 2.9.8;
  `OK (45 tests, 1595 assertions)`; 273/12,096 lines (2.26%); the six initial
  target classes retain 100% line/method coverage. All 620 current `src/` PHP
  files passed syntax checks. Exact current-cloud commands, reports, and harness
  limitations are recorded in `unit-coverage-baseline.md`.
- Environment limitation: the full repository Docker build is blocked by network
  policy. Required domains were saved in the draft, not applied or published.
  An isolated Docker harness ran the unchanged pure unit suite without installing
  the library's full runtime dependencies. Do not mistake that for application
  readiness or successful full-image validation.
- Undo: remove the two new documentation files and this entry. Diagnostic
  artifacts live outside the checkout under `/workspace/flat-environment`.
- Suggested commit: `Document verified PHP 7.2 unit coverage baseline and deferred findings`

## MOD-009 — Reach full configuration-controller coverage with unit tests

- Files: `tests/Unit/Core/ConfigTest.php`, `docs/unit-coverage-baseline.md`.
- What: added 23 configuration test cases exercising public loading, caching,
  defaults, reference transformation, enumeration, and exception behavior using
  temporary files. Tests save/restore static state for isolation.
- Why: increase coverage of previously uncovered framework code rather than
  expand the deferred audit of classes already at 100%.
- Behavior change: none. No source files or runtime requirements were changed,
  and no coverage exclusions were added. Observed quirks are characterized rather
  than silently fixed. The maintainer was asked about configuration-directory
  switching semantics before adding lifecycle expectations.
- Verified: same Docker PHP 7.2.34 harness and coverage command as MOD-008;
  `OK (68 tests, 1653 assertions)`. Configuration: 100% lines (189/189), 100%
  methods (17/17). Overall: 4.04% lines (485/12,019), 6.22% methods (66/1,061),
  6.04% classes (20/331). The six existing targets remain at 100%.
- Metric note: newly loaded files replace static uncovered-line estimates with
  runtime counts; the denominator changed without changing or excluding source.
- Undo: remove `ConfigTest.php` and the MOD-009 report/log entries.
- Suggested commit: `Cover configuration controller loading, references, and failure paths`

## MOD-010 — Record the maintainer's configuration lifecycle contract

- File: `docs/unit-coverage-baseline.md`.
- Decision: `config::set_base_dir()` is called once per runtime of a PHP script,
  at startup. Directory switching within a running script is not a requirement.
- Why: resolve purpose with the maintainer before imposing lifecycle assertions
  or proposing cache-reset behavior changes.
- Behavior change: none; documentation only. Test isolation simulates independent
  script runs inside PHPUnit and does not change the application contract.
- Verification: maintainer confirmation; existing test results unchanged.
- Undo: remove the lifecycle clarification and this entry.
- Suggested commit: `Document once-per-script configuration initialization`

## MOD-011 — Add mapper characterization tests and capture its contract

- Files: `tests/Unit/Core/MapperTest.php`, `docs/mapper-unit-testing.md`,
  `docs/unit-coverage-baseline.md`.
- What: 27 cases for property read/write and reflection access, ignored failures,
  marker interfaces, exports, positional/named arguments, input mapping, and
  iteration. Preserved errors are tested explicitly.
- Decisions obtained from the maintainer: `each_property()` is public-only and
  its access flags are obsolete; preserve misspelled reflection-call failures
  rather than fixing private-access behavior in this batch.
- Learned: dynamic-property exports differ from declared-property exports on
  PHP 7.2; missing selected properties omit the name from their exception message;
  protected selected-property iteration fails during reflection reads. Details
  and deferred decisions are recorded in `mapper-unit-testing.md`.
- Behavior change: none from tests. No assertions were weakened to enable a
  source behavior change. Expectations pin observed behavior where intent is not
  established, and explicitly confirmed boundaries where it is.
- Verified: full suite passes against the original HEAD mapper implementation:
  `OK (95 tests, 1722 assertions)` on PHP 7.2.34. Original source was temporarily
  used only for that differential check, then the cleaned version was restored.
- Undo: remove MapperTest.php and its documentation entries.
- Suggested commit: `Add mapper property mapping and conversion characterization tests`

## MOD-012 — Comment out unreachable mapper branches conservatively

- File: `src/flat/core/mapper.php`; rationale in `docs/mapper-unit-testing.md`.
- What: retained obsolete iteration-filter and inaccessible-property write blocks
  in CONSERVATIVELY DISABLED comments; likewise disabled impossible type-failure
  branches after successful string/object casts. Live casts remain unchanged.
- Why: cover the mapper's reachable implementation without enabling obsolete
  flags, deleting historical code, or concealing coverage gaps with exclusions.
- Behavior change: none on supported PHP 7.2 paths. The public-only contract is
  documented; all misspelled reflection calls and associated failures remain.
- Verified: cleaned implementation passes the same 95 tests / 1,722 assertions
  as original HEAD. Mapper: 100% lines (165/165), 100% methods (12/12).
  Full framework: 5.34% lines (641/12,001), 7.45% methods (79/1,061),
  7.25% classes (24/331). Configuration and the original six targets remain 100%.
- Undo: restore the disabled blocks and the original combined setter cast/check
  from their comments or HEAD; remove the added contract comment. Keep the tests
  when checking compatibility of any future rewrite.
- Suggested commit: `Comment out unreachable mapper branches without changing access behavior`

## MOD-013 — Expand unit coverage across utilities, XML, and events

- Files: `tests/Unit/Core/Util/{UtilityTest,ValueTest,ListcheckTest}.php`,
  `tests/Unit/Core/Xml/EncodeTest.php`, `tests/Unit/Core/EventTest.php`,
  `docs/utility-and-event-testing.md`.
- What: added characterization tests for copying/aliasing and recursive conversion,
  option checks, time conversions, relative-time units, reserved names, string
  conversion, list membership, XML output, and event dispatch/failure semantics.
- Decision: maintainer confirms the last supplied value defines the list; no
  conventional append-member rewrite was made. Other contract questions remain
  pending and do not authorize guessing or changing behavior.
- Learned: falsy copy inputs become null, default object clones are shallow,
  scalar-validation options are not applied, time-ago objects ignore their saved
  reference time, XML checksums hash null, and listener payloads have a defined
  priority. Details and pending decisions are in the new testing document.
- Behavior change: none. This batch changes tests/docs only; no new exclusions.
- Verified: `OK (164 tests, 1915 assertions)` on PHP 7.2.34. Deepcopy, reserved,
  timecode conversion, associative-option checks, and core events reach 100%
  lines/methods. Framework: 8.43% lines (991/11,756), 10.84% methods
  (115/1,061), 10.27% classes (34/331). Other targets remain in progress.
- Undo: remove these new test files and their documentation entries.
- Suggested commit: `Characterize utility conversions, XML encoding, and event dispatch`

## MOD-014 — Apply approved scalar-validation and list-callback fixes

- Files: `src/flat/core/util/validate/value.php`,
  `src/flat/core/util/listcheck/factory.php`, and their utility tests.
- Maintainer decisions: require_scalar must return scalar inputs and honor fallback
  options; list rejection must invoke the callback registered with
  set_fail_handler. The last supplied member still defines the list.
- What: validate the actual value instead of undefined variables; merge recognized
  options; preserve scalar zero/false/empty strings; support boolean validation
  mode and explicit falsy fallbacks. Dispatch the stored callback with factory
  and rejected value via call_user_func instead of calling a nonexistent method.
- Behavior change: intentional and approved. require_scalar previously returned
  null for all inputs; the registered list callback previously failed dispatch.
  The declared but unimplemented `exception` flag is not implemented by this
  fallback fix; its old no-throw behavior is unchanged.
- Verification: regression selection fails on original HEAD code (10 cases,
  1 error and 9 failures, exit 2), then both fixed files were restored. Full
  suite subsequently passed: 216 tests / 2,118 assertions on PHP 7.2.34.
  Value checks and list factory reach 100% line/method coverage.
- Undo: restore these two source files from HEAD and revise the regression
  expectations only with an explicit compatibility decision.
- Suggested commit: `Fix scalar validation and registered list failure callbacks`

## MOD-015 — Test configurable relative-time units without deleting a live path

- Files: `tests/Unit/Core/Util/UtilityTest.php`.
- Decision: the maintainer confirms subclasses may customize the protected unit
  table. The no-matching-unit return is reachable and must remain active.
- What: a subclass fixture temporarily installs a minimum-unit table, verifies
  null when no unit matches, restores it in finally, then checks default units.
- Behavior change: none; no time_ago source changes were needed.
- Verified: time_ago reaches 100% lines (29/29) and methods (4/4).
- Undo: remove the custom-unit fixture and test.
- Suggested commit: `Cover customized relative-time units and no-match return`

## MOD-016 — Correct numeric-string detection after maintainer confirmation

- Files: `src/flat/data/mapper.php`, `tests/Unit/Data/MapperTest.php`.
- What: require is_numeric before integer/float comparisons in
  strings_to_scalar(true). Added regressions for ordinary and partially numeric
  strings, plus data mapping/conversion/interface tests described in the testing
  progress documents.
- Why: on PHP 7.2, 0 == "hello" is true; nonnumeric strings previously became
  numeric zero, then null through the scalar wrapper. The maintainer confirmed
  nonnumeric strings must be preserved as the docblock describes.
- Behavior change: intentional and approved. Numeric detection no longer treats
  nonnumeric or partially numeric text as an integer/float. Scalar-wrapper empty
  handling remains separate and is pending a maintainer decision.
- Verified: the new data-conversion test failed with null instead of "hello"
  before the fix; afterward the complete suite passed (216 tests, 2,118
  assertions). The regression cases include hello, 12cats, 1.5x, and HTML text.
- Undo: remove the is_numeric guards and revert the approved expectations only
  after a new compatibility decision.
- Suggested commit: `Preserve nonnumeric strings during scalar conversion`

## MOD-017 — Enable approved mapping defaults and scalar fallbacks

- Files: `src/flat/data/mapper.php`, `src/flat/data/scalar.php`,
  `tests/Unit/Data/{MapperTest,ScalarTest}.php`.
- Decision: defaults and default_on_empty must work; PHP-empty scalar inputs must
  receive value_on_empty, including the normal empty-string fallback.
- What: iterate actual defaults, read actual rule flags, and assign scalar fallback
  via the parent constructor. Regression tests cover missing, present, empty, and
  prefixed mapping, invalid rules, repeated mapping, and scalar fallback types.
- Behavior change: intentional and approved. Defaults now apply; empty scalar
  wrappers now expose the fallback instead of an uninitialized null value.
- Verified: full suite passes on PHP 7.2.34; 264 tests / 2,236 assertions at the
  latest combined run. Scalar wrapper has 100% measured lines/methods.
- Undo: revert these specific defaults/fallback edits; retain the independent
  numeric-detection fix from MOD-016 unless separately reversed.
- Suggested commit: `Apply mapping defaults and assign scalar empty-value fallbacks`

## MOD-018 — Repair approved XPath node-list iteration

- Files: `src/flat/core/xml/dom/nodelist.php`, `tests/Unit/Core/Xml/DomTest.php`.
- What: initialize the parent iterator and pass childNodes when creating child
  lists. Previously the constructor called itself with the wrong argument type.
- Behavior change: intentional and approved; XPath results become usable instead
  of raising TypeError. DOM node/key/position semantics are tested explicitly.
- Verified: indexed queries, iteration/rewind, child text access, noninteger/missing
  indices, and repeated XPath calls pass in the full PHP 7.2 suite.
- Undo: restore the two constructor/child-list call sites and restore the failure
  characterization only after a new compatibility decision.
- Suggested commit: `Fix XPath result construction and child iteration`

## MOD-019 — Add approved Flat XML 0.3 typed metadata and decoding

- Files: `src/flat/core/xml/encode.php`, `tests/Unit/Core/Xml/{EncodeTest,DomTest}.php`.
- Decisions: decode serializer output; add metadata to preserve otherwise
  indistinguishable falsy/null values. This explicitly changes the wire format.
- What: version 0.3 type/key attributes, DOM-based recursive decoder, assoc mode,
  legacy attribute fallbacks, structure-node filtering, and string-zero/whitespace
  preservation. XML-specified class names are not instantiated.
- Behavior change: approved format/API change. Decoder previously errored on
  nonexistent methods. Old documents' already-lost distinctions are unrecoverable.
  Detailed compatibility boundaries are in docs/data-and-xml-testing.md.
- Verified: typed round trips for scalar roots, nested arrays/objects, falsy values,
  keys, escaping, whitespace, and assoc mode pass. A legacy-format fixture passes.
  Full suite: 264 tests / 2,236 assertions; PHP runtime remains 7.2.34.
- Undo: revert the decoder and metadata/string-handling changes and restore format
  constant 0.2; adjust format-specific expectations only with maintainer approval.
- Suggested commit: `Support typed Flat XML 0.3 round trips and legacy decoding`

## MOD-020 — Extend DOM, data, UTF-8, image-dimension, and HTML coverage

- Files: new/expanded tests under Unit/Data, Unit/Core/Xml, Unit/Core/Html, and
  Unit/Core/Util, plus docs/data-and-xml-testing.md.
- What: test documented data interfaces/conversions, DOM XML/HTML/file input,
  repeated-node grouping, image scaling arithmetic and aliases, UTF-8 fixing,
  scalar display, HTML labels/escaping/layout, and error paths.
- Learned: documented aliasing, repeated empty-node conversion, scalar fallback,
  XPath construction, and XML value-loss findings in the relevant testing docs.
- Behavior change: none from this test batch; approved source fixes are recorded
  separately in MOD-014 through MOD-019.
- Verified: 264 tests, 2,236 assertions, no failures/skips. Overall 15.06% lines,
  19.11% methods, 14.80% classes. HTML encoding is now 100% lines/methods.
  The retained local test helper reuses a dedicated labeled PHP 7.2 container to
  avoid repeated container-create overhead; it does not install host tooling.
- Undo: remove only the new tests/docs for this batch; source fixes have separate
  undo descriptions. Original ephemeral helper is retained as run-tests.ephemeral.
- Suggested commit: `Expand data, DOM, UTF-8, image scaling, and HTML unit coverage`

## MOD-021 — Expand collection, routing, CLI, and event-factory tests

- Files: `tests/Unit/Core/{CollectionTest,EventFactoryTest,CliTest}.php`,
  `tests/Unit/Core/Route/ControllerTest.php`,
  `docs/collections-routing-cli-testing.md`.
- What: isolated fixtures exercise routing contracts and controller interfaces,
  collection iteration/ranges/callbacks, CLI argument/output interfaces, and event
  factory lifecycle. No flat-webapp or external service integration was started.
- Learned: deepcopy loses index zero, callbacks are duplicated, collection
  constraint/validator interfaces are inactive, static copying uses $this, and
  routing subclasses fail private-cache access. CLI parsing/formatting quirks are
  documented in the relevant new testing document.
- Behavior change: none from this batch; no source was modified for these newly
  discovered collection, routing, or CLI issues. Decisions were requested where
  changing behavior could affect consumers. No failing assertion was disabled.
- Verified: focused CLI/event-factory run passes 17 tests / 57 assertions. Complete
  run executes 310 tests / 2,366 assertions with 2 errors: missing copied member
  due to lost index zero, and routing subclass cache access. No skipped tests.
  This complete run is not claimed as passing.
- Coverage from that run: 20.07% lines (2,221/11,065), 24.67% methods
  (262/1,062), 17.22% classes (57/331). Event factory and repaired XPath node-list
  wrapper are 100% lines/methods. Collection/routing completion is pending answers.
- Undo: remove these new test files and their documentation entries. No source
  rollback is needed for this batch.
- Suggested commit: `Characterize collection, routing, CLI, and event factory behavior`

## MOD-022 — Preserve copy values and repair collection copies/index zero

- Files: `src/flat/core/util/deepcopy.php`, `src/flat/core/collection.php`,
  `src/flat/core/collection/controller.php`, copy/DOM tests and relevant docs.
- Decision: copying must work; avoiding index-zero errors takes priority. The
  maintainer answered the falsy-copying question in that context.
- What: preserve falsy scalar values/empty arrays, initialize member storage to
  an array, use source members/index in static load_copy, normalize object
  destination selectors to class names, and preserve independent member wrappers.
- Behavior change: intentional and approved. Falsy copied data no longer becomes
  null; index zero survives; static collection copying no longer accesses $this.
  Default object-copy depth and arr(null) behavior are unchanged.
- Learned: this shared helper also caused loss of the first empty repeated XML
  value; preserving values repairs that downstream case without XML-specific hacks.
- Verified: copy destinations, invalid/abstract fallbacks, empty/fresh collections,
  zero/falsy members, independent wrapper identity, and DOM repeated empty strings.
  Collection class is 100% lines/methods. Full suite: 334 tests / 2,439 assertions.
- Undo: revert these specific copy/storage edits and approved expectations; note
  that doing so reintroduces index-zero loss and the two prior downstream errors.
- Suggested commit: `Preserve falsy copy values and repair collection copying`

## MOD-023 — Enforce approved collection constraints and validator hooks

- Files: both collection member constraint/validator controllers,
  `tests/Unit/Core/Collection/ConstraintTest.php`, and collection testing docs.
- Decision: declared class/interface constraints and validator interfaces must
  actually be enforced, even though the broken implementation previously let
  invalid members pass.
- What: qualify interface names, validate actual class names for class constraints,
  retain the source collection in the validator controller, and delegate its
  declared validation/filter methods correctly.
- Behavior change: intentional compatibility change. Invalid members now fail;
  validation/filter/ignore/success hooks operate. Plain callback duplication is
  left unchanged. No new policy for undocumented return shapes was introduced.
- Verified: matching/mismatching class and interface constraints; malformed
  definitions; validator pass/failure/exception/ignore results; transformed success
  values. Constraint controller: 53/53 lines, 5/5 methods. Validator controller:
  24/24 lines, 3/3 methods. Full suite passes 334 tests / 2,439 assertions.
- Undo: revert these controller edits and their explicitly approved regression
  expectations; this disables enforcement again.
- Suggested commit: `Activate collection constraints and delegate validator hooks`

## MOD-024 — Repair approved routing-controller subclass cache access

- File: `src/flat/core/controller/route.php`, routing tests and docs.
- Decision: routing-controller subclasses, including custom SPECIAL_NODES sets,
  are supported.
- What: access private special-node maps through the base-owned self storage;
  retain existing hashes so different node sets stay separated.
- Behavior change: intentional repair; subclasses no longer raise private-cache
  access errors. Base controller transformation behavior remains covered.
- Verified: base/default/reserved transformations, repeated cache hits, and a
  subclass with a different node set. Routing controller: 137/137 lines, 4/4
  methods. Full suite passes 334 tests / 2,439 assertions.
- Undo: restore the two late-static map accesses; this restores the subclass error.
- Suggested commit: `Support routing subclasses with base-owned node caches`

## MOD-025 — Complete CLI coverage with a wrapper test and dead-code comments

- Files: `src/flat/core/cli.php`, `tests/Unit/Core/CliTest.php`, relevant docs.
- What: retain the unused private argv helper and impossible zero-argument printf
  branch in CONSERVATIVELY DISABLED comments. Test getopt result/operand splitting
  using a controlled namespaced primitive, restoring globals afterward.
- Why: no source/test callers use the private helper, and a required format
  argument prevents execution of printf's zero-argument branch.
- Behavior change: no supported public behavior changes; no exclusions added.
  Existing CLI quirks remain documented, not silently fixed.
- Verified: CLI 204/204 lines, 24/24 methods. Full PHP 7.2.34 suite passes
  334 tests / 2,439 assertions. Overall: 20.89% lines (2,311/11,062),
  26.20% methods (278/1,061), 19.64% classes (65/331).
- Undo: restore the private helper and printf branch from their comments; remove
  only the new wrapper test/fixture if reverting this logical change.
- Suggested commit: `Complete CLI unit coverage and retain dead code in comments`

## MOD-026 — Apply confirmed collection iteration and range contracts

- Files: `src/flat/core/collection/controller.php`,
  `tests/Unit/Core/CollectionTest.php`, and collection coverage documentation.
- Decisions: named members return registered keys; unkeyed members return numeric
  positions. Range limit is a count of members, not an exclusive end position.
- What: shared key lookup uses the member position, preserves a registered numeric
  key of zero, and returns null after exhaustion. Range computes a clipped end
  from start + count, normalizes negative starts before validation, throws the
  declared out_of_range exception, and supplies consistent named/numeric callback
  keys. Range calls do not advance the collection iterator.
- Behavior change: intentional and approved. Named keys no longer become false,
  unkeyed keys become positions instead of null, nonzero-start ranges return the
  requested count, and unkeyed range callbacks no longer search a null index map.
  Empty collections consistently reject a normalized out-of-range start.
- Verified: 338 tests / 2,454 assertions pass on PHP 7.2.34. Collection controller
  reaches 78/78 lines and 14/14 methods. Zero named keys, mixed indexing,
  exhaustion, clipped ranges, default/large limits, negative starts, callback keys,
  and boundary exceptions are covered. Framework: 20.98% lines (2,321/11,062),
  26.55% methods (282/1,062), 20.24% classes (67/331).
- Undo: revert this specific key/range patch and its approved expectations while
  retaining the earlier storage/copy/validation repairs. Original file line endings
  were preserved to avoid unrelated source churn.
- Suggested commit: `Return collection keys and interpret range limits as counts`

## MOD-027 — Cover session and nonce lifecycle in isolated PHP processes

- Files: `tests/Unit/Core/Session/SessionTest.php`, `src/flat/core/session.php`,
  `docs/session-and-http-testing.md`; local test helper/config outside checkout.
- What: test session storage/start/refresh/destroy/snapshots and nonce creation,
  consumption, TTL/corrupt data, strong-randomness failures, and missing globals.
  Each test uses a separate PHP process and temporary storage.
- Source change: retain the impossible wrong-instance guard after new nonce_meta
  in a CONSERVATIVELY DISABLED comment. Active integrity/error checks remain.
- Runner diagnosis: isolated tests passed without coverage; with coverage they
  lacked Xdebug. Fixed local PHP_INI_SCAN_DIR inheritance instead of disabling
  process isolation, assertions, or coverage. Parent/child PHP remain 7.2.34.
- Learned: failed storage currently retries then returns an empty id while session
  status remains NONE; this error policy is documented, not silently changed.
- Verified: session 114/114 lines, 12/12 methods. Latest combined suite passes
  404 tests / 2,609 assertions; process-isolated coverage is included.
- Undo: remove the session test file, restore the commented guard if desired,
  and restore the previous local helper; note that old helper cannot collect
  isolated-process coverage without an inherited Xdebug configuration.
- Suggested commit: `Cover isolated PHP sessions and nonce lifecycle failures`

## MOD-028 — Characterize HTTP request/configuration without outbound traffic

- Files: `tests/Unit/Core/Curl/{RequestTest,ConfigTest}.php`,
  `docs/session-and-http-testing.md`.
- What: URL validation, form/JSON/raw request construction, configuration types,
  cache paths, and selector/default handling.
- Learned: malformed JSON content type, inverted raw-content checks/missing body,
  overwritten text defaults and Chrome selector. Asked about request format
  intent before changing its current public behavior.
- Behavior change: none; source request/configuration files are unchanged.
- Verified: request 36/36 lines, 2/2 methods; config 23/23 lines, 1/1 methods.
  Exact legacy format expectations are labeled as current behavior, not endorsed.
- Undo: remove these new tests and related documentation entries.
- Suggested commit: `Characterize HTTP request formats and configuration validation`

## MOD-029 — Cover response handling with controlled cURL primitives

- Files: `tests/Unit/Core/Curl/ResponseTest.php`, HTTP testing documentation.
- What: real allocated handles with controlled execution/results/options exercise
  files, cookies, statuses, DOM/XPath, robots rules, errors, metadata, and cleanup.
  No outbound traffic or external service credentials are used.
- Learned: body transport is not established by current header setup; missing
  info exception definition is absent; get_dom's lazy condition is suspect.
  Findings are documented for later decisions rather than expanding this patch.
- Behavior change: none; response source is unchanged and no exclusions were added.
- Verified: response 183/183 lines, 10/10 methods. Full PHP 7.2.34 suite passes
  404 tests / 2,609 assertions. Framework: 25.25% lines (2,741/10,854),
  30.70% methods (326/1,062), 24.17% classes (80/331).
- Undo: remove the response tests and their namespace primitive fixtures/docs.
- Suggested commit: `Cover HTTP response wrappers and resource cleanup without network calls`

## MOD-030 — Repair JSON and raw request construction

- Files: `src/flat/core/curl/request.php`, `tests/Unit/Core/Curl/RequestTest.php`,
  `docs/session-and-http-testing.md`, `docs/unit-coverage-baseline.md`.
- Authorization: maintainer approved both request-format fixes.
- Behavior change: JSON uses application/json. Encoding failure uses a strict
  false check, so valid JSON `0` succeeds. JSON/raw modes process supplied falsy
  bodies; NULL retains its existing meaning of no supplied body. Form encoding's
  empty-input behavior is unchanged.
- Raw mode requires a nonempty string content type and assigns the supplied body
  unchanged, including empty strings, zero, false, and embedded NUL bytes.
  Invalid content types continue to raise bad_param. Corrected constructor docs.
- Tests: replace malformed-format characterization with intended behavior;
  cover falsy JSON/raw values, invalid content types, binary text, and omitted
  bodies. Against unchanged source these regressions produced 2 errors and
  11 failures (28 tests). Retained diagnostic: local request-before-fixes.txt.
- Verified: Docker PHP 7.2.34, PHPUnit 8.5.56, Xdebug 2.9.8; full suite passes
  418 tests / 2,636 assertions. Request: 37/37 lines, 2/2 methods (100%).
  Framework: 25.26% lines (2,742/10,855), 30.70% methods (326/1,062),
  24.17% classes (80/331). No coverage exclusions added.
- Learned: PHP empty() and truthiness reject legitimate encoded/raw zero values;
  no-body and encoding-failure checks must distinguish NULL and false explicitly.
  These tests validate construction, not actual outbound body delivery.
- Undo: revert only this entry's request source/test changes; retain earlier
  session, collection, and other modernization work.
- Suggested commit: `Fix JSON content type and preserve supplied raw request bodies`

## MOD-031 — Cover debug events and data interface application

- Files: `tests/Unit/Core/DebugTest.php`, `tests/Unit/Data/MapperTest.php`,
  `tests/Unit/Tmpl/DataTest.php`; no source behavior changes in this entry.
- What: suppression/config gates, display/dump/message events, caller metadata,
  creation times, supported user-error levels, CLI output, a throwing kill handler,
  webview/rule-consumer interfaces, template array/property mapping and falsy data.
- Learned: debug formatting selects incorrect templates; webview discards the
  HTML-encoded clone; field_list(instance) reaches an unset ReflectionClass.
  Tests label these current failures rather than silently inventing semantics.
- Isolation: save/restore debug/config static state; no mid-runtime directory
  switching is introduced. Kill coverage stops at a deliberately throwing handler,
  not at the normal process-exit path. No claim of complete debug coverage.
- Verified with MOD-032: 451 passing tests / 2,773 assertions. Template data is
  100% lines/methods. Overall line coverage 33.85% (3,409/10,072), methods
  35.50% (377/1,062), classes 26.28% (87/331).
- Undo: remove the new test files and the added data mapper fixtures/cases.
- Suggested commit: `Cover debug events and template data access`

## MOD-032 — Exercise templates and repair PHP 7 Markdown execution

- Files: `tests/Unit/Core/Template/ControllerTest.php`,
  `tests/Unit/Core/Md/ConvertTest.php`,
  `src/flat/vendor/erusev/src/Parsedown/Parsedown.php`,
  `src/flat/core/md/convert/file.php`,
  `src/flat/core/md/convert/exception/invalid_source.php`.
- What: PHP/HTML/HTM/Markdown/generated Markdown templates, chunked files,
  class/object designs, checking, formatting, logger, prefixes/bases, callbacks,
  constructor/prepared interfaces, Markdown string/file/extra parsing and errors.
- Repairs: brace Parsedown's dynamic handler method name for unambiguous PHP 7
  parsing; fully qualify invalid_source's existing parent and file-loader throw.
  Tests originally failed with dynamic-call Error and a fatal missing parent.
  No parser upgrade, syntax redesign, or PHP version change.
- Learned/deferred: absolute template path resolution uses a null local variable;
  display's declaration extends an interface; some failure paths retain output
  buffers. More coverage and intended-behavior clarification remain needed.
- Verified: full Docker PHP 7.2.34 suite 451 tests / 2,773 assertions, no failures,
  errors, or skips. Lines 33.85% (3,409/10,072), methods 35.50% (377/1,062),
  classes 26.28% (87/331). Source loading replaces static line estimates; the
  denominator decrease is not code deletion. No coverage exclusions were added.
- Undo: revert the three narrowly scoped source corrections and remove new tests.
- Suggested commit: `Cover template rendering and fix PHP 7 Markdown handler dispatch`

## MOD-033 — Cover PDO using native SQLite and retain valid SELECT rows

- Files: `tests/Unit/Db/Pdo/{PdoTest,ConnectionTest,StatementTest}.php`,
  `src/flat/db/driver/pdo/pdo.php`, database/assets/minifier testing document.
- What: native in-memory SQL, binding/execution, record mapping/callbacks, SQL file
  loading/permissions/errors, cache and force-new lifecycle, providers, SQL builder
  criteria/joins/order/limits and current explicit-fetch failures.
- Behavior change: prepare_map fetches rows without consulting driver-dependent
  SELECT rowCount; empty rows still produce the existing empty-array/null result.
  Real SQLite initially returned [] despite rows; regression now asserts the rows.
- Learned: the separate statement builder has its own zero-rowCount and undefined
  data_class defects; its fetch options always throw. Owner intent requested.
  No MySQL/service readiness claim. Native driver is not mocked.
- Verified checkpoint with MOD-034/035: 540 tests / 3,000 assertions, all pass.
- Undo: restore only prepare_map's rowCount block and remove the new PDO tests.
- Suggested commit: `Cover native PDO wrappers and fetch SELECT rows independently of rowCount`

## MOD-034 — Characterize bundled JavaScript minification and failure cleanup

- Files: `tests/Unit/Vendor/JsMinifierTest.php`; minifier source unchanged.
- What: comments/licenses/strings/escapes/regexes/operators/line endings; malformed
  inputs throw and restore buffers; a subsequent valid invocation succeeds.
- Learned: conditional one-line comments lose their newline; documented for later.
  No JavaScript execution or complete minifier correctness claim from PHP coverage.
- Verified checkpoint with MOD-035: 540 passing tests / 3,000 assertions.
- Undo: remove the new test file.
- Suggested commit: `Cover JavaScript minifier token handling and buffer cleanup`

## MOD-035 — Cover asset resources, registration, cascading, and dependencies

- Files: `tests/Unit/Asset/{AssetTest,CascadeTest}.php`, related testing document.
- What: real temporary files/permissions, URL/hash/type/inline/data URI output,
  positions/filtering/deduplication, dependencies, cascading, first-instantiation
  behavior, callbacks, errors, aliases, and current stub/cyclic behavior.
- Behavior change: none. Special HTML registration purpose requested from owner;
  tests inspect the self dependency without running an infinite queue.
- Learned: inline source resource state and directory flag checks need follow-up;
  source keeps its current behavior while tested and documented.
- Verified: core asset controller 36/36 lines and 11/11 methods (100%). Full suite
  540 tests / 3,000 assertions. Framework lines 45.28% (4,358/9,624), methods
  42.84% (455/1,062), classes 29.31% (97/331); no exclusions or PHP upgrade.
- Undo: remove the asset tests/docs; no production source changes in this entry.
- Suggested commit: `Cover asset registration and class cascading with local filesystem fixtures`

## MOD-036 — Cover API dispatch, statuses, responses, and repair header getters

- Files: `tests/Unit/Api/{ControllerTest,ResponseTest,StatusTest}.php`,
  `src/flat/api/response.php`; testing follow-up/baseline docs.
- What: native interface dispatch including HEAD/TRACE/any fallback, global and
  explicit response handlers, constructor input/callback paths, response-bearing
  exceptions, status classes/numeric boundaries/formatting, response payloads,
  inferred messages, header replacement, invalid status/error accessors.
- Behavior change: get_headers now returns its header array (or []), and
  get_header_strings returns array_values of its string array (or []). Their
  previous logical-OR expressions returned bool and raised TypeError against the
  declared array return types. Recorded pre-fix failure in local diagnostic file
  headers-before-fix.txt. No HTTP server or header transport claimed.
- Learned: status formatter reads string offsets instead of CODE_CLASSES metadata;
  its type/description getters use unmatched tokens. TRACE loses dynamic input
  fields through existing mapper export. Request-method getter references a field
  never assigned; HEAD state persists. Constructor error callbacks have undefined
  data/context variables. These remain documented, not silently redesigned.
- Verified: full PHP 7.2.34 Docker suite passes 579 tests / 3,182 assertions.
  API response base 10/10 lines, API status base 30/30 lines (100%); many concrete
  responses/statuses also 100%. Framework lines 49.45% (4,675/9,454), methods
  48.49% (515/1,062), classes 35.95% (119/331). No exclusions.
- Undo: revert the two getter expressions and remove the new API tests.
- Suggested commit: `Cover API dispatch and return arrays from response header getters`

## MOD-037 — Cover library requirements and HTML form generation

- Files: `src/flat/lib.php`, `tests/Unit/Lib/LibTest.php`,
  `tests/Unit/Core/Html/FormTest.php`, debug/templates/text testing docs.
- Fixes: recurse over individual nonempty parameter keys instead of the same list;
  give missing_dependency its normalized class array when input was a string.
  Pre-fix tests hit recursive Error and TypeError; local lib-before-fixes.txt kept.
- Tests: valid/error parameter paths, dependencies/metadata, escaped form fields,
  nested maps, unsupported types, serializer's current discarded return, decode.
- Learned: false/zero remain nonempty-required failures; form serialize's missing
  return is deferred, not silently changed to a new contract.
- Verified checkpoint MOD-041: library 45/45 lines, 7/7 methods; form 36/36, 4/4,
  both 100%. Full suite 649 tests / 3,371 assertions passes.
- Undo: revert the two library expressions and remove the new tests.
- Suggested commit: `Cover helper requirements and fix list recursion and dependency errors`

## MOD-038 — Cover real UTF-8 byte conversions and Markdown grammar

- Files: `tests/Unit/Vendor/{EncodingTest,MarkdownTest}.php`; no source changes.
- What: real iconv/encoding inputs, nested arrays/BOMs/labels; Markdown block and
  inline grammar, Extra features, options and repeated parser instances.
- Learned: repeated footnotes receive numbered back-reference ids; tests check
  actual grammar behavior rather than an assumed single-reference id.
- Verified: UTF-8 converter 85/85 lines, 11/11 methods (100%). Full suite passes
  at MOD-041; Markdown parsers still have uncovered paths.
- Undo: remove the two new test files.
- Suggested commit: `Cover UTF-8 conversions and bundled Markdown grammar`

## MOD-039 — Return stored exception metadata and cover unavailable contexts

- Files: `src/flat/core/exception.php`, `tests/Unit/Core/ExceptionTest.php`.
- Fix: getter reads $this->_exception_data rather than an undefined local.
- Tests: explicit/derived codes, nested associative/list metadata and explicit,
  object/class/method/function/caller feature-not-ready descriptions.
- Verified: pre-fix metadata regression failed; repaired core exception 11/11
  lines, 3/3 methods; feature_not_ready 22/22 lines (100%).
- Undo: restore the original getter and remove the new tests.
- Suggested commit: `Return stored exception metadata and cover failure context messages`

## MOD-040 — Repair explicit template paths and display-class resolution

- Files: `src/flat/core/controller/tmpl.php`, `src/flat/tmpl/display.php`, expanded
  `tests/Unit/Core/Template/ControllerTest.php`.
- Fixes: resolve absolute file from $design; use flat/tmpl/display's namespace;
  implement output interface instead of extending it as a class.
- Tests: real absolute PHP rendering and display-class check/render; controlled
  open/read/close/file-content failures, native successful I/O, cleaned resources.
- Verified: template controller 186/191 lines (97.38%); complete suite passes at
  MOD-041. No template version/API signature or PHP upgrade.
- Undo: revert these three source expressions and remove only the added cases.
- Suggested commit: `Resolve absolute template files and display classes correctly`

## MOD-041 — Cover actual debug exits and disable proven unreachable fallbacks

- Files: `src/flat/core/debug.php`, expanded `tests/Unit/Core/DebugTest.php`.
- What: same-runtime child scripts test exit statuses/default/custom handlers;
  shutdown reports real Xdebug data appended through PHPUnit's collector. Web
  formatting uses a child-local platform constant; no HTTP integration claim.
- Cleanup: retain three impossible fallback statements in CONSERVATIVELY DISABLED
  comments with caller/type proofs. No live error handling or kill options fixed.
- Learned: Xdebug collection must extend through shutdown for exit paths. Current
  kill options require an unintended nested shape; that remains a follow-up.
- Verified: debug 152/152 lines, 13/13 methods (100%). Full PHP 7.2.34 suite
  649 tests / 3,371 assertions passes. Framework 55.35% lines (5,204/9,402),
  54.14% methods (575/1,062), 38.37% classes (127/331). No exclusions added.
- Undo: restore the commented fallback statements and remove subprocess cases;
  earlier debug event tests may remain.
- Suggested commit: `Cover debug process exits and retain unreachable fallbacks as comments`

## MOD-042 — Complete template configuration and Markdown failure coverage

- Files: `tests/Unit/Core/Template/ControllerTest.php`; no production changes.
- What: configured/fallback design roots and a controlled Markdown file-read
  failure that reaches the resolver's existing exception path. Configuration
  fixtures save/restore static state; this does not permit runtime reconfiguration.
- Verified: template controller 191/191 lines, 16/16 methods (100%).
- Undo: remove these added cases and the Markdown read primitive fixture.
- Suggested commit: `Complete template root and read-failure coverage`

## MOD-043 — Cover ORM lifecycle and retain an impossible mapping guard

- Files: `tests/Unit/Orm/LifecycleTest.php`, `src/flat/orm.php`.
- What: explicit storage-hook fixtures test create/read/update/delete support,
  before hooks, cached existence/done flags, false/string failures, named/positional
  keys/data and current delete-after-read behavior. No database driver is mocked
  as a working service; these are controller lifecycle unit tests.
- Cleanup: original is_array(boolean-expression) nested mapping attempt retained
  in a CONSERVATIVELY DISABLED comment; || always produces bool, never array.
  The existing always-taken mapping expression remains live. CRLF preserved.
- Learned: delete checks read_done, crud_error copies details into its op field,
  and nested constructor mapping needs a separately defined contract. Deferred.
- Verified: ORM 97/97 lines, 10/10 methods (100%) at MOD-045 checkpoint.
- Undo: restore the commented guard and remove the new ORM tests.
- Suggested commit: `Cover ORM lifecycle and retain unreachable nested mapping guard`

## MOD-044 — Cover AWS client boundaries and S3 request construction

- Files: `tests/Unit/Cloud/AwsTest.php`; production AWS source unchanged.
- What: provider/explicit client type checks and caching, missing client errors,
  canonicalized keys/prefixes, body and small-file upload arguments/waiters,
  local file/permission failures, providers/URLs/constructors and current read stub.
- Fixtures: explicit client doubles and legal subclasses widen property visibility
  and define a test client class. No SDK class is fabricated, no credentials or
  outbound cloud calls used. Actual AWS SDK interoperability remains unverified.
- Learned: multipart code uses AWS SDK v2 classes although Composer requires v3;
  threshold comment says 10MB but constant is 10GiB. Multipart coverage remains.
- Verified: AWS base 16/16 lines, 2/2 methods (100%); S3 81/94 lines, 9/10 methods.
- Undo: remove the new test file; no AWS production code changed.
- Suggested commit: `Cover AWS client providers and S3 request construction without network calls`

## MOD-045 — Enable native image tests and preserve resize proportions

- Files: `src/flat/core/util/image.php`,
  `tests/Unit/Core/Util/Image/ImageTest.php`; local diagnostic Dockerfile/helper
  under `/workspace/flat-environment/php72-tools`, documented testing setup.
- Environment: built real ImageMagick 6.9.10-23 at upstream commit
  e97831dfdfc064c5246cd7ba195ff6ecd4958f34 and Imagick 3.4.4 source at
  42c07db5d0075b26471397fb6de8ffb4bd2692d8 on the existing PHP 7.2.34/Xdebug
  image. TLS verification and supplied CA retained. No apt-policy bypass or PHP
  version change. Imagick's Git source reports @PACKAGE_VERSION@ metadata;
  the source tag/commit and actual native image processing establish this build.
- Local default run-tests now selects flat-unit-coverage-images:7.2 and a separate
  labeled container. Original helper retained as run-tests-without-imagick.
- Tests: actual blobs/base64/PPM save/load, geometry/page isolation, growing and
  shrinking with fixed single-axis methods, minimum bounds and constructor paths.
- Fixes: fixed-axis methods compute the other dimension from the original ratio
  rather than scaling through a zero maximum dimension (which made squares).
  Minimum resize compares width instead of accidentally assigning it in its guard.
  Pre-fix run: six failures; repaired native geometry assertions pass.
- Pending owner purpose: constructor minimum-vs-target sizing semantics, especially
  both dimensions currently doing nothing. That policy has not been guessed.
- Verified: image 60/60 lines, 9/9 methods; dimensions 57/57, 7/7 (100%). Full
  PHP 7.2.34 suite passes 689 tests / 3,483 assertions. Framework 59.21% lines
  (5,486/9,265), 57.72% methods (613/1,062), 40.79% classes (135/331).
- Undo: restore prior image resize expressions/remove image tests; select the
  retained old helper if returning to the old diagnostic image.
- Suggested commit: `Test native image operations and preserve proportional resize geometry`

## MOD-046 — Add real legacy MongoDB test capabilities

- Files: local diagnostic Dockerfile.mongo, bootstrap-runtime.php, run-tests-mongo
  and isolated dependency manifest/lock; `docs/legacy-test-environment.md`.
- What: compiled native extension 1.5.5 and pinned C-driver source; installed
  MongoDB library 1.4.3/adapter 1.2.5 for PHP 7.2.34 in a separate directory.
  Started a labeled MongoDB 4.0 container on an internal network, no host ports.
- Local default runner now uses the image with MongoDB/Imagick/Xdebug, combined
  autoload bootstrap and explicit local test URI. Earlier runners are retained.
- Limits: MongoDB TLS disabled in the diagnostic build, not a production runtime
  recommendation; no remote TLS or flat-webapp functional readiness claim.
- Verified: real insert/read/update/delete/commands work through the legacy
  adapter and modern library; full MOD-048 suite passes with native extensions.
- Undo: select prior local helper, stop/remove only these labeled diagnostic
  containers/network if no longer needed; remove their local manifests/images.
  Repository dependency files and unrelated containers were not changed.
- Suggested commit: `Document the PHP 7.2 MongoDB coverage harness`

## MOD-047 — Repair bad-interface exception declarations without dropping names

- Files: new `src/flat/core/bad_interface.php`,
  `src/flat/core/exception/bad_interface.php`, its `message.php`,
  `tests/Unit/Core/BadInterfaceTest.php`.
- Fix: put existing flat/core/bad_interface at its autoloadable path; the expected
  flat/core/exception/bad_interface extends that legacy interface. Message class
  implements the controller interface rather than illegally extending it.
- Compatibility: both interface names remain usable; exception/message formatting
  quirks are characterized, not redesigned. Existing reason/method loss deferred.
- Verified: both names autoload; MongoDB bad-provider errors are usable exceptions;
  full MOD-048 suite passes.
- Undo: revert declaration changes/remove new interface/test file; this restores
  the old autoload/fatal declaration failures.
- Suggested commit: `Make bad-interface exceptions autoload while preserving legacy names`

## MOD-048 — Cover MongoDB CRUD and honor create's namespace option

- Files: `tests/Unit/Db/Mongo/{CrudTest,DriverTest}.php`,
  `src/flat/db/driver/mongo/crud.php`, MongoDB/environment testing docs.
- What: real temporary databases exercise legacy/modern clients, collections,
  shape/class/clone/callback reads, filters/sorting/limits/counts, namespaced CRUD,
  upserts/duplicates/invalid writes, update modes, load windows and validations.
  Additional pure context/affected-response fixtures test defensive policies.
- Fix: create reads collection_ns from its actual $option argument, not undefined
  $param. The documented alias failed before repair; sub_collection still works.
- Learned: adapter connect is lazy, getConnections returns [], and command failures
  become error arrays. Owner asked whether connect=true must verify/retry; no
  connection-policy change guessed. Existing count_only affected-record failures,
  single-record return shapes, setter missing-function errors are characterized.
- Isolation: separate compatible dependency autoloader; each integration fixture
  owns/drops a random flat_unit_* database. Save/restore static client caches;
  expired-ping cases change timestamps instead of sleeping for a minute.
- Verified: 57 MongoDB tests / 133 assertions pass. Complete PHP 7.2.34 suite:
  748 tests / 3,625 assertions, all pass. Framework 66.86% lines (5,993/8,964),
  60.55% methods (643/1,062), 43.50% classes (144/331); no exclusions or skips.
- Undo: restore create's old option reference and remove new MongoDB tests;
  retain unrelated coverage/source repairs.
- Suggested commit: `Cover MongoDB CRUD and honor collection namespace on create`

## MOD-049 — Cover error summaries and typed/history/media data

- Files: `tests/Unit/Lib/AppErrorTest.php`, `tests/Unit/Data/TypedTest.php`.
- What: scalar/object/array/resource/error/truncated summaries; missing-key forms;
  relative/absolute/missing type loaders, discriminator stripping, media defaults,
  supplied history timestamps and generated timestamps bounded by actual time.
- Source behavior change: none. Tests use native encoding and real data models.
- Verified at MOD-053: app_error 32/32 lines, 4/4 methods and typed loader 22/22,
  4/4 (100%). Full suite 822 tests / 3,898 assertions passes.
- Undo: remove these two test files.
- Suggested commit: `Cover application error summaries and typed data loading`

## MOD-050 — Complete XML/DOM coverage and close structure metadata attribute

- Files: `src/flat/core/xml/{encode,dom}.php`, expanded XML/DOM tests.
- Fix: close a missing quote in structure metadata when a non-scalar meta option
  falls back to stdClass structure. Regression originally produced malformed XML.
- Tests: legacy indexed/scalar decoding, named structures/floats, unusual keys,
  real resource fallback warnings/checksums, malformed files, warning policies,
  libxml restoration and a supported no-document subclass override.
- Cleanup: retain impossible private-loader guards, fixed enabled private type
  options, absent namespace-identifier fallback, numeric conversion fallback and
  nonempty var_dump alternate encodings in CONSERVATIVELY DISABLED comments.
  Each comment records the caller/type/native-function invariant, original code
  remains. No invented hits or exclusions.
- Learned: resource fallback is live on PHP 7.2 (htmlspecialchars warns/returns
  null). Its checksum still uses undefined input, hashing empty data; this and
  structure metadata retaining only the last member are deferred, not redesigned.
  PHP 7.2 forbids throwing __toString and method calls on uninitialized native
  iterators; invalid test approaches were removed, not kept as fake coverage.
- Verified: encode 295/295 lines, 16/16 methods; DOM 140/140, 22/22 (100%).
- Undo: restore the missing-quote expression/commented guards and remove only the
  added cases; retain previously approved XML 0.3 round-trip repairs.
- Suggested commit: `Complete XML and DOM coverage and repair structure metadata quoting`

## MOD-051 — Cover validation and repair filter/error-helper wiring

- Files: `tests/Unit/Core/Util/ValidationTest.php`,
  `src/flat/core/util/validate/match/filtervar/valid_filter_check.php`,
  `src/flat/core/util/validate/match/regexp/exception/invalid_regex/bad_pattern.php`.
- Fixes: pass the filter enumeration as one list-valued member to set_list, in
  accordance with the owner's last-supplied-member contract; throw the existing
  concrete invalid_filter instead of instantiating an abstract exception; use the
  existing preg_error::preg_errtxt helper instead of a missing preg_errtxt class.
- Tests: strict true validation, stored strings/defaults, ignored fallback options,
  regex patterns/definitions/config failures, actual PCRE errors, valid/invalid
  filter IDs. Pre-fix filter/syntax regressions retained in local diagnostics.
- Pending owner purpose: base options and PCRE execution-error exception policy.
  Non-string regex member ArgumentCountError remains characterized for later.
- Verified: targeted 11 tests / 34 assertions and full MOD-053 suite pass.
- Undo: revert the two filter changes and error-helper reference/remove new tests.
- Suggested commit: `Cover validation and repair filter enumeration and regex error helpers`

## MOD-052 — Characterize phone parsing and retain duplicate impossible prefix

- Files: `tests/Unit/Data/PhoneTest.php`, `src/flat/data/phone.php`.
- What: NANP/local/unicode dashes, explicit/protected input paths, empty/short
  values, extension extraction, international prefixes and retained metadata.
- Cleanup: retain the second identical 99534 alternative in a CONSERVATIVELY
  DISABLED comment; the immediately preceding branch already handles that value.
  No numbering, territorial, extension or country-code policy was changed.
- Learned: many country-code statements compare rather than assign; extension
  extraction can copy the base number; final string overwrites formatted metadata.
  These are labeled current behavior and deferred for owner-guided repair.
- Verified: phone 119/119 lines, 2/2 methods (100%); full MOD-053 suite passes.
- Undo: restore the duplicate branch and remove the new phone test file.
- Suggested commit: `Characterize phone formatting and retain unreachable duplicate prefix code`

## MOD-053 — Cover cURL facade and retain unreachable callback scaffolding

- Files: `src/flat/core/curl.php`, expanded `tests/Unit/Core/Curl/ResponseTest.php`.
- Cleanup: callback is neither declared nor assigned in exec, so its callable/
  array conditions are unreachable. Original scaffolding retained in a
  CONSERVATIVELY DISABLED comment; undefined-local notices disappear. No callback
  API is invented, and existing request/configuration arguments remain intact.
- Tests: URL and config-object facade paths use existing controlled cURL
  primitives; no outbound HTTP. Cookie argument propagation remains a follow-up.
- Verified: facade 10/10 lines, 1/1 method (100%). Complete PHP 7.2.34 suite passes
  822 tests / 3,898 assertions. Framework 72.03% lines (6,346/8,810),
  64.22% methods (682/1,062), 48.04% classes (159/331); no skips/exclusions.
- Undo: restore commented facade scaffolding/remove the added facade case.
- Suggested commit: `Cover cURL facade and retain unreachable callback scaffolding`

## MOD-054 — Exercise payment wrappers against the pinned SDK

- Added `tests/Unit/Cloud/AuthorizeTest.php` and `AuthorizeCustomerTest.php`.
- External diagnostic dependencies now include Authorize.Net 1.9.9, matching
  repository composer.lock, resolved for PHP 7.2.34; repository dependencies and
  runtime version are unchanged. Controllers/CIM network boundaries are explicit
  doubles. Authentication and response DTOs use the real SDK. No payment calls.
- Tests cover authentication, successful/rejected/null responses, transport
  exceptions, parameter metadata, customer reads/create/update/delete validation,
  validation-mode aliases, and the current no-op convenience operations.
- Learned: profile_Customer drops SDK fields initialized to null because it uses
  isset; payment arrays instantiate an abstract class; payment mutation IDs are
  unassigned; transaction array normalization always rejects; convenience methods
  are unfinished. These findings are characterized, not silently repaired.
- A maintainer question asks whether these legacy wrappers should be repaired or
  retired. No payment behavior or source code changed in this batch.
- Verified: Cloud suite 57 tests / 182 assertions passes on PHP 7.2.34.
- Undo: remove the two new test files and optional external SDK dependency.
- Suggested commit: `Characterize Authorize.Net wrappers using the existing SDK`

## MOD-055 — Cover alert subscriptions, listener delivery and shared resolvers

- Added Core/AlertTest.php and EventResolverTest.php. Explicit provider doubles
  record persistence/channel calls; real AMQPMessage and AMQPTimeoutException
  types drive deterministic message delivery, loop exit and expiry refresh.
- Covers queue suffix/provider/fallback, finite/infinite subscription TTLs,
  generated IDs, listener registration, JSON delivery, timeout recovery, current
  no-op log/subscription methods, and shared resolver handlers/listeners.
- Learned: resolver set_data is a no-op; alert trigger references undefined meta
  and driver variables and is unfinished. Thread listener requires absent pthreads
  and contains incomplete synchronization/disconnection work. These remain open.
- Verified: selected alert/resolver tests pass, 11 tests / 61 assertions.
- Undo: remove the two new test files.
- Suggested commit: `Cover alert subscriptions and resolver delivery boundaries`

## MOD-056 — Restore documented RabbitMQ returns and exercise a real broker

- Files: src/flat/db/driver/rabbitmq.php, new Unit/Db/RabbitMqTest.php.
- Fixes: _param_to_queue_param returns its mapped queue parameters; _load_conn
  returns the newly established connection on its first call. Both previously
  fell through to null, breaking initial channel creation and queue declaration.
- Tests: actual isolated RabbitMQ 3.8.34 with php-amqplib 2.12.3 on PHP 7.2.34;
  real connect/channel reuse, queue declaration, publish/read-back, routing,
  message objects, command dispatch, provider overrides, invalid parameters and
  configuration defaults. Service details are in legacy-test-environment.md.
- Fixtures use the public constructor for normal initialization. A reflection
  fixture exposes default pre-connect state for get_connection validation and
  provider setup because the parent constructor is final and connects immediately.
- Learned: broker queue flags cannot change on redeclaration; tests explicitly
  delete the first queue before using different auto-delete flags. Every test
  deletes its own queue even if its channel closed during a protocol error.
- Undo: remove the two return statements, new test file and optional local broker.
- Suggested commit: `Return RabbitMQ connection and queue parameters and test broker operations`

## MOD-057 — Cover exception and failure metadata

- Added tests/Unit/Data/ErrorDetailsTest.php: validation field lists, not-found
  lookup context, random failure lengths, CLI status/usage/data, IP lookup errors
  and Authorize.Net child-class mismatch diagnostics.
- Learned: data/validate_failure currently ignores its message argument and does
  not initialize its exception message. Tests label this behavior; no invented fix.
- Verified: 5 tests / 32 assertions pass on PHP 7.2.34.
- Undo: remove the new test file.
- Suggested commit: `Cover framework error metadata and validation field lists`

### MOD-056 full verification checkpoint

- Full suite after MOD-054–057: 892 tests / 4,165 assertions, all passing.
  Lines 78.84% (6,767/8,583); methods 71.00% (754/1,062); classes 53.17%
  (176/331). RabbitMQ 93/93 executable lines and 11/11 methods; Authorize.Net
  base 10/10 executable lines and 2/2 methods. Counts use actual Xdebug execution;
  runtime-loaded files replace the initial static executable-line estimates.
- The payment exception-wrapping test runs in a separate PHP process: print_r
  on an exception includes its trace arguments, which otherwise includes the
  entire PHPUnit suite and can exhaust PHP's 128 MiB memory limit. No production
  exception formatting was changed or memory limit raised to conceal this issue.

## MOD-058 — Cover IP lookup cache, parsing and failure boundaries

- Added Unit/Cloud/IpInfoDbTest.php; extracted existing cURL test functions into
  tests/Support/CurlDouble.php, reused by ResponseTest and the new lookup tests.
- Tests cover fresh/undated cache reuse, stale/missing cache refresh, semicolon
  field/location mapping, persistence upsert, HTTP status/transport error context,
  magic-property fallback and currently empty static load(). No external HTTP or
  IP location service/credentials are used. Persistence is an explicit double.
- Verified: 7 tests / 35 assertions pass. No framework source changed.
- Undo: remove the lookup test and move shared cURL doubles back into ResponseTest.
- Suggested commit: `Cover IP lookup caching and controlled HTTP failures`

## MOD-059 — Characterize shutdown priority behavior before owner-directed repair

- Added Unit/Core/ShutdownTest.php. Covers named/default/invalid priorities,
  integer ordering, unique handles, repeated invocation, collision shifting and
  priority-ceiling enforcement with real callback registration.
- Confirmed equal weights lose a shifted callback and do not implement the
  documented newest-first ordering. Explicit repeated invocation currently
  repeats callbacks. No shutdown policy was changed while the question is pending.
- Test snapshots restore static state so registered PHP shutdown closures see
  no test callbacks at process exit. Six tests / seven assertions pass.
- Undo: remove the new shutdown test file.
- Suggested commit: `Characterize shutdown callback ordering and collision loss`

## MOD-060 — Cover ORM map specifications and deprecated asset options

- Added Unit/Data/OrmMapTest.php and Unit/Asset/LegacyOptionsTest.php.
- ORM map tests verify object identity/precedence, construction from data/type,
  and missing/invalid class specifications. Asset option tests retain list/key
  flags, false-valued options, null fallback and deprecated no-op toggles.
- Verified: 8 tests / 25 assertions pass. No framework source changed.
- Undo: remove the two new test files.
- Suggested commit: `Cover ORM mapping specifications and legacy asset options`

## MOD-061 — Cover configuration providers using real SDK and SQLite types

- Added Unit/Db/ConfigProvidersTest.php, Unit/Cloud/AwsConfigTest.php and shared
  tests/Support/ConfigSandbox.php, which creates namespace files and restores
  every static configuration cache after each test (one startup per test runtime).
- Mongo getters use an explicit nonconnecting subclass; PDO uses native SQLite
  initialization. AWS uses an actual S3 client/credentials object without sending
  requests. Bucket and connection/options getters are asserted.
- External diagnostic dependencies now resolve AWS SDK 3.337.3 under the pinned
  PHP 7.2.34 platform. Root composer.lock remains unchanged (its current 3.402.6
  requires PHP 8.2 and cannot be used for this baseline). No framework SDK upgrade.
- AWS's documented PHP-deprecation notification toggle is enabled only inside the
  configuration test and restored afterward; framework warnings are not suppressed.
- Verified selected provider tests: 3 tests / 15 assertions pass.
- Undo: remove the new provider tests/support fixture and optional external SDK.
- Suggested commit: `Cover database and AWS configuration providers on PHP 7.2`

### MOD-061 full verification checkpoint

- Full suite: 916 tests / 4,247 assertions pass. Lines 82.13% (6,973/8,490),
  methods 74.11% (787/1,062), classes 56.80% (188/331).

## MOD-062 — Cover operation-specific error diagnostics

- Added Unit/Core/LegacyErrorMessagesTest.php. Tests SSH authentication,
  connection, SFTP and process errors, database operation and driver failures,
  configuration reference coordinates, scalar-default reasons, thread context
  and ignored collection-member string/property/method behavior.
- Characterizes two legacy SSH message constructors that replace their prefix
  when extra details are supplied. No SSH functionality or messages changed.
- Verified: 37 tests / 80 assertions pass on PHP 7.2.34.
- Undo: remove the new test file.
- Suggested commit: `Cover operation diagnostics and exception metadata`

## MOD-063 — Expand Markdown grammar regression cases

- Expanded Unit/Vendor/MarkdownTest.php with multi-level/excess-depth headings,
  closed fences followed by paragraphs, loose lists, lazy blockquotes, nested and
  self-closing HTML, malformed inline syntax, shortcut references, inline comments,
  pipe tables, repeated definitions, setext attributes, ordered/unused footnotes
  and nested markdown-enabled HTML.
- Verified: Markdown suite 36 tests / 112 assertions passes with real parsers.
- No parser code changed; no grammar expectations were inferred from coverage.
- Undo: remove the added corpus entries.
- Suggested commit: `Exercise Markdown grammar edge cases and nested HTML`

## MOD-064 — Cover payment data normalization and SDK adapters

- Added Unit/Cloud/AuthorizeDataTest.php. Tests interval lengths, occurrence
  defaults/coercion, date formatting/timezone selection/errors, subscription
  credit-card/bank adapters and amount DTOs using real SDK classes.
- Legal response subclasses expose the optional status_int field expected by
  the base data_ready method. Tests cover every mapped status, unknown status,
  explicit-value precedence and SDK success/failure metadata extraction.
- Learned: valid non-Denver timezone conversion references an unqualified,
  nonexistent DateTimeZone class; transaction factories also refer to undefined
  failure/status variables, and some classes declare a concrete loader as an
  interface. These remain part of the pending legacy-wrapper support decision.
- Tests filter only E_USER_DEPRECATED notices emitted while loading actual
  deprecated SDK files; other errors delegate to PHPUnit's previous handler.
  Framework warnings are not suppressed. No gateway calls or payment code fixes.
- Verified: 23 tests / 68 assertions pass on PHP 7.2.34.
- Undo: remove the new test file.
- Suggested commit: `Characterize payment data normalization and legacy SDK adapters`

### MOD-064 full verification checkpoint

- Full suite: 991 tests / 4,438 assertions pass. Lines 84.72% (7,159/8,450),
  methods 79.47% (844/1,062), classes 67.37% (223/331).

## MOD-065 — Cover API fallback resources and additional failure contexts

- Expanded Api/ResponseTest.php and Core/LegacyErrorMessagesTest.php. ETag header
  formatting and forbidden/not-found any-method resources have direct assertions;
  resolver/image/controller/session/ORM-driver errors and upload/broker causes
  retain expected operation context.
- Verified selected suites: 81 tests / 240 assertions pass. No source changes.
- Undo: remove added test cases.
- Suggested commit: `Cover fallback API resources and remaining error contexts`

## MOD-066 — Characterize view resolution and validation-controller failure

- Added Core/Template/ViewTest.php and ValidateControllerTest.php; ConfigSandbox
  exposes its temporary directory for real file-resolution fixtures.
- View tests search actual leaf/ancestor files and observe event payloads. Existing
  undefined resolved variable and its native implode warning are captured
  explicitly and asserted, with unrelated errors delegated to PHPUnit.
- A normal validation collection fails because the controller reads private
  member::$data. This failure and invalid argument rejection are characterized;
  no validator behavior was invented to get past the failure.
- Verified: 4 tests / 10 assertions pass.
- Undo: remove new files and the support directory accessor.
- Suggested commit: `Characterize view resolution and legacy validation failure`

## MOD-067 — Enforce documented Mongo collection-name restrictions

- Files: db/driver/mongo.php and expanded Db/Mongo/DriverTest.php.
- Mechanical fix: shortname/explicit setters now throw the existing illegal
  exception, as their docblock promises; previously they called a nonexistent
  function instead. Names remain derived from their provider interfaces.
- Added an explicit invalid database-factory boundary test and ping-offset cache
  timestamp assertion. No connection retry/lazy-connect policy changed.
- Verified: all 59 Mongo tests / 138 assertions pass against the pinned runtime.
- Undo: restore the two return/function expressions and prior characterization
  expectations; remove the two added cases/fixtures.
- Suggested commit: `Throw documented Mongo collection-name restriction errors`

## MOD-068 — Exercise certificate-file writes and real shutdown cleanup

- Added Db/Mongo/CaFileTest.php; moved the existing DebugTest subprocess helper
  into tests/Support/PhpSubprocess.php for reuse. Coverage collection runs after
  native shutdown callbacks by appending its collector during shutdown. It merges
  only actual Xdebug data, including the certificate cleanup callback.
- Tests include real legacy certificate helper files explicitly because their
  declared db/mongo namespaces differ from db/driver/mongo paths. Tests do not
  claim ordinary autoload works, certificate validation, TLS, or base64 decoding.
- Covers literal bytes, caller paths, cached/replaced paths, empty values, native
  write failure and temporary-file removal at child process shutdown.
- Verified selected Debug/certificate tests: 13 tests / 67 assertions pass.
- Undo: remove certificate tests and move the subprocess helper back to DebugTest.
- Suggested commit: `Test certificate-file lifecycle and collect shutdown coverage`

### MOD-068 full verification checkpoint

- Full suite: 1,008 tests / 4,487 assertions pass. Lines 85.99% (7,243/8,423),
  methods 81.26% (863/1,062), classes 71.90% (238/331).

## MOD-069 — Characterize legacy mapping, output delegation and listener APIs

- Added Core/LegacyMappingTest.php, Core/LegacyListenerTest.php,
  Tmpl/DesignOutputTest.php and a legacy-base case in Core/ExceptionTest.php.
- Tests cover declared core-data fields/unknown-field rejection, list defaults,
  non-object/object-map behavior, non-flat controller exports, optional template
  data delegation, explicit/derived exception codes and listener construction.
- Learned: from_object omits its required value argument; stub reads an unrelated
  undeclared listeners property rather than its registered listener collection.
  The latter notice is explicitly asserted before checking a public dynamic
  property. No fake framework interfaces or SDK classes were introduced.
- Verified: selected mapping/listener suites 8 tests / 12 assertions, and selected
  exception/output/mapping suites 15 tests / 30 assertions pass.
- Undo: remove new test files/cases.
- Suggested commit: `Characterize legacy mapping and listener convenience APIs`

## MOD-070 — Repair user-info reflection and cover extra fields

- Files: cloud/twit/user/info.php and new Cloud/TwitterUserInfoTest.php.
- Mechanical fix: instantiate ReflectionProperty with new in both constructor
  and extra-field discovery; the old expressions called a nonexistent function.
- Tests verify known fields map and remain excluded from extras, while fields
  added afterward return either their names or their original values, including
  false and object identity. No Twitter client, API or network is involved.
- Verified: 2 tests / 6 assertions pass on PHP 7.2.34.
- Undo: remove both new keywords and the new test file.
- Suggested commit: `Construct reflection properties when inspecting user-info fields`

## MOD-071 — Include supplied values in validation failure diagnostics

- Files: core/util/validate/exception/fail.php and expanded legacy error tests.
- Mechanical fix: use param[value] instead of undefined local value when forming
  the existing scalar-value message. Empty/nonscalar behavior and rule formatting
  are retained. This does not change validation pass/fail or fallback policies.
- Social asset wrappers now have direct username-base/ignored-resource tests.
- Verified selected asset/error suites: 69 tests / 186 assertions pass.
- Undo: restore undefined-local expression/remove the added cases and fixtures.
- Suggested commit: `Use supplied scalar value in validation failure diagnostics`

### MOD-071 full verification checkpoint

- Full suite: 1,036 tests / 4,545 assertions pass. Lines 87.01% (7,312/8,404),
  methods 83.99% (892/1,062), classes 75.23% (249/331).

## MOD-072 — Restore optional missing-class details in dependency errors

- Files: lib/exception/missing_dependency.php and expanded Lib/LibTest.php.
- Mechanical fix: test the supplied missing_class argument instead of an
  undefined class local when deciding whether to include missing-class details.
- Tests cover string/list/no details and preserve original accessor values.
  Additional error cases cover route coordinates, unspecified ORM operations
  and duplicate-key non-scalar value/index metadata through a legal subclass.
- Verified selected library/error suites: 64 tests / 136 assertions pass.
- Undo: restore the old empty check and remove added cases.
- Suggested commit: `Include supplied missing-class details in dependency errors`

## MOD-073 — Preserve documented API failure metadata

- Files: core/controller/api/exception/bad_response.php and legacy error tests.
- Fix: declare and initialize the class/method fields already exposed by the
  documented accessors. Both getters previously returned null.
- Regression test failed before the fix (LocalApi versus null) and passes after
  it. No API dispatch, callback or transport policy changed.
- Verified: legacy error suite 52 tests / 111 assertions passes at this checkpoint.
- Undo: remove the two fields/assignments and the new case.
- Suggested commit: `Retain API class and method in bad-response exceptions`

## MOD-074 — Exercise native PCRE errors and restore the hostname definer

- Files: core/util/validate/hostname.php and expanded Core/Util/ValidationTest.php.
- Fixes: implement the existing regexp/definer interface (regex/definer does not
  exist) and add PCRE delimiters in get_regexp. The public raw hostname_regex
  constant and its accepted hostname grammar are unchanged.
- Tests use actual PHP 7.2 PCRE compile, backtrack, recursion and UTF-8 offset
  errors; all modified INI values are restored. Diagnostic classes are exercised
  without changing the pending execution-error return/throw policy.
- Hostname tests use the real matcher with the supplied definition, not DNS or
  a claim that the separate constructor/value API is repaired. Tests cover the
  existing grammar's label, hyphen, underscore, port and empty-input boundaries.
- Verified: validation suite 25 tests / 63 assertions passes.
- Undo: restore original interface/getter and remove added cases.
- Suggested commit: `Restore hostname regex definition and test native PCRE diagnostics`

## MOD-075 — Cover parser rejection through an extension marker

- Expanded Vendor/MarkdownTest.php with additional malformed/unclosed syntax.
  A legal parser subclass registers an extra marker against the existing Quote
  handler; ordinary public text() input verifies it rejects a nonmatching marker
  and falls back to a paragraph. No private state or fake parser classes.
- Standalone Markdown tests pass (47 tests / 132 assertions before the final
  three cases); selected Markdown/error suites pass (100 tests / 241 assertions).
- The remaining Extra version guard may be reachable when the standalone vendor
  file is paired with an older real parser; it is not labeled dead simply because
  the bundled parser has a fixed compatible version.
- Undo: remove added corpus/extension cases.
- Suggested commit: `Cover malformed Markdown and extension-marker fallback`

## MOD-076 — Complete data dump, event facade and Mongo failure-path tests

- Data mapper: replace the always-truthy assignment condition in dump's fallback
  with else, retaining the original condition in a CONSERVATIVELY DISABLED comment.
  Every non-var type still receives the same human-readable dump; no new dump
  type or HTML behavior was introduced. Added fallback equality/base field-list
  tests in Data/MapperTest.php.
- Added Core/EventFacadeTest.php for stored data, handlers, listeners, resolver
  loading and callable trigger shortcut, restoring both shared event factories.
- Expanded Mongo CRUD tests with a real test-owned collection validator; a native
  error 121 (document validation failure) confirms non-duplicate insert failures
  are rethrown. No synthetic Mongo exceptions or shared collection changes.
- Verified selected suites: 69 tests / 211 assertions pass.
- Undo: restore the assignment condition/remove the added tests.
- Suggested commit: `Cover event facade and native Mongo failures; retain invariant dump fallback`

## MOD-077 — Retain unreachable bundled-parser and native-reflection fallbacks

- ParsedownExtra: original version guard retained in a CONSERVATIVELY DISABLED
  comment. Its framework loader unconditionally requires bundled Parsedown 1.5.0;
  lexical parent::version is fixed, so the less-than-1.5.0 branch cannot run.
  Revisit this proof if changing the bundled parser or its loading contract.
- Three API response constructors (bad_request/not_found/unauthorized): native
  backtrace class names identify already loaded classes; ReflectionClass and
  getShortName cannot throw Exception for those names. The original try/catch
  fallbacks are retained in comments, and the live reflection stays unchanged.
- No fake parent class, backtrace, reflection exception or hit count was used.
- Verification: selected Markdown/conversion suites 54 tests / 146 assertions;
  API response suite 14 tests / 77 assertions pass.
- Side investigation: an actual Parsedown 1.4.0 source checkout at commit
  0d288083922576bcd55389bcf1cd8ac1db7adaa7 exists only under the external diagnostic
  fixtures directory. It lacks the version constant, so it cannot exercise the
  intended guard either. No test or repository dependency requires this checkout.
- Undo: restore commented guards. Suggested commit:
  `Retain provably unreachable bundled-parser and native-reflection fallbacks`

## MOD-078 — Cover PDO file configuration and remove an impossible argument branch

- Files: db/driver/pdo.php, expanded PdoTest/ConnectionTest.php and new
  Db/Pdo/FileConfigTest.php.
- Cleanup: load_statement's array type forbids its rules-object branch before
  method entry. Original branch retained in a CONSERVATIVELY DISABLED comment;
  the array mapping remains live. No parameter type or fetch policy changed.
- Native SQLite tests cover configured SQL root, array second-argument driver
  options, prepare_map driver-option propagation, bare record/result flags and
  rejection of incomplete multiple-argument rules.
- Verified: all 49 PDO tests / 112 assertions pass. Statement-builder fetch
  semantics remain pending; these tests do not claim that path is repaired.
- Undo: restore the unreachable branch/remove added cases and file-config test.
- Suggested commit: `Cover PDO file configuration and retain impossible rules-object branch`

## MOD-079 — Cover quote-bearing JavaScript regular expressions

- Expanded Vendor/JsMinifierTest.php with valid regex literals containing single
  and double quote characters. The actual minifier preserves both regex bodies.
- Verified: 22 tests / 28 assertions pass; no minifier source change.
- Undo: remove the two corpus cases.
- Suggested commit: `Cover quote-bearing JavaScript regular expressions`

### MOD-079 full verification checkpoint

- Full suite: 1,066 tests / 4,613 assertions pass. Lines 87.57% (7,350/8,393),
  methods 86.63% (920/1,062), classes 80.36% (266/331).

## MOD-080 — Restore legacy header exception autoload and retain field invariant

- Added api/header/exception.php and its missing_request_field forwarding file.
  They load existing implementations under their unchanged legacy namespaces;
  original files remain available for direct includes. No class was renamed.
- Empty response-header fields now throw the actual declared exception instead
  of class-not-found Error. ResponseTest checks its type and message.
- Header rendering's unreachable empty-field fallback is retained in a disabled
  comment: construction rejects empty fields and the private field has no other
  writer. Nonempty/zero-like value formatting remains as before.
- Added generic API-exception fallback tests. Unknown errors from a class caller
  still hit the missing ReflectionClass::getClassName method; that failure is
  explicitly characterized rather than choosing a new class-name format.
- Verified response/error selection: 77 tests / 218 assertions pass at MOD-083.
- Undo: remove forwarding files/restore header guard and earlier test expectation.
- Suggested commit: `Autoload legacy header exceptions and retain constructor invariant`

## MOD-081 — Call the certificate-file adapter's declared hook

- Files: db/driver/mongo/ssl/cafile/file.php and expanded CaFileTest.php.
- Mechanical fix: _get_cafile_path replaces the nonexistent __get_cafile_path.
  Native temporary-file tests assert original-path reuse and empty-file behavior.
- Helpers still declare old namespaces; tests explicitly include their real
  files and do not claim the separate Mongo SSL autoload layout is repaired.
- Verified selected API/certificate suites: 18 tests / 94 assertions pass.
- Undo: restore the double-underscore calls/remove the new case/fixture.
- Suggested commit: `Use declared certificate path hook in the file adapter`

## MOD-082 — Characterize factory constraints and legacy ORM file declarations

- Added Core/Util/ValidationFactoryTest.php, Orm/LegacyFieldErrorTest.php and
  Orm/ReportTest.php. Validator pairs preserve rule identity/data through a legal
  subclass overriding the class-constraint hook. The default factory constraint
  still rejects those pairs; its intended default remains a maintainer question.
- Learned: flat/data supports positional constructor mapping, so factory/data
  correctly receives the validator and data arguments; this is verified rather
  than assumed broken. No factory production behavior changed.
- invalid_field.php actually declares invalid_param and conflicts with the other
  same-named class. A fresh PHP runtime explicitly includes the real legacy file,
  checks its defining filename and metadata. No class aliases or fake classes
  are used, and no claim is made that both definitions can coexist.
- Reports accept database class names without instantiating/querying them and
  reject non-database classes. Metadata loadability is repaired in MOD-086.
- Verified initial selected suites: 5 tests / 14 assertions pass.
- Undo: remove the new test files.
- Suggested commit: `Characterize validator factory and legacy ORM declaration boundaries`

## MOD-083 — Restore reason/type metadata and cover remaining diagnostics

- Session bad_key and bad_config now store the original reason already exposed
  by their accessors; displayed exception messages are unchanged.
- Scalar bad_value initializes its private type from the supplied argument
  instead of reading undefined type. The existing public message stays unchanged.
- Added reason/TTL/field, cURL system-data, actual libxml XPath context, legacy
  status-code and SSH badParam declaration tests. Native libxml state is restored.
- Existing core/controller/api/not_found still reports code 500 with Not Found;
  tests label that inconsistency and do not silently change routing policy.
- Verified selected debug/error suites: 74 tests / 198 assertions at MOD-084.
- Undo: remove new assignments/type initialization and added test cases.
- Suggested commit: `Retain session reason metadata and initialize scalar error type`

## MOD-084 — Cover legacy hookable and debug collection helper behavior

- Expanded Core/LegacyListenerTest.php and DebugTest.php with legal subclasses
  exposing their protected helper contracts. Debug display-type checks and no-op
  filter/success hooks are tested without claiming collection integration.
- Hookable constructors/reset helpers currently reference undefined event_data;
  the two notices and lost data are explicitly asserted. No hook delivery was
  invented or other errors suppressed; the previous handler receives other errors.
- Moved parser extension creation into the test runtime and added a fresh-PHP
  bundled-loader check. This measures real loader execution instead of loading
  parser classes during PHPUnit discovery before coverage starts.
- Verified: debug/error selection 74 tests / 198 assertions; Markdown 51 tests /
  139 assertions pass. JS quote-return regex case also passes; its semicolon
  omission before a closing brace is retained as current minifier output.
- Redundant minifier buffer cleanup is retained in a disabled comment: its
  initial native getChar consumes c; remaining helpers never repopulate it, and
  public minify constructs the concrete tokenizer. Revisit if that contract changes.
- Undo: remove added cases/fixtures and restore the buffer cleanup.
- Suggested commit: `Cover legacy helper boundaries and fresh parser loading`

## MOD-085 — Use the required AWS SDK v3 multipart API

- Files: cloud/aws/s3.php and new Cloud/S3MultipartTest.php.
- Fix: replace removed v2 UploadBuilder/exception names with real v3
  MultipartUploader and MultipartUploadException, matching Composer's ^3.23
  requirement. Keep the existing 10 GiB threshold, 25 MiB parts and concurrency 3.
- Abort an initialized upload through the client using SDK upload-state identity;
  initiation failures without an UploadId are wrapped without an invalid abort.
  Successful uploads still return immediately. The unreachable trailing waiter
  remains in a disabled comment; large-file MIME behavior is unchanged.
- The regression failed first with missing UploadBuilder; after the fix, actual
  SDK orchestration sends Create/Part/Complete or Abort commands to Aws/MockHandler.
  Only framework file-size routing metadata is controlled; the SDK reads a small
  real file. No remote S3 request, credentials, huge file or fake SDK class.
- Verified: 3 tests / 13 assertions pass with real SDK 3.337.3 on PHP 7.2.34.
- Undo: restore v2 imports/builder/abort code and remove the new test file.
- Suggested commit: `Use AWS SDK v3 multipart uploader and test upload cleanup`

### MOD-085 full verification checkpoint

- Full suite: 1,092 tests / 4,692 assertions pass. Lines 88.78% (7,435/8,375),
  methods 89.83% (954/1,062), classes 87.92% (291/331).

## MOD-086 — Restore existing event-loader and report metadata contracts

- Event listener factory now extends the existing core/event/factory/loader;
  the prior core/event/loader path does not exist. Constructor behavior is kept:
  it stores the supplied factory/data; registration remains explicit.
- Report meta stays an interface exposing get_meta; remove its illegal extension
  of concrete core/data. Report tests exercise actual metadata-source selection.
- Added missing core/data/create_properties marker, whose role is explicitly
  defined by core/data's existing constructor flag. A marked subclass creates
  dynamic fields; unmarked data remains strict. No default mapping flags changed.
- Verified selected listener/mapping/report suites: 14 tests / 24 assertions pass.
- Undo: restore invalid parent declarations/remove marker and new test cases.
- Suggested commit: `Restore event loader, report metadata and dynamic-field marker contracts`

## MOD-087 — Unwrap validation collection members before inspecting fields

- Files: core/controller/validate.php and ValidateControllerTest.php.
- Mechanical fix: use member::get_data instead of accessing private member data.
  Ordinary collections now reach the existing field-inspection loop.
- The loop's body remains unfinished; no validator invocation, aggregation or
  error policy was invented. Tests assert member unwrapping and unchanged input.
- Verified: 3 tests / 4 assertions pass.
- Undo: restore private-field access and original failure characterization.
- Suggested commit: `Unwrap collection members in legacy validation controller`


## MOD-088 — Preserve every shutdown callback at equal priority

- Existing register() documentation specifies newest-first ties. Shift occupied slots before insertion and update the exclusive upper bound, so no callback is overwritten or skipped. Check the priority ceiling before mutating queues.
- Preserve default/named queues and repeated explicit invocation. Retain the unused private _clear body in a conservative comment; its only call was already commented out.
- Verification: ShutdownTest, 6 tests / 7 assertions. Newest-first ordering, distinct queues, invalid weights and priority exhaustion are exercised.
- Undo: restore the prior collision algorithm and unused method. Suggested commit: `Preserve equal-priority shutdown callbacks`.

## MOD-089 — Restore loadable payment transaction declarations

- Customer transaction extends the actual typed loader; static dispatch reaches subclass type definitions. Abstract payment type accessor is static, matching its interface and implementations. Preserve legacy payment-profile names with forwarding files.
- Failed transaction validation now reports its actual validation result instead of an undefined variable. SDK field filtering still drops null-initialized fields; this is characterized, not silently changed.
- Verification: AuthorizeTransactionTest, 10 tests / 33 assertions, using the real pinned SDK without API calls.
- Undo: restore declarations and remove forwarding files/tests. Suggested commit: `Restore legacy payment transaction type contracts`.

## MOD-090 — Test native CIM construction and repair payment identifiers

- A legal application subclass supplies the historical request-preparation hook; test the real SDK client construction/cache and libxml flag restoration. This does not add that missing hook to the default base class.
- Pass validated customer/payment-profile IDs into create/update requests instead of undefined locals. Normalize generic transaction input into the existing transaction type selector.
- Response factory compares the actual failure-code table and assigns its existing status field for approved/held/declined/error results. Unknown codes still return null; no new status field or gateway policy was introduced.
- Scoped test-only SDK deprecation handling delegates unrelated errors to the previous handler. Tests use actual SDK response objects and SimpleXML, including absent identifiers without an index-zero warning.
- Verification: selected customer/data/transaction suites, 94 tests / 317 assertions. No external payment requests.
- Undo: restore ID/type/status selection and remove added tests/support. Suggested commit: `Repair customer payment IDs and transaction response selection`.

## MOD-091 — Exercise SSH operations with an explicit transport boundary

- Correct reversed authentication type checks, nonexistent exception namespaces, optional passphrase access, process-resource checks and temporary-key cleanup guards.
- Internal command execution supports the existing subclass exec_output hook and otherwise uses exec. Validate both supplied signals/waits from their arguments; retain the existing shared clock and process command forms.
- Preserve legacy badParam while supplying the bad_param name used by callers. Retain the unused private disconnect callback in a conservative comment; do not invent connection lifecycle wiring.
- SshBoundary is an explicit namespaced unit double for transport/availability/failure paths; native local OpenSSL, subprocess and file operations remain real. No native SSH2 extension/server/network authentication is claimed.
- Verification: SshTest, 50 tests / 85 assertions. SFTP transfer-loop timing remains an owner question; no safe_feof behavior was invented. OpenSSL output generation is tested, not encryption-at-rest security.
- Undo: restore guards, argument selection and exception paths; remove alias/test boundary. Suggested commit: `Repair SSH authentication guards and process argument validation`.

## MOD-092 — Restore both legacy session-driver names and native persistence

- Preserve the declared flat\data\session\driver\session name and alias the name implied by its original ORM path. Forwarding files supply the missing historical parent and class paths.
- Pass handler_param into handler construction; use the supported config::get API. Use root-relative data/orm keys to avoid the separately characterized flat-prefix truncation bug; a non-null false fallback also works before config initialization.
- Make the native ORM-key accessor static to match its callers, construct the declared bad_param exception correctly, and supply its missing class. Retain unreachable final handler revalidation in a conservative comment because concrete fallback construction guarantees a handler.
- Verification: SessionDriverTest, 15 tests / 33 assertions, including real file-session persistence across separate PHP processes and both class names. A small explicit platform-status double covers disabled-session availability.
- Native destroy still attempts regeneration after ending the session; tests record the warning rather than choosing a new lifecycle contract. Root flat\data\orm driver selection remains unresolved.
- Undo: restore legacy calls/declarations and remove aliases/exception/test support. Suggested commit: `Restore legacy session driver loading and native persistence`.

## MOD-092 verification checkpoint

- Latest completed full suite before native session additions: 1,180 tests / 4,926 assertions pass.
- Actual Xdebug coverage: lines 93.35% (7,670/8,216), methods 93.40% (991/1,061), classes 93.05% (308/331).
- These are line/method/class metrics, not branch coverage. PHP remains 7.2.34; flat-webapp functional tests have not yet run.


## MOD-093 — Restore subscription helper loading and native timezone conversion

- Qualify the native DateTimeZone used by subscription schedule conversion. UTC midnight now actually converts to the preceding Denver date.
- Correct subscription not_found's nonexistent relative parent. Preserve cancelled and the canceled filepath spelling with an alias and forwarding file. Cover cache metadata and non-scalar duplicate-key descriptions.
- Verification: selected AuthorizeDataTest/LegacyErrorMessagesTest, 94 tests / 224 assertions pass.
- Undo: restore parent/timezone references and remove alias/forwarder/tests. Suggested commit: `Restore subscription helper names and native timezone conversion`.

## MOD-094 — Test asset output failures and correct directory flag matching

- SYSTEM_PATH_FILE and SYSTEM_PATH_DIR both include SYSTEM_PATH_EXISTS. Require the complete flag mask, so a directory request no longer enters the file-only branch merely because both contain bit 256.
- Retain the unreachable empty-base return in a conservative comment: its preceding condition tests the nonzero constant itself and always throws. No empty-base behavior changed.
- Cover real file/directory/permission paths, lawful overridden system paths, and read failure through an explicit namespaced file_get_contents boundary that delegates normally outside its one selected path.
- Exercise CSS content, fallback tags, nested dependency groups and special-output argument paths. Accidental special-output self-cycles are not executed or fixed without intent.
- Verification: AssetTest/CascadeTest, 31 tests / 118 assertions pass.
- Undo: restore mask checks and return, remove additional cases/boundary. Suggested commit: `Correct asset directory masks and cover output failures`.

## MOD-095 — Repair REST callback metadata and test actual PHP shutdown

- Initialize optional error metadata before dispatch, and read message/data from the actual returned error response instead of undefined data. Non-error failure statuses supply null optional metadata; ordinary exception callbacks retain the actual exception/message.
- Conservatively retain an unreachable second catch rethrow in a comment: the earlier branch either invokes the callback and sets the flag or already throws.
- Verify callback metadata and a registered callback executing at actual child PHP shutdown, with genuine child Xdebug data merged.
- Verification: selected ControllerTest/ShutdownTest filter, 67 tests / 163 assertions pass.
- Undo: restore metadata expressions/catch tail, remove added tests. Suggested commit: `Repair REST error callback metadata and test native shutdown`.

## MOD-096 — Make the subscription wrapper loadable and exercise existing operations

- Declare the credential-dependent subscription base abstract, matching its existing abstract parent and unimplemented transaction interface methods. No credential defaults or transaction implementations are invented.
- Resolve qualified authorize references through the actual framework namespace. Require the documented subscriptionId before existing-subscription operations.
- Repair payment object normalization, bank-account member selection and the malformed-payment exception name. Correct params/amount, schedule validation parentheses and optional object mapping variables.
- Status/updatability call the existing get_status method. Pass the constructed SDK list object into getSubscriptionList and extract the total only after checking index zero.
- Preserve the current create return value (null), SDK isset filtering, unfinished transaction query, application request-preparation hook and pagination offset behavior. Search pagination is still not complete; do not claim all pages are retrieved.
- Verification: AuthorizeSubscriptionTest, 36 tests / 74 assertions pass with real SDK DTOs and a local client protocol double. Native SDK client construction/cache is exercised without API requests.
- Undo: restore declarations/expressions and remove new suite. Suggested commit: `Restore loadable subscription base and documented operation inputs`.

## MOD-097 — Repair validator exception naming and retain native PCRE behavior

- Match-interface failure now extends the existing validation factory exception and loads from its canonical path; retain the historical declared name via alias/forwarding file.
- Test explicit regex constructor parameters through a legal validation override without pretending native preg_match accepts arrays.
- Conservatively retain the unreachable engine-error throw in a comment. Native preg_match returns 1, 0 or false; the original loose comparison returns false for both failure results. The native failure policy remains unchanged, pending owner preference.
- Undo: restore the invalid parent/namespace and throw, remove forwarding file/test cases. Suggested commit: `Restore validator exception paths and document unreachable PCRE throw`.


## MOD-098 — Cover public registration and actual subscription search bounds

- Asset output tests cover explicit resource/asset/callback inputs and currently unimplemented type/tag fallthrough. Retain the private missing-position fallback in a conservative comment: both private registration writers store a validated integer position atomically with the asset, and no public API removes it.
- Cover subscription schedule objects, list rejection after status lookup, full-page total exhaustion and the actual 100,000-tick circuit breaker. The last test uses 1,000 genuine SimpleXML details per repeated page; no private counter, synthetic line hit or fake native return is substituted. It takes about 40 seconds on this runner.
- Cover empty non-scalar duplicate indexes with their existing array-to-string warning, and fresh-process legacy validator exception autoload.
- Verification: initial combined filter 161 tests / 400 assertions; circuit-breaker case 1 test / 2 assertions. No paging advancement or special-HTML cycle policy chosen.
- Undo: restore private fallback and remove new boundary tests. Suggested commit: `Cover asset registration and subscription search termination`.

## MOD-099 — Retain proofs for unreachable legacy option/getter paths

- Base validator's NULL defaults cannot be overwritten through its isset-based merge. Preserve its actual pass-through-on-success/null-on-failure behavior while retaining the unreachable fallback/callback/exception dispatch as a conservative comment. The declared options remain a known defect pending intent; they were not implemented or silently advertised as working.
- API request-method return is unreachable because its private backing field is never assigned. Preserve its existing missing-method exception while retaining the return with proof in a comment.
- Mongo's local option whitelist never populates connect_attempts, so retain its unreachable override in a conservative comment. Do not change retry count, lazy connection behavior or SDK compatibility.
- Verification: selected ValidationTest/ControllerTest/DriverTest filter, 128 tests / 305 assertions pass.
- Undo: restore commented option/getter branches. Suggested commit: `Document and prune proven unreachable legacy option paths`.

## MOD-100 — Restore the legacy ORM's declared default session fallback

- Replace nonexistent default_handler with the already-declared fallback_driver and store the constructed driver on the instance. Native default storage now reaches the session implementation rather than failing on a missing constant.
- Retain the unused private default-driver placeholder and undefined-param option branches in conservative comments with local-variable/call-site proofs. Do not invent custom driver-array selection or change the existing void get/set results.
- Verification: LegacyOrmTest, 4 tests / 12 assertions. Real native session storage is observed from an isolated PHP child; legal subclass dispatch/cache and currently invalid nonempty constructor arrays are separately characterized.
- Undo: restore default constant/local assignment and dormant branches; remove suite. Suggested commit: `Restore the legacy ORM default session driver`.

## MOD-101 — Characterize the holiday wrapper against real Yasumi

- Add Yasumi 1.8.0 only to the isolated test dependency manifest/lock. It supports PHP >=5.6; the root project manifest/lock/vendor remain unchanged. Composer reports one addition and no updates/removals.
- Verify real USA provider construction, native schedule comparisons, invalid/missing date fallback and the existing timestamp passed to providers expecting DateTimeInterface.
- Record current defects without choosing the pending date contract: valid strings/explicit years leave backing fields unset; cache hits do not attach the provider to the next instance; set_date can retain the old timestamp; locale is ignored.
- Verification: YasumiDateQueryTest, 9 tests / 36 assertions pass. No holiday-wrapper production behavior was changed.
- Undo: remove suite and isolated Yasumi package. Suggested commit: `Characterize legacy holiday queries against real Yasumi`.

## MOD-102 — Exercise PDO callbacks with buffered native SQLite rows

- A test-only PDOStatement subclass buffers actual native SQLite result rows to provide an accurate SELECT row count and preserve later fetches. It does not claim native SQLite rowCount supports SELECT counts or fabricate data.
- Verify the existing fetch_callback option consumes real query results. Preserve the existing undefined-data-class failure for executed rows without a callback, and validate explicit join-clause separators through a legal rules subclass.
- Retain four unreachable local fetch closures in conservative comments: every specified fetch option throws before those closures can escape or run. Fetch option semantics remain unchanged and pending intent.
- Verification: StatementTest, 30 tests / 45 assertions pass.
- Undo: restore local closures and remove buffered adapter/cases. Suggested commit: `Cover PDO callback rows and retain unreachable fetch closures`.

## MOD-103 — Test configured alert fallback and preserve unfinished boundaries

- Verify default alert broker configuration against the real isolated RabbitMQ service, cleaning up native channels/connections and restoring caches. Characterize the absent relative CRUD class separately.
- Characterize trigger's listener invocation, undefined metadata/driver warnings and failure before publishing; no success callback or fabricated publisher behavior is asserted. Retain its unreachable second publish in a conservative comment: the unassigned local driver always fails on the first publish.
- SFTP test opens transport-double/native-local streams and records the actual missing safe_feof failure. It does not supply an invented EOF/timeout helper.
- Legacy DB controller tests exercise constructor connection forwarding and its literal-self warning/NULL read behavior. Retain the unreachable read body with PHP-7.2 constant-type proof, pending read versus op_read intent.
- Undo: restore unreachable bodies and remove added tests. Suggested commit: `Characterize remaining alert, SFTP and legacy read boundaries`.


### MOD-103 cleanup and verification

- Test-owned SSH disconnect closures retain their instance. Release those callbacks and collect cycles while the explicit transport boundary is still active, before closing resources/restoring native calls; this prevents a delayed destructor from attempting unavailable native SSH2 functions after the test.
- Affected SSH/alert/legacy DB/PDO filter: 96 tests / 206 assertions pass with clean process exit.
- PHP 7.2 syntax verification: all 631 framework PHP source files pass; whitespace diff check passes with legacy CRLF handling.
- Current blocking questions are narrowed in pending-behavior-questions.md; unrelated unanswered policy questions remain characterized rather than guessed.


### MOD-103 full verification checkpoint

- Full suite: 1,277 tests / 5,164 assertions pass with clean exit.
- Actual Xdebug coverage: lines 99.32% (7,994/8,049), methods 99.15% (1,052/1,061), classes 98.19% (325/331).
- Runtime 6.76 minutes, memory 56 MB. The actual 100,000-tick subscription guard test takes about 40 seconds without coverage and several minutes with Xdebug; no loop counter was patched or native coverage hits fabricated.
- Remaining report has 55 uncovered lines: SSH transfer loop 19, unassigned API getter implicit return 1, Mongo compatibility paths 17, scalar duplicate-key message 1, alert fallback/fallthrough 2 and native pthread listener 15.

## MOD-104 — Close the last scalar-message and getter fallthrough gaps

- Add a nonempty scalar duplicate-key value case and assert the stored value and exact description.
- Express the API getter's guaranteed missing-method exception directly. Its private field has no assignments, so the original conditional return and implicit fallthrough cannot execute; retain their proof/original statements in a conservative comment. Incoming-versus-dispatched verb behavior remains unresolved and no method is newly returned.
- Selected ControllerTest/LegacyErrorMessagesTest filter: 128 tests / 304 assertions pass. The final full run is collected separately below.
- Undo: restore the conditional guard/commented return and remove scalar case. Suggested commit: `Cover scalar duplicate-key descriptions and remove impossible getter fallthrough`.


### MOD-104 final full verification

- All 1,278 tests / 5,166 assertions pass with clean exit on PHP 7.2.34.
- Actual Xdebug coverage: lines **99.34% (7,995/8,048)**, methods **99.34% (1,054/1,061)**, classes **98.79% (327/331)**.
- Runtime 9.59 minutes / 56 MB for this run, including the real subscription circuit-breaker loop. Native coverage text, Clover XML and raw PHP coverage are retained in the external php72-tools directory; no coverage exclusions or synthetic hits were added.
- Exactly **53 uncovered lines** remain: SSH SFTP transfer loop 19; Mongo connection compatibility branches 17; alert default CRUD/fallthrough 2; pthread listener 15. These correspond to the three current owner decisions in pending-behavior-questions.md.
- All 631 PHP source files pass PHP-7.2 syntax checks; whitespace diff check passes. PHP and root project dependency versions have not been upgraded. No commits, merges, pushes or environment publication performed; flat-webapp functional verification remains pending.


## MOD-105 — Implement owner-approved SFTP idle timeouts

- Owner decision: restart the timeout after every successful read. Replace missing safe_feof with native feof and initialize/reset the microtime idle clock accordingly.
- Preserve callback arguments/frequency and normal null return, including partial bytes already written when the loop times out. Treat string "0" as successful data rather than PHP-empty EOF. Progress uses actual byte lengths and avoids division by zero for a changed/zero reported size.
- Close remote/local streams in finally blocks, including local-open and callback failures. No new SSH2 per-stream blocking/timeout configuration or write-failure policy was invented; native read blocking remains governed by the transport.
- Tests use the existing explicit SSH transport double, native local streams and a controlled clock: reads spread over 12 seconds succeed with a 5-second idle timeout, while an idle gap stops the next read. Empty/failed/zero reads, progress callbacks and cleanup are exercised.
- Undo: restore the prior SFTP loop and failure characterization. Suggested commit: `Implement SFTP idle timeouts and guaranteed stream cleanup`.

## MOD-106 — Actively verify and retry eager Mongo connections

- Owner decision: connect=true actively verifies connectivity and retries failures. New eager clients perform an actual MongoDB ping; explicit connect=false remains lazy. Successful verification alone records a timestamp.
- Preserve the existing 60-second verification TTL. Cached eager clients with stale/missing metadata are actually pinged; success keeps the same client, while failure evicts client/timestamp before retries. Fresh verified clients are reused.
- Honor the previously documented connect_attempts option with a positive numeric count; otherwise retain the default five attempts. Exhaustion throws the existing connect_failure with the first failure context. Adapter error arrays with unsuccessful ok become MongoConnectionException so the retry path runs; native connection exceptions use the same path.
- Retain superseded getConnections inspection and constructor-only retries as line-commented history. No synthetic SDK classes, fabricated connection metadata, new root dependency versions or fake coverage hits were added.
- A test-only TCP proxy forwards actual bytes to the real local MongoDB service, then closes transport connections. Verify cached failure, eviction and retries with the actual SDK/client/socket. Separate tests verify lazy behavior, fresh/stale reuse and failure before any command is returned to the caller.
- Undo: restore the archived load_client implementation and earlier cache/lazy characterizations, remove transport fault fixture/tests. Suggested commit: `Verify eager Mongo connections and retry real ping failures`.

## MOD-107 — Retire unfinished alert scaffolding while preserving working APIs

- Owner decision: retire unfinished things; preserve tested subscriptions and synchronous listeners. The incomplete publisher retains its entry-point signature but immediately throws BadMethodCallException, without invoking callbacks or pretending to publish. Its original implementation is retained as line-commented history.
- The unfinished pthread listener is no longer declared; preserve the original file's scaffolding as comments for reference. No Thread stand-in or coverage exclusion is used. Supported alert::add_listener remains synchronous.
- Retire the impossible default CRUD lookup (absent relative class/helper/abstract construction). A missing or empty storage provider raises a clear LogicException. Existing provider-based storage, queue selection, subscription expiration, synchronous message decoding/expiry refresh and configured RabbitMQ fallback remain tested.
- Preserve unrelated tested no-op log methods; no new log/subscription routing was invented.
- Focused verification for MOD-105/106/107: 150 tests / 361 assertions pass; SSH, Mongo and alert components have 100% measured line coverage. The resumed environment's existing MongoDB/RabbitMQ containers were restarted before verification; no images or PHP versions changed.
- Undo: restore archived publisher/thread/default lookup and earlier failure characterizations. Suggested commit: `Retire unfinished alert publisher and pthread listener`.


### MOD-107 full source verification

- Full PHP 7.2.34 suite: 1,289 tests / 5,194 assertions pass.
- Actual Xdebug coverage: classes 100% (330/330), methods 100% (1,059/1,059), lines 100% (7,988/7,988). Runtime 8.15 minutes / 56 MB.
- All 631 PHP source files pass syntax checks. This checkpoint used the former external diagnostic harness; repository-owned reproduction is verified under MOD-108 below, rather than treating this result as evidence of harness portability.

## MOD-108 — Move the entire test harness into the repository

- Maintainer feedback, as requested for this work and its commit: my human was disappointed and irritated with me because I put the Docker test harness outside the repository. AGENTS.md now states that repository-owned Docker setup and reproducibility are mandatory for future agents.
- Replace the old incomplete Dockerfile/Compose scaffold with a complete build from pinned public PHP/native sources, checked-in isolated Composer manifests/locks, readable embedded bootstrap, and actual native runtime extension/version verification. Remove the obsolete entrypoint that ran composer update against application dependencies.
- tests.sh builds/reuses the image, starts only required services, waits for health, runs tests, preserves failure status and tears down containers/network/test volumes on normal exit, failure and interruption. Both -h and --help show usage. Default all; unit excludes service groups; services/mongo/rabbitmq select their marked cases. Optional coverage reports stay under ignored build/tests/<mode>.
- Mark genuine external-service cases using PHPUnit groups. Pure Mongo metadata tests use no-connect fixtures instead of incidental backend connections. PDO remains SQLite inside PHP; SFTP remains an explicit unit transport boundary. No MySQL/SFTP service is falsely claimed.
- Dependency archives use locked GitHub codeload commit URLs. The archive-fetch stage uses Composer's image/platform constraints; framework/tests execute only under verified PHP 7.2.34. The runtime actually builds sockets; its absence is ignored only in the archive-fetch stage. Optional CA input uses BuildKit secrets, never copied certificates or disabled TLS verification.
- Unit-only wrapper verification: 1,243 tests / 5,054 assertions pass with no external services; its container/network are removed afterward. An earlier failed unit run also verified cleanup/status propagation and identified one RabbitMQ case requiring service classification.
- Branch handling: create php-7.2, carrying the working tree from ba9f93d. Do not alter modernize-26/master refs or history; maintainer will perform their own housekeeping. Commit/push only php-7.2 when validation completes. See branch-investigation.md for read-only evidence of the existing 19-file commit above master.
- Undo: restore the previous Docker scaffold/remove wrapper, test locks/bootstrap/groups and guidance; restores the old nonportable limitations. Suggested commit: `Complete PHP 7.2 coverage with repository-owned Docker tests`.


### Maintainer commit preference

- Include all change/testing/decision documentation under docs/ in the current php-7.2 commit and push. Future work should use more frequent commits for coherent groups of related changes, with documentation in the same group. Record this expectation in AGENTS.md so subsequent agents do not accumulate another large uncommitted work tree.


## MOD-109 — Test the subscription guard with an overridable budget

- Owner decision: subscription subclasses may override arb_search_max_tics. Use static:: for the existing public constant while retaining the default 100,000.
- The regression fixture overrides the limit to 2, uses real 1,000-member XML pages and exercises the same guard. Assert the parent default remains 100,000. The prior 100-million-detail brute-force test was poor test design and is replaced; no coverage counters or native hit records are fabricated.
- The maintainer requested an immediate checkpoint commit/push of the current work, then continued work. Offline runner support follows as a separate logical change.
- Undo: restore self::/the expensive test if a fixed universal cap is desired; this contradicts the current explicit owner contract. Suggested commit: `Complete PHP 7.2 coverage with portable Docker tests`.


### MOD-109 repository-owned full verification and checkpoint

- `./tests.sh all --coverage`: all 1,289 tests / 5,195 assertions pass on PHP 7.2.34 in 18.11 seconds (56 MB), plus reports. Actual Xdebug: classes 330/330, methods 1,059/1,059, lines 7,988/7,988 — all 100%.
- Docker-created PHP/MongoDB/RabbitMQ containers, network and test volumes are removed afterward. Unit/services/mongo/rabbitmq modes separately pass. Both -h and --help print usage.
- The staged portability audit finds no agent-specific executable environment detection, private workspace paths, private service fallbacks, generated caches, report artifacts or private harness archives. All code/learning/change documents are included under docs/.
- Checkpoint commit/push is to php-7.2 only, per explicit owner request; modernize-26/master remain untouched. Offline mode is the next separate logical change.


## MOD-110 — Add cached-image offline Docker execution

- Add --offline for a previously built PHP test image and cached selected service images. Check exact local image refs, skip builds and enforce no pulls. Missing images fail clearly before startup. --build and --offline cannot be combined.
- Keep the test network internal-only: runtime containers reach local peers without an Internet route. The wrapper owns readiness by test mode; PHP does not unconditionally require database/broker containers. Optional build-CA input is unused offline.
- Offline full coverage: 1,289 tests / 5,195 assertions pass, 100% classes (330/330), methods (1,059/1,059), lines (7,988/7,988), PHP 7.2.34, 21.05 seconds / 56 MB plus reports. All stack resources are removed afterward.
- Restore the full running change log from the earlier staged Git object: the first checkpoint accidentally replaced it with Docker instructions. Preserve all MOD-001 through MOD-108 history, append MOD-109/110, and keep operational Docker instructions in their separate document. No code or test results were lost.
- Owner requested frequent logical commits; this offline change and documentation correction follow checkpoint 2aa6cdd separately.
- Undo: remove the offline flags/preflight and internal-only network to restore the prior online-build workflow. Suggested commit: `Support offline Docker tests and preserve the full change log`.


### MOD-110 verification

- `./tests.sh all --offline --coverage`: 1,289 tests / 5,195 assertions pass in 21.05 seconds, 100% line/method/class coverage. No builds or pulls occur; runtime networking is internal-only; stack teardown completes.
- `./tests.sh unit --offline`: 1,243 tests / 5,055 assertions pass in 5.5 seconds with no service containers, even with an unavailable build-only CA input.
- Missing PHP image is rejected with exit 1 before startup; the local image tag was restored after this check. --offline with --build is rejected with exit 2. Both help aliases and shell/Compose validation pass. No test-stack containers remain.
- Full change-log restoration is verified by its correct heading, 1,819-plus lines, MOD-108/109/110 entries, and a uniqueness check across all docs/ Markdown files.


## MOD-111 — Make the harness portable to root hosts and close two test gaps

- Independent review: the suite was rerun from a fresh container and probed with
  14 deliberate source mutations (one at a time, restored after each). 12 were
  detected. Two survived and are closed here; no source behavior changes.
- Harness: on a root host, `./tests.sh` failed 4 unreadable-path cases (AssetTest
  x2, AwsTest, PdoTest) because root bypasses permissions. A root host now runs
  PHP as 1000:1000 and makes the report directory writable for it.
- Harness: RabbitMQ `ping` reported healthy ~4 s before port 5672 accepted
  connections, so `./tests.sh rabbitmq` failed all 7 cases (3 of 3 runs) and full
  runs could fail one. The healthcheck now uses `check_port_connectivity`.
- Harness: with a CA file, newer Compose/Bake refused the out-of-context secret
  path. tests.sh sets BUILDX_BAKE_ENTITLEMENTS_FS=0 only on that CA path.
- Test gap: nonce expiry at exactly its TTL was untested (`>` to `>=` survived).
  SessionTest now pins the documented boundary: accepted at age == ttl, expired at
  ttl+1. The clock is not faked (SshBoundary already shadows flat\core\time);
  the acceptance case retries only if the second ticks mid-check.
- Test gap: routing never received request-path shapes; removing the empty
  segment filter survived. Route ControllerTest now checks leading, trailing and
  doubled slashes and a leading backslash resolve exactly like `ParentApp/Leaf`.
- Undo: revert the healthcheck, the tests.sh root/CA lines and the 7 new cases.

### MOD-111 verification

- As root with a CA file, `./tests.sh all --coverage`: 1,296 tests / 5,212
  assertions pass, 100% classes (330/330), methods (1,059/1,059), lines
  (7,988/7,988). `./tests.sh rabbitmq` passes 7/7 on repeated runs.
- Both formerly surviving mutations now fail the suite.

## MOD-112 — Run each shutdown callback at most once (KD-16)

- Owner decision (known-defects Q6): explicit invocation followed by PHP
  shutdown must not run callbacks twice.
- `invoke_shutdown_callbacks()` snapshots the queues, empties them, then runs
  the snapshot. A working `_clear()` replaces the never-called original, which
  assigned local variables and cleared nothing. The PHP hook stays registered,
  so callbacks registered during or after invocation run at PHP shutdown.
- Tests: repeated invocation runs each callback once across all queues; real
  child PHP processes show no repeat at shutdown after explicit invocation and
  that a callback registered mid-run still runs at shutdown.
- Undo: restore the snapshot-free loop and the commented helper.

## MOD-113 — Retire the core mapper's private-read options (KD-28)

- Owner decision (known-defects Q12): reading private/protected properties is
  not supported; the mapper is public-only (MOD-011).
- `get_property_value` with `get_private_property`, and `get_as_assoc` /
  `get_as_stdClass` with `$get_private_properties = true`, raised `Error` on the
  misspelled `setAcessible` whenever a non-public property was reached. They now
  throw `cannot_get_property` at that same point, naming the property. Objects
  with only public properties behave as before; `set_private_property` already
  worked and is unchanged.
- Tests assert the clear error for all three paths and that the flag is
  harmless for public-only objects.

## MOD-114 — Retire unfinished payment conveniences and IP lookup load (KD-45/46)

- Owner decision (known-defects Q16): retire never-implemented methods, as
  MOD-107 did for the alert publisher.
- Customer `void_transaction`, `refund_transaction`, `authonly`, `authcapture`,
  `captureonly`, `get_transaction`, subscription `get_transaction` and
  `ipinfodb::load` had empty bodies returning `null`. They keep their
  declarations (the ccgateway interface requires them) and throw
  `BadMethodCallException`; messages point to `create_transaction()` or to
  constructing the lookup class where those work.
- Tests assert each failure and that no gateway call or cache access occurs.
  The subscription `get_transaction` assertion moved out of the unrelated
  update/cancel test into its own test.

### MOD-112 to MOD-114 verification

- `./tests.sh all --coverage`: 1,305 tests / 5,231 assertions pass; 100%
  classes (330/330), methods (1,060/1,060), lines (7,994/7,994).
- `--group known-defect` drops from 130 to 124 cases.

## MOD-115 — Reorganize the phase documents (no code change)

- Owner direction: keep every document, but make it clear what is current,
  what is lasting reference and what is finished. The owner moved the docs
  under `docs/modernization/php-7.2/` (renaming `modernization-26.md` to this
  `CHANGELOG.md`) and added `docs/modernization/README.md`. The folder name
  typo (`moderization`) was then fixed, and the release plan's still-valid
  rules were folded into that README (`php-version-release-plan.md` removed).
- New layout: `README.md` (status, how to resume, contents), `CHANGELOG.md`,
  `active/` (current work), `notes/` (per-area testing reference) and
  `history/` (finished or replaced; each file now starts with a banner).
  `docker-testing.md` moved up to `docs/modernization/testing.md` because it
  applies to every phase. Root `AGENTS.md` now points to the phase README.
- Open items from `testing-follow-ups.md` were carried into
  `active/known-defects.md` as KD-57 to KD-68 and TS-01 to TS-05, with owner
  questions 19 to 23. Items marked "source checked" were confirmed in the
  current code; the others are marked "not yet verified".
- Earlier entries keep their original paths. Old to new:

| Old path (under `docs/`) | New path (under `docs/modernization/`) |
| --- | --- |
| `docker-testing.md` | `testing.md` |
| `modernization-26.md` | `php-7.2/CHANGELOG.md` |
| `known-defects.md` | `php-7.2/active/known-defects.md` |
| `consumer-impact-review.md` | `php-7.2/active/consumer-impact.md` |
| `test-inventory.md` | `php-7.2/active/test-inventory.md` |
| `collections-routing-cli-testing.md` | `php-7.2/notes/collections-routing-cli.md` |
| `data-and-xml-testing.md` | `php-7.2/notes/data-and-xml.md` |
| `database-assets-and-minifier-testing.md` | `php-7.2/notes/database-assets-minifier.md` |
| `debug-templates-and-text-testing.md` | `php-7.2/notes/debug-templates-text.md` |
| `mapper-unit-testing.md` | `php-7.2/notes/mapper.md` |
| `payment-and-alert-testing.md` | `php-7.2/notes/payment-and-alert.md` |
| `session-and-http-testing.md` | `php-7.2/notes/session-and-http.md` |
| `utility-and-event-testing.md` | `php-7.2/notes/utility-and-event.md` |
| `unit-coverage-baseline.md` | `php-7.2/history/coverage-baseline.md` |
| `pending-behavior-questions.md` | `php-7.2/history/pending-behavior-questions.md` |
| `testing-follow-ups.md` | `php-7.2/history/testing-follow-ups.md` |
| `branch-investigation.md` | `php-7.2/history/branch-investigation.md` |
| `handoff-modernize-26.md` | `php-7.2/history/handoff-modernize-26.md` |
| `legacy-test-environment.md` | `php-7.2/history/legacy-test-environment.md` |
| `php-version-release-plan.md` | folded into `README.md` ("Rules for every phase") |

- Verified: every relative Markdown link in the repository resolves.

## MOD-116 — Uppercase short document names and a repository-owned test inventory

- Owner direction: document file names are UPPERCASE, at most three words and
  preferably two, for all modernization phases; the test inventory lives in the
  phase folder as `TEST-INVENTORY.md` and is produced by the repository's own
  test tooling. Conventions are recorded in `docs/modernization/README.md`.
- New `./tests.sh inventory [KNOWN-DEFECTS.md]`: runs PHPUnit's
  `--list-tests-xml` in the test container (no service containers) and converts
  it with `tests/docker/inventory.php`, printing Markdown to stdout so it can be
  redirected anywhere. Classes, methods, groups and data-provider cases come from
  PHPUnit; file paths follow the PSR-4 test namespace and line numbers are read
  from the source text, so no test code is loaded. With a defects file,
  `@group known-defect` tests are labelled with their KD IDs. It replaces an
  ad-hoc generator that lived outside the repository.
- Renames under `docs/modernization/` (MOD-115 paths to new paths):

| MOD-115 path | New path |
| --- | --- |
| `testing.md` | `TESTING.md` |
| `php-7.2/active/test-inventory.md` | `php-7.2/TEST-INVENTORY.md` |
| `php-7.2/active/known-defects.md` | `php-7.2/active/KNOWN-DEFECTS.md` |
| `php-7.2/active/consumer-impact.md` | `php-7.2/active/CONSUMER-IMPACT.md` |
| `php-7.2/notes/collections-routing-cli.md` | `php-7.2/notes/COLLECTIONS-ROUTING-CLI.md` |
| `php-7.2/notes/data-and-xml.md` | `php-7.2/notes/DATA-XML.md` |
| `php-7.2/notes/database-assets-minifier.md` | `php-7.2/notes/DATABASE-ASSETS-MINIFIER.md` |
| `php-7.2/notes/debug-templates-text.md` | `php-7.2/notes/DEBUG-TEMPLATES-TEXT.md` |
| `php-7.2/notes/mapper.md` | `php-7.2/notes/MAPPER.md` |
| `php-7.2/notes/payment-and-alert.md` | `php-7.2/notes/PAYMENT-ALERT.md` |
| `php-7.2/notes/session-and-http.md` | `php-7.2/notes/SESSION-HTTP.md` |
| `php-7.2/notes/utility-and-event.md` | `php-7.2/notes/UTILITY-EVENT.md` |
| `php-7.2/history/coverage-baseline.md` | `php-7.2/history/COVERAGE-BASELINE.md` |
| `php-7.2/history/pending-behavior-questions.md` | `php-7.2/history/PENDING-QUESTIONS.md` |
| `php-7.2/history/testing-follow-ups.md` | `php-7.2/history/TESTING-FOLLOWUPS.md` |
| `php-7.2/history/branch-investigation.md` | `php-7.2/history/BRANCH-INVESTIGATION.md` |
| `php-7.2/history/handoff-modernize-26.md` | `php-7.2/history/AGENT-HANDOFF.md` |
| `php-7.2/history/legacy-test-environment.md` | `php-7.2/history/LEGACY-TESTING.md` |

- Verified: `./tests.sh inventory` output matches the suite (704 methods, 1,305
  cases, 46 known-defect methods all labelled with KD IDs; 29 service-group
  methods marked); every relative Markdown link resolves.

## MOD-117 — Rename the test entry point to bin/test

- Owner direction: drop the `.sh` extension. A top-level `tests` file cannot
  coexist with the `tests/` directory, so the script moved to `bin/test`
  (`git mv`, executable bit kept). It now resolves the repository root from its
  parent directory, so it works from any working directory.
- Usage is unchanged apart from the name: `bin/test`, `bin/test unit`,
  `bin/test inventory ...`. Live documentation, `AGENTS.md`, the help text and
  `tests/docker/inventory.php` say `bin/test`; past entries and `history/`
  files keep `tests.sh`, as written.
- Verified: `bin/test all --offline` passes 1,305 tests; `../bin/test` from
  `docs/` and an absolute path from `/` both work; `bin/test inventory`
  regenerated TEST-INVENTORY.md.
- Undo: `git mv bin/test tests.sh`, restore the root-resolution line.

## MOD-118 — Reference audit after the renames

- Audit of every tracked file after MOD-115 to MOD-117: all 63 relative
  Markdown links and their `#` anchors resolve; no old document name,
  `tests.sh`, `moderization` or `modernization-26` remains outside past
  CHANGELOG entries and `history/` files (kept as written). Shorthand source
  and test paths in the notes resolve under `src/flat/` and `tests/Unit/`.
  `bin/test` is stored executable (100755).
- The inventory header now names the defects file by the path given to
  `bin/test inventory`, so the reference resolves from the repository root.
- Documented regeneration now writes to a temporary file first. A redirect
  straight onto TEST-INVENTORY.md emptied it when `bin/test` failed (the Docker
  daemon was not running); the file was restored before any commit.
- Verified: `bin/test all --offline` passes 1,305 tests; the documented
  command replaces the inventory on success and leaves it untouched on failure.

## MOD-119 — Move the test entry point to docker/bin/test

- Owner direction: `bin/` reads as the package's own executables (Composer can
  install `bin/` scripts into consumers' `vendor/bin`), which is misleading for
  a script that only drives Docker. It moves next to the Dockerfile and Compose
  file it uses: `docker/bin/test` (`git mv`, still 100755). It resolves the
  repository root two levels up, so it works from any working directory.
- Live documentation, `AGENTS.md`, help text and `tests/docker/inventory.php`
  now say `docker/bin/test`; past entries and `history/` files keep the names
  they were written with.
- Verified: runs from the repository root, from `docs/` and by absolute path;
  `docker/bin/test all --offline` passes 1,305 tests; the inventory regenerated
  with only its command line changed; all links and anchors resolve.
- Undo: `git mv docker/bin/test bin/test` and restore the one-level root lookup.

## MOD-120 — Split the test runner into native bin/test and the Docker wrapper

- Owner direction: `bin/test` is the native runner that could run on any
  machine with the tools installed; `docker/bin/test` only ensures the right
  containers and runs it, without duplicating its logic.
- New `bin/test` holds all test logic: modes (all/unit/services/mongo/rabbitmq),
  PHPUnit pass-through, `--coverage` reports under `build/tests/<mode>/`, and
  `inventory`. Explicit inputs: `FLAT_TEST_PHP`, `FLAT_TEST_PHPUNIT` (default
  `vendor/bin/phpunit`), `FLAT_TEST_BOOTSTRAP`; without PHPUnit it stops with a
  message pointing to `composer install` or `docker/bin/test`.
- `docker/bin/test` keeps only Docker work: root/UID handling, CA build input,
  `--offline`/`--build`/`--down`, starting the services a mode needs, cleanup.
  It strips its own options and runs `bin/test` in the PHP container with the
  remaining arguments. The Compose file sets `FLAT_TEST_PHPUNIT` and
  `FLAT_TEST_BOOTSTRAP` to the image's PHPUnit install.
- The inventory converter is no longer Docker-specific and moved from
  `tests/docker/inventory.php` to `tests/tools/inventory.php`.
- `AGENTS.md` records the placement rule: `bin/` for native scripts, `docker/`
  for Docker-only wrappers that mirror and wrap them.
- Verified through `docker/bin/test` (which runs `bin/test` on PHP 7.2.34):
  unit 1,259, mongo 39, rabbitmq 7, services 46 tests pass; `all --coverage`
  passes 1,305 tests with 100% classes/methods/lines and writes all reports;
  PHPUnit options pass through (also after `--`); a PHPUnit error returns a
  nonzero exit; no containers remain. Natively on this host (PHP 8.3, no
  PHPUnit) `bin/test` reports the missing PHPUnit; a native PHP 7.2 install was
  not available to test.

## MOD-121 — Record documentation, defect and communication rules in AGENTS.md

- Owner direction: record the working rules established while organizing the
  phase documents. `AGENTS.md` gains three sections: documentation rules
  (conventions, never delete, generated files, consumer impact for every
  behavior change, change log never rewritten, link checks after moves), the
  known-defect process, and communicating with the maintainer (summaries in
  chat, long listings in files, questions instead of guesses).
- Documentation only; no code or test change.

## MOD-122 — Plain-language AGENTS.md sections and a CLAUDE.md pointer

- Owner direction: rewrite the first-person sections written by an earlier
  agent as plain rules. "Maintainer feedback" becomes "Reproducibility" and
  "Commit cadence" becomes "Commits and branches"; their requirements are
  unchanged.
- New root `CLAUDE.md` contains only `@AGENTS.md`, which imports it, so Claude
  Code reads the same rules as other agents; `AGENTS.md` stays the single source.

## MOD-123 — Owner answers all remaining known-defect questions

- The owner read every open question and the KD entries they reference and
  agreed with all 20 recommendations (questions 1-5, 7-11, 13-15, 17-23).
  Each question in `active/KNOWN-DEFECTS.md` is marked answered; every open
  defect is now actionable. No code change in this entry; fixes follow.
