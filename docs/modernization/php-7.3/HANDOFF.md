# php-7.3 phase: handoff for the next agent

Written 2026-11-20, at the end of the php-7.2 phase. It tells you what to do, what to read
first, what state everything is in, and the traps that cost time before. It does not repeat
what the other documents say; it points to them.

**Do not change anything until the owner says go.** This document is not that go-ahead.
Moving to the next PHP version needs the maintainer's explicit approval (rules in
`AGENTS.md` and `docs/modernization/README.md`). Say what you are about to do and wait.

## 1. Read these first, in this order

1. `AGENTS.md`: the working rules. They override your defaults.
2. `docs/modernization/README.md`: the plan, the rules every phase follows, the release scheme
   (`v1.<PHP major><PHP minor>.0`, so `v1.73.0`), and the branch rules.
3. `docs/modernization/TESTING.md`: how to run every test (always through `docker/bin/test`).
4. `docs/modernization/php-7.2/README.md` and its `RELEASE-NOTES.md`: what the finished phase
   delivered and how it was run. Its "Resume here" steps are the template for yours.
5. `docs/modernization/php-7.2/active/KNOWN-DEFECTS.md`: especially "How each fix lands", the
   categories A, B and C, and how owner questions are recorded.
6. `docs/modernization/php-7.2/active/CONSUMER-IMPACT.md`: every behavior change made so far,
   and the format a new entry must follow.
7. `docs/modernization/php-7.2/notes/DEPENDENCY-ALERTS.md`: the Dependabot situation, which
   depends on the PHP version you are moving to.
8. `docs/modernization/php-7.2/history/AGENT-HANDOFF.md`: the previous handoff (older, between
   agents; read for tone and for what was learned then).

The owner's working style is in the rules, but it bears repeating: chat replies are summaries,
long output goes into a file under the phase folder; when a fix needs a decision, record the
question with a recommendation in the open questions and wait, never choose silently;
Markdown must read well as plain text (lists, no wide tables); never delete a document.

## 2. State at handoff

- **flat:** `master`, `modernize-26` and `php-7.2` were at the same commit when this was
  written (the branch this file is on, `modernize-26`, is one commit ahead). Release `v1.72.0`
  is published (tag `619adcb`). No `v1.72.1`: the owner decided against it.
- **Tests:** 1,428 unit tests pass on PHP 7.2.34 with 100% class, method and line coverage
  (1,062 methods, 8,057 lines). PHPUnit 8.5. The inventory is generated; see the phase
  README for the command.
- **Known defects:** 79 tracked, all resolved. KD numbers continue at KD-80, owner questions
  at Q36, change-log entries at MOD-180 (the last one used is MOD-179). Whether a new phase
  keeps counting or restarts is decision D3 below.
- **Test environment (all in the repository):** `docker/Dockerfile` (PHP 7.2 base image pinned
  by digest; xdebug, ImageMagick and imagick, the MongoDB extension 1.16.2 with libmongoc, and
  sockets are built from pinned commits; Composer 2.2 pinned by digest for the dependency
  stage), `docker/docker-compose.yml` (MongoDB 4.0 and RabbitMQ 3.8.34 by digest), and
  `tests/docker/runtime` and `tests/docker/tools` (Composer manifests and locks, with the
  platform pinned to PHP 7.2.34 and `ext-mongodb` 1.16.2).
- **Dependabot:** one alert fixed, four dismissed or to be dismissed by the owner (fixes need
  PHP 8.1 or later). Re-run `composer audit --locked` on both locks at the end of your phase
  and update `notes/DEPENDENCY-ALERTS.md` in your phase folder: a newer PHP may allow newer
  packages.
- **flat-webapp (`katmore/flat-webapp`):** the owner now writes it; you give snippets only when
  asked. Its `php-7.2` and `modernize-26` are the same commit, it requires flat `1.72.*`, its
  Docker setup is development-only, and it has no tests. Integration tests against it are on
  hold on purpose (see `docs/modernization/php-7.2/active/WEBAPP-TESTS.md`); do not write any
  until the owner asks. Its review and the consumer check are in `WEBAPP-REVIEW.md`.
- **Still open for the owner, not for you:** the consumer check against the closed-source
  application (the source is on drives the owner will only reach in mid-January; the tool is
  `bin/consumer-check`); deleting stale branches; creating tags and Releases.

## 3. What the php-7.3 phase is

One step only: make flat run and be tested on PHP 7.3, without changing behavior. Rules from
the modernization README that matter most here: fix the phase's known bugs before the PHP step
(there are none open now, but any you find go through the known-defect process first),
record the exact PHP patch version and tool versions used, rerun the whole suite, never weaken
an assertion or drop a check to make an upgrade pass, and keep runtime-compatibility fixes
separate from behavior changes, in the change log and in the commits. A runtime-compatibility
fix (a deprecation, a changed internal function) is not a behavior change; a result a
consumer could notice is, and gets a consumer-impact entry.

### Suggested steps

0. Confirm the go-ahead and the decisions in section 5 with the owner.
1. Create the branch `php-7.3` from `modernize-26` and push it (the session may also be told
   to use a session branch; mirror to it as the system instructions say). Scaffold the phase
   folder like `php-7.2`'s: README (status, how to resume, contents), CHANGELOG,
   `active/`, `notes/`, `history/`. The test inventory is generated, not written.
2. **Baseline first.** Before changing anything, run the full suite on 7.2 and confirm
   1,428 passing. Every later failure is then yours.
3. Change the environment: the base image (`php:7.3-cli`, pinned by digest; record the exact
   PHP 7.3 patch it contains, as 7.2.34 was recorded), rebuild the extensions from pinned
   commits (check each supports 7.3: the MongoDB extension 1.16.2 does; xdebug and imagick
   need a look), the `platform` pins in both `tests/docker/*/composer.json`, and re-lock under
   the 7.3 platform. The PHP version and image name are hard-coded in several places: the
   image name `flat-tests-php72` in `docker/docker-compose.yml`, comments in `docker/Dockerfile`,
   `docker/bin/*`, `bin/test` and the tools under `tests/tools`. Find them with a search, not
   from this list.
4. Run the suite. Triage every failure and every deprecation notice into: (a) a
   runtime-compatibility fix with no behavior change, (b) a behavior change or a defect (use
   the known-defect process), or (c) a test that was wrong. Commit each kind separately.
5. Scan for 7.3 problems beyond what the tests hit. The official 7.2-to-7.3 migration guide
   in the PHP manual is the authority; read it. From memory, and to be verified there, things
   that bite code like this: `continue` inside a `switch` now warns; PCRE moved to PCRE2
   (regular expressions in validation and text code can behave differently); heredoc and
   nowdoc closing markers can now be indented, which can change how a body is read;
   `compact()` with an undefined variable now notices; defining a case-insensitive constant is
   deprecated; some undocumented `mbstring` aliases are deprecated; the new `is_countable()`,
   `array_key_first()` and `array_key_last()` exist (see decision D4 before using them).
6. When green: re-run the API comparison against the previous release (section 4); re-run the
   mutation probe on a sample (`docker/bin/mutate`, see `active/MUTATION-PROBE.md`); run
   `bin/consumer-check` on flat-webapp; update the consumer-impact document; write the phase's
   release notes (`RELEASE-NOTES.md` of the previous phase is the model); run `bin/check-docs`.
7. The owner declares the phase complete, merges, creates the tag and the Release
   (`v1.73.0`, or a different number if D1 says so) and decides about branches. You cannot do
   those (see the traps).

## 4. Tools you will want that already exist

- `docker/bin/test`: all tests (`unit`, `services`, `mongo`, `rabbitmq`, `all`, flags
  `--coverage`, `--offline`, `--filter`); `docker/bin/test inventory <KNOWN-DEFECTS.md>`
  writes the inventory (write to a temporary file first, see the phase README).
- `docker/bin/mutate`: the mutation probe on a throwaway copy (`--services`, `--at=FILE:LINE`).
  Its record: `active/MUTATION-PROBE.md`.
- `tests/tools/apidump.php`: dumps and compares the public API with Reflection. Its use and
  its results for 7.2 are in `notes/API-COMPARISON.md`. Dump the new version and compare it to
  `notes/API-V1.72.0.json` (that file is the 7.2 baseline).
- `bin/consumer-check`: searches an application for the terms of the consumer-impact list.
- `bin/check-docs` (and `docker/bin/check-docs`): run it after every documentation change.

## 5. Decisions to bring to the owner (with a recommendation each)

- **D1: the `php` constraint in `composer.json` (now `>=7.2.1`) and the version number.**
  If the new release no longer supports 7.2, is that a breaking change? The owner's rule is
  that the major version stays 1 until something breaks existing callers, and moves to 2 on
  the owner's decision. Recommendation: keep `>=7.2.1` if the suite passes on both versions
  (see D2); if flat must drop 7.2, ask whether that counts as breaking, because it decides
  between `v1.73.0` and `v2.73.0`.
- **D2: run the suite on 7.2 and 7.3 together, or replace 7.2.** Recommendation: run both
  during the phase (a version argument for the image), so a failure is always attributable,
  and drop 7.2 only on D1's answer.
- **D3: numbering of MOD, KD and Q across phases.** Recommendation: keep counting
  (MOD-180, KD-80, Q36) so every reference in the project stays unambiguous.
- **D4: whether flat's source may use 7.3-only functions.** Recommendation: no. This phase is
  runtime compatibility; new language features belong to later work.
- **D5: whether flat-webapp gets a `php-7.3` branch and Docker image change.** It is the
  owner's code; recommendation: ask when the library phase is done, not before.

## 6. Traps (things that cost time or caused mistakes)

Environment:
- A fresh container has no Docker daemon running: start it first, in the background, with
  its output in a log file. Builds need the sandbox's proxy CA: set `FLAT_TEST_CA_FILE` to
  the bundle the session documents (`/root/.ccr/README.md`) and, for Compose builds,
  `BUILDX_BAKE_ENTITLEMENTS_FS=0`.
- RabbitMQ sometimes exits at startup during a full run (`exited (0)`). It passes on a retry;
  its cause is not diagnosed. Retry before you suspect your change.
- Never pipe a long Docker build into `head`: the pipe closes and kills the build half way.
  Log to a file and read the file.
- The shell's safety check refuses `rm -rf` on a variable path. Do not work around it: use
  fresh `mktemp -d` directories in the scratchpad and delete nothing.
- Do not run PHP tests as root: file-permission tests cannot fail as root. The harness runs
  PHP as user 1000 when started as root; keep it that way.

Editing:
- 19 PHP files in `src/` and `tests/` use CRLF line endings (for example
  `src/flat/core/collection/controller.php`, `src/flat/orm.php`, `src/flat/exception.php`).
  An editor or script that converts them hides the real change and breaks "diff only what
  you changed". Edit them byte-preservingly and check `git diff --stat` is small. List them
  with a search for carriage returns before you edit a file you have not touched.
- Shell pitfall: `grep -c pattern file && python ...` skips the Python when the count is 0.
  Use `;` or separate commands. A test that cannot fail proves nothing: when you add or
  change one, show it failing without the fix.
- Match the existing style: dense tests, behavior asserted not implementation, a
  `@group known-defect` tag only while a test still asserts defective behavior.

GitHub and the session:
- This session's git access cannot push tags, delete branches, or read Dependabot alerts
  (HTTP 403). Do not try to work around it; give the owner the exact commands instead.
  Pushing branches works, including fast-forwarding `modernize-26` and `master` when the owner
  asks for it explicitly.
- `master` and `modernize-26` are the owner's. Touch them only on an explicit instruction, and
  check first that the push is a fast-forward or an empty merge.
- Commit in coherent groups with their documentation, run `bin/check-docs`, and push to the
  phase branch regularly. Commit messages end with the attribution lines the system
  instructions give.
- Change-log entries are never rewritten; a correction is a new entry, and a moved file needs
  an old-to-new path map in it.

## 7. Definition of done for the phase

- The whole suite passes on the new PHP, with 100% class, method and line coverage, the
  inventory regenerated, and nothing weakened (a diff of the tests shows no loosened
  assertion).
- The exact PHP patch version and the pinned image digests are recorded.
- The API comparison against `v1.72.0` is done and every difference has a verdict.
- Every behavior change has a consumer-impact entry and a change-log entry.
- Dependency audit re-run and `notes/DEPENDENCY-ALERTS.md` updated for the new PHP.
- Release notes are drafted for the owner; `bin/check-docs` is clean.
- Open questions are answered or recorded, and the owner has declared the phase complete.
