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

## MOD-194 — Documentation: shorter READMEs, new rule against history in READMEs

- Kind: documentation. The php-7.3 README status is cut to three lines that link here; the
  main README's php-7.3 progress line is one line. `AGENTS.md` (flat and flat-webapp) gets a
  rule: a README says what is true now, history goes in a changelog or note, documents stay
  short. Maintainer's request, after `da67d6c`. No other change.

## MOD-195 — Documentation: the reason for the MOD-194 rule is written into AGENTS.md

- Kind: documentation. At the maintainer's request, the rule added in MOD-194 now says why it
  exists: the maintainer scolded the agent and was not pleased. flat and flat-webapp
  `AGENTS.md`. No other change.

## MOD-196 — Behavior change (KD-80): JShrink keeps the character after a regex starting with a quote

- Kind: behavior change (bug fix), owner decision Q37 (2026-11-21). Replaces the `break` of MOD-190.
- `src/flat/vendor/tedious/JShrink/minifier.php`, `loop()`: `continue 3;` (was `break;` after
  MOD-190, `continue;` before). `saveRegex()` already reads the next real character into `$this->b`;
  the loop's own `getReal()` that followed lost it.
- Tests (`JsMinifierTest`): one expected value corrected (`function f(){return /'quoted'/;}`, it
  had pinned the lost `;`), three cases added (`.test(s)`, `;foo()`, `;}` before a newline). All four
  fail with the old code, pass with the fix.
- Consumer impact: [active/CONSUMER-IMPACT.md](active/CONSUMER-IMPACT.md). Verified:
  `docker/bin/test all --coverage`.
- Undo: put `break;` back (the four tests then fail).

## MOD-197 — Test change (Q39): `ConfigTest` fails as root instead of skipping

- Kind: test, no library change. `testDirectoriesAndUnreadableFilesAreNotLoadedAsConfig` asserted
  nothing and skipped when the process was root; it now fails with a message. Shown failing as
  root and passing as user 1000 (the harness user).

## MOD-198 — Approved exception (Q38): AWS SDK deprecation notice in two tests

- Kind: documentation and comments. At the owner's decision the two tests that set
  `AWS_SUPPRESS_PHP_DEPRECATION_WARNING` are listed in "Approved exceptions" in `AGENTS.md`, each
  with a comment block; the php-8.1 phase must remove them. flat-webapp's `AGENTS.md` now points
  to flat's list. The old comment "verifies the maintained PHP 7.2 baseline" is replaced.

## MOD-199 — Finding: the test environment hides notices and deprecations (Q40)

- Kind: finding, nothing hidden or lowered by me, nothing changed yet. Found while checking a
  `strpos()` deprecation that the suite did not report. Corrects MOD-189 to MOD-192 and the handoff,
  which said no warning, notice or deprecation remained: that held for warnings and errors only.
  Details and the 19 failing tests of a measured run: [active/HIDDEN-ERRORS.md](active/HIDDEN-ERRORS.md).
  Question Q40 asks for the owner's decision.

## MOD-200 — Environment: report everything (answers Q40)

- Kind: environment, owner's instruction (2026-11-21): deprecations and warnings are never
  suppressed on dev and are fixed when found.
- `docker/Dockerfile`: the image copies PHP's `php.ini-development` to `php.ini`
  (`error_reporting = E_ALL`), and the image build fails unless `error_reporting()` is `E_ALL`.
- `phpunit.xml.dist`: `convertDeprecationsToExceptions`, `convertErrorsToExceptions`,
  `convertNoticesToExceptions` and `convertWarningsToExceptions` are all `true`.
- Result: 20 tests failed at once ([active/HIDDEN-ERRORS.md](active/HIDDEN-ERRORS.md) has the
  first measurement); MOD-201 to MOD-203 fix them. Rule added to `AGENTS.md` (flat and flat-webapp).

## MOD-201 — Runtime-compatibility fixes, no behavior change (KD-81, 82, 84, 86, 87)

- Kind: runtime-compatibility fix. Each is a notice or deprecation fixed at its cause; callers see
  nothing different. Detail per item in [active/KNOWN-DEFECTS.md](active/KNOWN-DEFECTS.md).
- `core/html/encode.php` (`_option_to_param` static), `core/curl/request.php` (initialize
  `$fields_string`), `core/collection/controller.php` (`get()` and `is_exist()` check the index),
  `orm.php` (no `each()`), `vendor/tedious/JShrink/minifier.php` (guard the `strpos()` needle).
- Verified: `docker/bin/test all --coverage`, 1,432 tests, 100%.
- Undo: revert the commit (the tests then fail, since the environment reports the errors).

## MOD-202 — Behavior changes (KD-83, KD-85): two exception messages

- Kind: behavior change (bug fix), owner's instruction to fix what can be fixed now.
- `bad_dim` says why (`... width must be numeric.`); the mapper's `cannot_get_property` names the
  property. Tests assert the new messages (`DimTest` gained a message check; `MapperTest`'s
  expected message changed). Consumer impact: [active/CONSUMER-IMPACT.md](active/CONSUMER-IMPACT.md).

## MOD-203 — Tests that relied on hidden notices

- Kind: test correction, no assertion weakened. `ResponseTest` read properties the response
  objects do not have (`data`, `message`) and asserted `null`, which raised a notice. It now asserts
  the property is absent (`assertObjectNotHasAttribute`), which is stricter, and reads an absent
  `message` with `?? ''` where it only checks the text.

## MOD-204 — Line endings: every file is LF, and Git enforces it

- Kind: whitespace only, no content change (`git diff --ignore-cr-at-eol` is empty).
- 21 tracked files in flat had CRLF line endings (1,442 lines): 19 under `src/flat`
  (`orm.php`, the `orm/` folder, `exception.php`, `core/collection/controller.php`,
  `core/controller/data.php`, `core/exception/`, `cloud/authorize/`, `db/crud.php`,
  `data/validate_failure_provider.php`, `api/response/invalid_status.php`) and two vendored files
  (`vendor/azuyalabs/yasumi/dateQuery.php`, `vendor/neitanod/forceutf8/Encoding.php`).
  All are now LF; no other tracked file had CRLF. flat-webapp had none.
- Why it was missed: flat had no `.gitattributes`, and its `.gitignore` ignores every dotfile
  except two, so one would have been ignored. Now `.gitattributes` holds `* text=auto eol=lf`
  (the same line as flat-webapp) and `.gitignore` allows it.
- Verified: `git ls-files --eol` shows every text file as `i/lf w/lf`; `docker/bin/test all
  --coverage`, 1,432 tests, 100%.

## MOD-205 — Documentation: broken `history/` link in the phase README

- Kind: documentation. The README linked an empty `history/` folder, which Git does not keep, so
  `check-docs` failed on a fresh clone (not in the working copy). The link is removed; the
  folder is created when a document first moves there. Found by the pre-release check from a clone.

## MOD-206 — Documentation: `history/` is kept with a `.gitkeep` (replaces the MOD-205 fix)

- Kind: documentation, owner's suggestion. `docs/modernization/php-7.3/history/.gitkeep` keeps the
  empty folder in Git, and `.gitignore` allows `.gitkeep` (it ignores other dotfiles). The phase
  README links `history/` again, as the phase skeleton in `docs/modernization/README.md` expects.

## MOD-207 — Fix (KD-88): XML encode of a resource raises no warnings

- Kind: runtime-compatibility fix, no behavior change (output identical). `core/xml/encode.php`:
  `htmlspecialchars()` only for scalars; the checksum hashes `''` explicitly instead of an undefined
  variable. `EncodeTest::testResourceFallbackWithAndWithoutDumpRaisesNoWarnings` (renamed from
  `...PreservesWarnings`) no longer captures warnings, so any warning fails it; shown failing
  without the fix. Intent question Q42.

## MOD-208 — Pre-release check: findings and corrections

- Kind: verification. A full re-check from a fresh clone found: the broken `history/` link
  (MOD-205, MOD-206); one more swallowed deprecation, in `tests/Support/LegacyAuthorizeSdk.php`,
  recorded as Q41 and not changed; the XML warnings pinned by a test (KD-88, MOD-207).
- Also: `notes/API-V1.73.0.json` was regenerated from the final environment (only the free-text
  `reason` of the seven unloadable classes differs from the earlier dump; no API difference with
  `v1.72.0`); the phase README test count and the environment notes are corrected.

## MOD-209 — Approved exception (Q41) and two items for v2.X (Q42)

- Kind: documentation and comments, owner's decision. `tests/Support/LegacyAuthorizeSdk.php` is in
  the "Approved exceptions" list of `AGENTS.md` and carries a comment block. `DEFERRED-V2.md` gets
  item 2 (port the Authorize.Net wrappers to the SDK's current API) and item 3 (what an XML resource
  checksum covers). No code change.

## MOD-210 — Functional tests in flat-webapp (documentation here)

- Kind: documentation; the code is in flat-webapp (its tests folder and its docker/bin/test script, Dockerfile.test and
  compose.test.yml). Owner's request, 2026-11-21. Overview and findings:
  [active/WEBAPP-FUNCTIONAL.md](active/WEBAPP-FUNCTIONAL.md). Two findings in `app/` became questions
  Q43 and Q44. The handoff's "integration tests are on hold" sentence is replaced.

## MOD-211 — Decision recorded: `PATCH` support waits for v2.X

- Kind: documentation, owner's decision (2026-11-21). New item 4 in
  [DEFERRED-V2.md](../DEFERRED-V2.md); Q43 is marked answered in part. No code change.

## MOD-212 — Decision recorded: flat-webapp's `app/` and `web/` are never changed

- Kind: documentation, owner's decision (2026-11-21). The application models what the one known
  consumer does, so it is not changed even for bugs. Q43 and Q44 are answered on that basis (the
  snippets they carried are not applied); the behaviors stay, documented in
  [active/WEBAPP-FUNCTIONAL.md](active/WEBAPP-FUNCTIONAL.md). flat-webapp's `AGENTS.md` says so.

## MOD-213 — Correction to MOD-212: `app/` changes for literal bugs only

- Kind: documentation, owner's clarification (2026-11-21). MOD-212 said `app/` and `web/` are never
  changed; the rule is: only for a literal bug. Q43: a 500 is fine and flat has no 501 response, so
  nothing changes. Q44 is a literal bug but its obvious fix would raise a PHP fatal error (the check
  runs before the error handlers exist), so it is open again and left unchanged.

## MOD-214 — flat-webapp: the malformed-JSON check works (Q44)

- Kind: bug fix in flat-webapp's `app/AppWeb/InputTrait.php`, owner's decision (2026-11-21): one
  line, no custom 500 handling, no new flat object. `json_decode()` returns `null` on bad JSON, which
  the old `false !== ...` test accepted; it now tests `json_last_error()`. Malformed JSON ends in the
  app's own `trigger_error(E_USER_ERROR)`, a PHP fatal error and a blank 500; a valid JSON `false`
  body is accepted (it used to be called malformed). The functional test for a malformed body is
  replaced by one for `false`. Why 405 does not apply to `PATCH`: flat throws `unknown_method` before
  any response exists, and a 405 needs an `Allow` header (`DEFERRED-V2.md` items 1 and 4).

## MOD-215 — Documentation: adversarial review handoff

- Kind: documentation, owner's request. [active/ADVERSARIAL-REVIEW-HANDOFF.md](active/ADVERSARIAL-REVIEW-HANDOFF.md)
  briefs a second agent to review the phase: frozen commit ranges, ground rules, the claims to try to
  break, and where the author expects weaknesses. No code change.

## MOD-216 — Documentation: review findings go into v1.73.1

- Kind: documentation, owner's decision. The adversarial review handoff now says the review
  targets the frozen v1.73.0 state, is report-only, and that confirmed findings are fixed in v1.73.1,
  one at a time with options from the author. No other change.

## MOD-217 — Documentation: independent adversarial review

- Kind: documentation, owner's request. Corrected the review handoff: v1.73.1 is unreleased
  and this review prepares it; removed the exclusion of v1.73.1 work. This clarifies MOD-216.
- [Review](active/ADVERSARIAL-REVIEW.md) records eight findings from source inspection,
  test-assertion review and Docker experiments, with evidence and reproduction steps. No
  source, tests or locks changed. The full framework suite passes; the webapp HTTP run was
  blocked by dependency-download HTTP 403 responses. Fixes await owner review.

## MOD-218 — JShrink preserves comment token boundaries (KD-89)

- Kind: bug fix, adversarial finding 1. Removed global empty-comment stripping. The parser now
  accepts empty comments, preserves comment whitespace/newlines and separates adjacent plus/minus
  tokens. Quoted comment text remains intact. Consumer impact: KD-89 in the phase impact list.
- Added shared regression cases: PHPUnit checks output, and `docker/bin/test-javascript` executes
  original/minified programs against explicit expected results. The execution check uses a pinned
  Node container; PHP minification still uses the existing PHP 7.3 image. Both checks reproduced
  the defect before the source fix. Inventory regenerated. Validation: all 1,447 tests pass on
  PHP 7.3.33 (6,372 assertions, full service suite, 100% reported coverage); all 15 JavaScript
  programs preserve their expected results in the execution check.

## MOD-219 — HTML parsing preserves libxml reporting (KD-90)

- Kind: bug fix, adversarial finding 2. Removed global error-capture changes from cURL DOM
  initialization and bundled ParsedownExtra HTML parsing. Scoped cURL XPath diagnostic capture
  to the query with `finally` restoration, retaining its existing exception and diagnostic text.
- Tests reproduce the leaks and hidden HTML warnings before the fix, check both caller modes
  and existing buffered errors, and verify restoration when XPath reports an error or a PHP
  warning interrupts it. The nested HTML5 Markdown output assertion is retained alongside exact
  expectations for its previously hidden parser warnings. Consumer impact recorded; inventory
  regenerated. Validation: all 1,454 tests pass on PHP 7.3.33 (6,402 assertions, MongoDB and
  RabbitMQ included), with 100% reported class, method and line coverage.

## MOD-220 — cURL form key encoding and URL validation

- Kind: bug fixes, findings 3 and 4 (KD-91, KD-92). URL-encode form names and reject absent URL
  schemes before reading the scheme index. Existing scalar-value encoding is retained; nested
  values need the separate Q45 decision.
- Focused request tests reproduced one encoding failure and four native notice errors before
  the fixes; all 33 request cases now pass. Consumer impact recorded and inventory regenerated.

## MOD-221 — API comparison detects reference returns

- Kind: tooling fix, finding 7. Dump ReflectionMethod::returnsReference and compare changes in
  either direction. Existing baselines without this flag default to value returns.
- New tool-level controls reproduced two missed-reference failures before the fix. All three
  tool regression tests now pass, including parameter-type changes and legacy-baseline handling.
  No framework API change. Inventory regenerated.

## MOD-222 — Lock flat-webapp to the published PHP 7.3 release

- Kind: reproducibility fix, finding 8. flat-webapp now requires flat 1.73.* and tracks its
  application Composer lock. It resolves v1.73.0 at 8bee551; tests verify the installed version
  and reference against that lock, and reconcile existing installs before running.
- Development Apache workers use the configured host UID/GID so private checkouts are readable.
  Test HTTP traffic bypasses external proxies for the local web service. An explicit Composer
  source-download option supports environments without archive access; test images omit Git
  histories. PHP remains 7.3.33 and application behavior is unchanged.

## MOD-223 — Reject structured errors in flat-webapp's log gate

- Kind: tooling fix, finding 5. The runner now uses a tested PHP log checker for native PHP
  messages (including catchable/recoverable fatals), application JSON errors and truncated error
  records. Its command exits nonzero for errors; ordinary access/startup logs pass.
- Isolated native PHP tests exercise the real fatal/exception handlers and ensure their emitted
  records are rejected by the gate. The application error handlers and responses are unchanged.

## MOD-224 — Observe parsed webapp input and test failure contracts

- Kind: tests, finding 6. A test-only HTTP endpoint exposes the actual InputTrait result for
  JSON, inferred JSON, form data and GET input. No app/ or web/ code changed. Native malformed-JSON
  and PATCH paths are checked in separate PHP processes with exact expected failures.
- A scratch empty-input mutant causes six new input checks to fail; the five existing
  unsupported-method body checks still pass. The fixture is enabled only in the test stack,
  which exposes no host port.
- Validation: the full framework suite passes 1,462 tests, 6,454 assertions and 100% reported
  coverage. flat-webapp passes 66 tests, 196 assertions, including real HTTP checks and the strict
  server-log gate. Composer validates the application lock. No release was published.

## MOD-225 — Compact, reproducible webapp test images

- Kind: build tooling. Keep source-download Git histories out of the runtime image. An explicit
  WEBAPP_GITHUB_ARCHIVES option seeds Composer's cache from official GitHub ZIPs at the locked
  commit references when API archives are unavailable. The original lock remains unchanged.
- Separate application and test-tool download preferences. The rebuilt PHP 7.3.33 test image is
  about 470 MB and passes all 66 webapp tests, 196 assertions and the strict log gate. Removed
  the oversized temporary image and its build-cache descendants created during this work.

## MOD-226 — Refuse incomplete legacy API metadata

- Kind: tooling correction to MOD-221. A missing return-reference flag cannot be treated as a
  known value return: an old reference-returning method could otherwise change unnoticed.
  Comparisons now exit 2 with a short regeneration instruction when either dump lacks the flag.
- Controls cover both reference-change directions, incomplete legacy dumps, equal complete dumps
  and parameter-type changes. Historical API dumps are retained; new comparisons must re-dump
  both versions with the current tool.
- Final validation: all 1,462 framework tests pass (6,458 assertions, full service suite and
  100% reported coverage). All 66 webapp tests and its strict log gate pass on the compact image.

## MOD-227 — Reject unsupported form values (Q45, KD-93)

- Kind: bug fix, owner's decision on Q45. Validate URL-encoded field values before passing them
  to `urlencode()`. Nested arrays, non-stringable objects and resources now raise `bad_param`;
  scalar/null/stringable values keep their encoding, and JSON remains available for nested data.
- Reproduced seven native warnings before the fix. Added constructor regressions for nested
  array/object containers, bracketed query-string input and resources, plus flat-value and nested
  JSON compatibility checks. Consumer impact recorded and inventory regenerated.
- Validation: all 1,471 tests pass on PHP 7.3.33 (6,480 assertions, MongoDB/RabbitMQ included),
  with 100% reported class, method and line coverage.

## MOD-228 — Tooling upgrade 1 of 7: test-lock packages (within their constraints)

- Kind: tooling, owner's instruction (2026-11-23): upgrade every tool PHP 7.3 allows, one at a time
  (the php-7.3 handoff had said "nothing else"; that applied to the PHP step only).
- `tests/docker/tools/composer.lock`: PHPUnit 8.5.56 to 8.5.57. `tests/docker/runtime/composer.lock`:
  Guzzle 7.17.0, `guzzlehttp/promises` 2.6.2, `guzzlehttp/psr7` 2.13.6, jmespath 2.10.0, phpseclib
  3.0.59. `php-token-stream` stays at 3.1.3: Composer offered 4.0.4, a 2020 release of an abandoned
  package that only sorts higher (it leaves with PHPUnit 9). Links and `plugin-api-version` keep the
  old lock's form. Already the newest PHP 7.3 allows, so unchanged: `mongodb/mongodb` 1.16.1, the
  AWS SDK 3.337.3, the MongoDB extension 1.16.2 (1.17 needs PHP 7.4).
- Verified: `docker/bin/test all --coverage`, 1,471 tests, 100%, no PHP message.

## MOD-229 — Tooling upgrade 2 of 7: the Composer image

- Kind: tooling. `docker/Dockerfile`: the dependency stage uses `composer:2.11` (2.11.2) pinned by
  digest, was 2.2.31. It installs from the locks exactly as before (no blocking by the newer security
  check; it only warns that `doctrine/annotations` is abandoned). The locks were not regenerated.
  The Composer that runs the tests' packages is still the image's PHP 7.3.33 runtime.
- Verified: `docker/bin/test all --coverage`, 1,471 tests, 100%, no PHP message.

## MOD-230 — Tooling upgrade 3 of 7: ImageMagick

- Kind: tooling. `docker/Dockerfile`: ImageMagick 6 is built from the pinned commit of release
  6.9.13-61, the newest 6.x (was 6.9.10-23). Same configure options. The imagick extension and the
  MongoDB extension layers rebuilt on top of it.
- Verified: `docker/bin/test all --coverage`, 1,471 tests, 100%, no PHP message.

## MOD-231 — Tooling upgrade 4 of 7: the imagick extension

- Kind: tooling. `docker/Dockerfile`: imagick is built from the pinned commit of release 3.8.1 (was an
  unreleased snapshot that reported its version as `@PACKAGE_VERSION@`). It supports PHP 5.6 and later.
- Verified: `docker/bin/test all --coverage`, 1,471 tests, 100%, no PHP message.

## MOD-232 — Correction to MOD-231: imagick still reports `@PACKAGE_VERSION@`

- Kind: documentation. MOD-231 implied the old snapshot's placeholder version was fixed by the upgrade.
  It is not: an extension built from a Git commit never fills in `@PACKAGE_VERSION@`, so
  `phpversion('imagick')` shows it for any release. The pin is the commit of the `3.8.1` tag
  (`70087bab33eab913e99ac77d64d04d1a2fd0b7b0`); that is how the version is known.

## MOD-233 — Tooling upgrade 5 of 7: Xdebug

- Kind: tooling. `docker/Dockerfile`: Xdebug 3.1.6 (the last release for PHP 7.3; 3.2 needs PHP 8.0) from
  its pinned commit, was 2.9.8. Xdebug 3 needs its mode set: `xdebug.mode=develop,coverage` (develop
  keeps the enhanced diagnostics 2.x had by default; coverage is what `--coverage` needs). No error
  reporting setting is touched; the image still reports `E_ALL`.
- Verified: `docker/bin/test all --coverage`, 1,471 tests, 100%, no PHP message.

## MOD-234 — Tooling upgrade 6 of 7: PHPUnit 9.6

- Kind: tooling and test code, no library change. PHPUnit 8.5 to 9.6.39 (the last line for PHP 7.3),
  with php-code-coverage 9.2.32 and the other `sebastian/*` packages; `php-token-stream` leaves with
  it. `tests/docker/tools/composer.json` and its lock were re-resolved on the 7.3.33 platform;
  the root `composer.json` dev requirement follows.
- `phpunit.xml.dist`: coverage settings moved to PHPUnit 9's `<coverage>` element with the same
  meaning (`src/flat`, uncovered files not processed). The four convert-to-exception settings stay.
- Tests: `assertFileNotExists` to `assertFileDoesNotExist` (7), `assertRegExp` to
  `assertMatchesRegularExpression` (5). `assertObjectNotHasAttribute`, deprecated in 9.6 with a
  replacement that only exists in PHPUnit 10, became `property_exists()` (3, `ResponseTest`).
  `tests/Support/PhpSubprocess.php` hands the child's raw Xdebug coverage to php-code-coverage 9 as
  `RawCodeCoverageData` (17 tests failed with a TypeError under `--coverage` until then).
  No assertion was removed or loosened.
- Coverage numbers changed, still 100%: classes 332 (was 331), methods 1,063 (1,062), statements
  8,393 (8,061). Measured against 8.5 on the unit suite: php-code-coverage 9 counts executable lines
  differently in 264 files (+328 statements net), and counts one class the old one did not:
  `data/orm/driver/session/exception/bad_param.php` (a one-line constructor), now covered.
- The inventory generator and the mutation probe work unchanged (inventory output is identical).
- Verified: `docker/bin/test all --coverage`, 1,471 tests, 6,480 assertions, 100%, no PHP message.

## MOD-235 — Clarification of MOD-234: the extra class in the coverage count is not new code

- Kind: documentation. `flat\data\orm\driver\session\exception\bad_param` has been in the source since
  2026-11-13 (php-7.2 phase, KD-19-23); no Codex-era commit touched it. It is one of 17 `bad_param`
  classes, one per component. The old php-code-coverage reported 0 classes, methods and statements
  for it even while a test threw it; php-code-coverage 9 reports 1, 1 and 1, covered. Checked by
  running `SessionDriverTest` under both PHPUnit versions. A counting difference only.

## MOD-236 — Tooling upgrade 7a of 7: the MongoDB test service

- Kind: tooling and one test fixture. `docker/docker-compose.yml`: MongoDB 5.0 pinned by digest (was 4.0),
  health check through `mongosh`. 7.0 (the newest the 1.16 driver supports) was tried first and
  failed: flat's update count needs the `getLastError` command, removed in MongoDB 5.1 (Q46, open).
  5.0 is the newest where the unchanged library works.
- `CrudTest::testInvalidDocumentWritePreservesTheDriverException` used `['$invalid'=>'document']`,
  which MongoDB 5.0 accepts, so it no longer raised the driver exception. It now writes an array
  `_id`, which every MongoDB version rejects; same assertion. Passes on 4.0 and on 5.0.
- Verified: `docker/bin/test all --coverage`, 1,471 tests, 100%, no PHP message.

## MOD-237 — Tooling upgrade 7b of 7: the RabbitMQ test service

- Kind: tooling. `docker/docker-compose.yml`: RabbitMQ 3.13.7 pinned by digest (was 3.8.34). 4.4 was
  tried first and failed: flat's default queue declaration (non-durable, non-exclusive) is denied by
  RabbitMQ 4 (Q47, open). 3.13 is the newest where the unchanged library works.
- Verified: `docker/bin/test all --coverage`, 1,471 tests, 100%, no PHP message.

## MOD-238 — Tooling upgrade 8 of 9: flat-webapp's Composer image

- Kind: tooling, in flat-webapp (`docker/compose.yml` and `docker/Dockerfile.test`): `composer:2.11`
  pinned by digest, was 2.2. Installs from the existing locks are unchanged.
- Verified: `docker/bin/test` in flat-webapp, 66 tests, clean server log (run from a scratch copy; see the
  root-run log bug reported with the adversarial review fixes, not yet fixed).

## MOD-239 — Tooling upgrade 9 of 9: flat-webapp's PHPUnit

- Kind: tooling and two test lines, in flat-webapp. PHPUnit 9.6.39 (was 8.5.57) with the matching
  `sebastian/*` packages, relocked on the 7.3.33 platform; `assertRegExp` became
  `assertMatchesRegularExpression` (2, `tests/Unit/DependencyLockTest.php` there). No assertion changed otherwise.
- Verified: `docker/bin/test` in flat-webapp, 66 tests, 196 assertions, clean server log.

## MOD-240 — Fix (KD-94): flat-webapp's log check on a root host (v1.73.1)

- Kind: tooling bug fix, in flat-webapp (its `docker/bin/test` and its log check script), owner's decision
  (2026-11-23). `mktemp` made the log copy private, so a container running as another user could not read
  it and the check failed with a misleading "Usage". The copy is now readable; an unreadable file and a
  wrong argument count have separate messages; a test covers both. Shown failing before the fix and
  passing after it from a root host with files owned by 1000, with no workaround.

## MOD-241 — Rule recorded: keep every tool, service and dependency current

- Kind: documentation, owner's directives (2026-11-23): be aggressive about updating tooling, services and
  dependencies; never hold them back for the known consumer; keep the public API compatible through
  workarounds. Now in `AGENTS.md` ("Keep everything current"), in the phase rules of
  `docs/modernization/README.md`, and in flat-webapp's `AGENTS.md`, so it applies to every later phase and
  release, not only v1.73.x. The handoff's "nothing else" and "PHPUnit is a separate decision" are corrected.
