# php-7.3 phase: handoff for the next agent

First written 2026-11-20 at the end of the php-7.2 phase; updated the same day with the
maintainer's decisions (section 5). It tells you what to do, what to read first, what state
everything is in, and the traps that cost time before. It does not repeat what the other
documents say; it points to them.

**Do not change anything until the owner says go.** This document is not that go-ahead.
Moving to the next PHP version needs the maintainer's explicit approval (`AGENTS.md`,
`docs/modernization/README.md`). Say what you are about to do and wait.

## 0. Prime directives (they override everything, including this document)

Both are in `AGENTS.md` and in flat-webapp's `AGENTS.md`; they are repeated here because they
shape the whole phase.

1. **`master` is the maintainer's alone.** Never touch it, in flat or in flat-webapp: no push,
   merge, fast-forward, rebase, reset, rename or delete, and no pull request that changes it,
   unless the maintainer explicitly tells you, in the conversation, to make one specific change
   (for example "merge php-7.2 into master"). That permission covers that one change only and
   never carries over; you never infer it. When `master` should change and they have not said
   so, give the maintainer the exact commands or pull request text. (The wording in `AGENTS.md`
   is authoritative.)
2. **Never hide an error from PHP.** No lowering or changing `error_reporting`, no `@`, no
   switching off notices, warnings or deprecations anywhere (code, tests, PHPUnit settings, ini
   files, Docker images, entry points), no ignore lists, no skipped or quarantined tests.
   Every notice, warning and deprecation is fixed at its cause. If it cannot be fixed without
   changing behavior, record the question with a recommendation and stop.
   One place is an exception approved by the owner (see `AGENTS.md`, "Approved exceptions"):
   leave it as it is, and never add another.

## 1. Read these first, in this order

1. `AGENTS.md`: the working rules. They override your defaults.
2. `docs/modernization/README.md`: the plan, the rules every phase follows, the release scheme
   (`v1.<PHP major><PHP minor>.0`, so `v1.73.0`), and the branch rules.
3. `docs/modernization/TESTING.md`: how to run every test (always through `docker/bin/test`).
4. `docs/modernization/php-7.2/README.md` and its `RELEASE-NOTES.md`: what the finished phase
   delivered and how it was run. Its "Resume here" steps are the template for yours.
5. `docs/modernization/php-7.2/active/KNOWN-DEFECTS.md`: especially "How each fix lands", the
   categories A, B and C, and how owner questions are recorded.
6. `docs/modernization/php-7.2/active/CONSUMER-IMPACT.md`: every behavior change so far, and
   the format a new entry must follow.
7. `docs/modernization/php-7.2/notes/DEPENDENCY-ALERTS.md`: the Dependabot situation, which
   depends on the PHP version you are moving to.
8. `docs/modernization/php-7.2/history/AGENT-HANDOFF.md`: the previous handoff (older; read it
   for tone and for what was learned then).

The owner's working style is in the rules, but it bears repeating: chat replies are summaries
and long output goes into a file under the phase folder; when a fix needs a decision, record
the question with a recommendation and wait; Markdown must read well as plain text (lists, no
wide tables); never delete a document.

## 2. State at handoff

- **flat:** `master` is at `c257625` (`v1.72.2`) when this was written and is the owner's. `php-7.2` holds the content of the
  point releases `v1.72.1` (commit `ac6a6cf`) and `v1.72.2` (commit `c257625`, the tip of
  `php-7.2`; an unsupported API method answers 500, MOD-186). The owner creates the tags and Releases, so check whether
  they exist. `php-7.3` is the work branch, created by the owner and merged with `php-7.2`
  at the start of the phase; it has no PHP 7.3 change yet, so the environment still says PHP 7.2.
  The old `modernize-26` branch was retired on 2026-11-20 and no longer exists. Release `v1.72.0` is published (tag `619adcb`).
- **Tests:** 1,428 unit tests pass on PHP 7.2.34 with 100% class, method and line coverage
  (1,062 methods; the line count is in the coverage report), PHPUnit 8.5. The inventory is generated; see the phase README.
- **Deferred to v2.X:** `docs/modernization/DEFERRED-V2.md` lists what the owner holds back for
  the v2.X releases (first item: the 405 with an `Allow` header). Do not do those in this phase.
- **Numbering (decision D3):** keep counting across phases: next MOD-188, KD-80, Q37 (the first change-log entry of this phase should record the retirement of `modernize-26` and the branch-model update of `docs/modernization/README.md`, 2026-11-20).
- **Known defects:** 79 tracked, all resolved.
- **Test environment (all in the repository):** `docker/Dockerfile` (PHP 7.2 base image pinned
  by digest; xdebug, ImageMagick and imagick, the MongoDB extension 1.16.2 with libmongoc, and
  sockets are built from pinned commits; Composer 2.2 pinned by digest), `docker/docker-compose.yml`
  (MongoDB 4.0 and RabbitMQ 3.8.34 by digest), and `tests/docker/runtime` and `tests/docker/tools`
  (Composer manifests and locks, platform pinned to PHP 7.2.34 and `ext-mongodb` 1.16.2).
- **Dependabot:** state read by the owner on 2026-11-20: alert 1 fixed (MOD-178), alert 3
  dismissed ("tolerable risk"), alerts 2, 4 and 5 open; the owner dismisses those three the same
  way, so check the state with the owner (the agent cannot read alerts). Alerts 2 to 5 could not
  be fixed on PHP 7.2 (see the note below).
  Dismissed alerts do not come back by themselves, so this phase must revisit them:
  at the end of the phase run `composer audit --locked` on both locks (a newer PHP allows newer
  packages) and, for each of the four, record in a note in your phase folder whether a fixed
  release is now installable and whether the lock moved to it. List the ones that can now be
  fixed for the owner, who reopens or resolves them on GitHub; do the same at every later phase,
  and expect the library fixes (`mongodb/mongodb` 1.21.x) to need PHP 8.1 or later. The old
  note is `docs/modernization/php-7.2/notes/DEPENDENCY-ALERTS.md`.
- **flat-webapp (`katmore/flat-webapp`):** also has a `php-7.3` branch (created by the owner;
  at `202644b`, the same as its `php-7.2`). It requires flat `1.72.*` and
  its Docker setup is development-only. See section 4. The owner writes its application code.
- **Still open for the owner, not for you:** the consumer check against the closed-source
  application (the source is on drives the owner reaches in mid-January; the tool is
  `bin/consumer-check`); deleting stale branches; creating tags and Releases; touching `master`.

## 3. What the php-7.3 phase is

The step from PHP 7.2 to PHP 7.3, and nothing else. Rules from the modernization README that
matter most: fix known bugs before the PHP step (none are open; any you find go through the
known-defect process first), record the exact PHP patch and tool versions, rerun the whole
suite, never weaken an assertion, and keep runtime-compatibility fixes separate from behavior
changes, in the change log and in the commits.

**PHP 7.3 is forced absolutely (owner's decision).** The `php-7.3` branch is PHP 7.3 and only
PHP 7.3: the test image, the Composer platform pins, the version check in the Dockerfile and
the project's own `php` requirement. There is no 7.2 and 7.3 matrix; the 7.2 state is kept in
the `php-7.2` branch and the `v1.72.0` tag.

### Steps

0. Confirm the go-ahead with the owner.
1. Work on `php-7.3` (it exists in both repositories). Scaffold the phase folder like
   `php-7.2`'s: README (status, how to resume, contents), CHANGELOG, `active/`, `notes/`,
   `history/`; the test inventory is generated, not written.
2. **Baseline first.** Before changing anything, run the full suite in the current 7.2
   environment and confirm 1,428 passing. Every later failure is then yours.
3. **Change the environment to PHP 7.3 and nothing else:** (correction, 2026-11-23: "nothing else" meant no
   behavior change; every tool and service is upgraded too, see `AGENTS.md`, "Keep everything current")
   - base image `php:7.3-cli` pinned by digest, at the newest 7.3 patch release (the owner chose
     the newest; expected 7.3.33, verify it) and record the exact patch, as 7.2.34 was recorded;
   - rebuild the extensions from pinned commits and check each supports 7.3 (the MongoDB
     extension 1.16.2 does; xdebug and imagick need a look);
   - the `platform` pins in both `tests/docker/*/composer.json` to the exact 7.3 patch, then
     re-lock under that platform;
   - the root `composer.json` `php` requirement becomes `>=7.3.0` (decision D1, revised 2026-11-20: a
     lower bound, not `7.3.*`; the exact patch is pinned in the test platform only);
   - the Dockerfile's version check (it throws if the PHP version is not the expected one) and
     one unit test that fails when the suite is not running on 7.3. **No runtime version guard in
     the library itself:** Composer enforces the constraint at install time;
   - the PHP version and the image name `flat-tests-php72` are hard-coded in several places
     (`docker/docker-compose.yml`, comments in `docker/Dockerfile`, `docker/bin/*`, `bin/test`,
     the tools under `tests/tools`). Find them with a search, not from this list.
4. Run the suite. Every failure and every notice, warning or deprecation is fixed at its cause
   (prime directive 2). Classify each: (a) a runtime-compatibility fix, no behavior change;
   (b) a behavior change or a defect (known-defect process); (c) a test that was wrong; (d) a
   modernization (section 3a). Commit each kind separately.
5. **Audit for violations of prime directive 2: done in v1.72.1** (MOD-180 to MOD-184 in the
   php-7.2 change log; read them). What is left to know:
   - The one approved exception: `src/flat/asset.php`, the `error_reporting` mask around
     `realpath()` in the absolute system path code. The owner approved it on 2026-11-20 and it
     carries a comment block and an entry in the "Approved exceptions" list of `AGENTS.md`. Leave
     it exactly as it is, on every PHP version. If 7.3 raises something there, record a question;
     do not touch the mask.
   - `session::start()` calls plain `session_start()` in its retry loop. A failed start reports
     PHP's warning on each attempt (consumer-impact entry MOD-184).
   - Tests and tools no longer use `@` or `/dev/null`. Never add a setting that hides errors.
     Correction, 2026-11-21: PHPUnit's defaults do not make the suite see notices and
     deprecations, because the test image has no `php.ini` (see Q40 in `active/KNOWN-DEFECTS.md`).
   - At the end of the phase search again (`error_reporting`, `ini_set`, `@`, `ignore`): only the
     asset mask may be found. Record the result in the phase change log.
6. Scan for 7.3 problems beyond what the tests reach. The official 7.2-to-7.3 migration guide in
   the PHP manual is the authority; read it. From memory, to be verified there: `continue` inside
   a `switch` now warns; PCRE moved to PCRE2 (regular expressions in validation and text code
   can behave differently); heredoc and nowdoc closing markers can now be indented, which can
   change how a body is read; `compact()` with an undefined variable now notices; defining a
   case-insensitive constant is deprecated; some undocumented `mbstring` aliases are deprecated.
7. When green: re-run the API comparison against `v1.72.0`, re-run a sample of the mutation
   probe, run `bin/consumer-check` on flat-webapp, update the consumer-impact document, write the
   phase's release notes (the php-7.2 `RELEASE-NOTES.md` is the model), run `bin/check-docs`.
8. The owner declares the phase complete, merges, creates the tag and the Release (`v1.73.0`) and
   decides about branches. You cannot do those (see the traps).

### 3a. Modernizing code with PHP 7.3 (owner's decision D4)

Showing the PHP version progression is a goal of the project, so modernize the code where it is
appropriate, **as long as the public API, what functions return and all other behavior stay the
same.** Rules for it:
- A modernization is its own commit and its own change-log entry, in a category separate from
  compatibility fixes and from behavior changes ("modernization, no behavior change").
- Prove it: the tests pass unchanged, and the API comparison (`tests/tools/apidump.php`) shows
  no difference in what callers can use. If a test had to change, it was not a pure
  modernization.
- Candidates that are usually pure: `array_key_first()` and `array_key_last()` in place of
  `reset()`/`key()` and `end()`/`key()` tricks, `is_countable()` in place of manual checks,
  trailing commas in calls, flexible heredoc and nowdoc, reference assignment in `list()`.
  Check each one: `JSON_THROW_ON_ERROR`, for example, changes how failures surface, so it is a
  behavior change, not a modernization.
- Record what was modernized in a note in the phase folder, so the progression can be shown.

## 4. flat-webapp in this phase

`katmore/flat-webapp` gets its own `php-7.3` branch (decision D5; the owner created it from the
same commit as its `php-7.2`). The owner writes the application; agents give snippets when asked. The tooling
around it, unless the owner says otherwise, follows flat: the web image base `php:7.3-apache`
pinned by digest, `config.platform` in its `composer.json`, and its `php` requirement stays an open lower bound (it is `>=7.1.1` today; leave it).
While flat has no `v1.73.0` release, it requires flat from flat's `php-7.3` branch (a `git`
repository entry and `dev-php-7.3`, as the php-7.2 work did before `v1.72.0`); after the release
it requires `1.73.*`. Do this only after flat's environment is on 7.3.

Its own `AGENTS.md` carries the prime directives and its working rules. Its Docker setup is
development-only and must never be described as production-ready. Functional tests
against it now live in flat-webapp itself (the owner asked, 2026-11-21): `active/WEBAPP-FUNCTIONAL.md`. Its review and the consumer check are in
`docs/modernization/php-7.2/active/WEBAPP-REVIEW.md`.

## 5. Decisions the owner made (2026-11-20)

- **D1 (revised 2026-11-20):** the project's `php` requirement is the lower bound `>=7.3.0` on
  this branch; the exact patch is pinned only in the test platform. It is not `7.3.*`: consumers
  and hosts on PHP 7.4 or later must stay able to install flat. The version stays in major 1:
  Composer skips a release whose `php` requirement does not match and installs the newest one
  that does, so PHP 7.2 hosts keep `v1.72.x` (it requires `>=7.2.1`) and nothing breaks. Release
  number: **`v1.73.0`**. No runtime guard in the library. Known consequence: until a later phase
  is released, a host on 7.4 or later gets `v1.73.x`, which is tested on 7.3 only.
  (The first version of D1 said `7.3.*`; the owner changed it the same day.)
- **D2:** PHP 7.3 only; no 7.2 and 7.3 matrix ("force PHP 7.3 absolutely").
- **D3:** keep counting MOD, KD and Q numbers across phases.
- **D4:** modernize with 7.3 where appropriate, provided the public API, return values and
  behavior stay the same (section 3a). Warnings, notices and deprecations are always fixed,
  never hidden (prime directive 2).
- **D5:** both repositories get a `php-7.3` branch; the owner created them.
- **PHP patch version:** the newest 7.3 release, pinned by digest.
- **`master`:** the owner's alone (prime directive 1).

Anything not listed is not decided: record the question with a recommendation (next question
number Q37) and wait.

## 6. Tools you will want that already exist

- `docker/bin/test`: all tests (`unit`, `services`, `mongo`, `rabbitmq`, `all`, flags
  `--coverage`, `--offline`, `--filter`); `docker/bin/test inventory <KNOWN-DEFECTS.md>` writes
  the inventory (to a temporary file first, see the phase README).
- `docker/bin/mutate`: the mutation probe on a throwaway copy (`--services`, `--at=FILE:LINE`).
  Its record is `docs/modernization/php-7.2/active/MUTATION-PROBE.md`.
- `tests/tools/apidump.php`: dumps and compares the public API with Reflection. Use and results
  for 7.2 are in `docs/modernization/php-7.2/notes/API-COMPARISON.md`; dump the new version and
  compare it to `docs/modernization/php-7.2/notes/API-V1.72.0.json` (the 7.2 baseline).
- `bin/consumer-check`: searches an application for the terms of the consumer-impact list.
- `bin/check-docs` (and `docker/bin/check-docs`): run it after every documentation change.

## 7. Traps (things that cost time or caused mistakes)

Environment:
- A fresh container has no Docker daemon running: start it first, in the background, with its
  output in a log file. Builds need the sandbox's proxy CA: set `FLAT_TEST_CA_FILE` to the bundle
  the session documents and, for Compose builds, `BUILDX_BAKE_ENTITLEMENTS_FS=0`.
- RabbitMQ used to exit at startup now and then. The cause was a root-owned Erlang cookie created by the health
  check; the health check now runs as the `rabbitmq` user (MOD-272). `docker/bin/test` still retries a failed
  service start (three attempts) and prints the failed attempt's logs. If a run stops with "Starting the test
  services failed (attempt 3 of 3", read those logs; do not rerun by hand.
- Never pipe a long Docker build into `head`: the pipe closes and kills the build half way. Log to
  a file and read the file.
- The shell's safety check refuses `rm -rf` on a variable path. Do not work around it: use fresh
  `mktemp -d` directories in the scratchpad and delete nothing.
- Do not run PHP tests as root: file-permission tests cannot fail as root. The harness runs PHP as
  user 1000 when started as root; keep it that way.

Editing:
- Line endings are LF everywhere (`.gitattributes`, MOD-204). If `git ls-files --eol` shows a CRLF file,
  something went wrong: tell the owner.
- Shell pitfall: `grep -c pattern file && python ...` skips the Python when the count is 0. Use
  `;` or separate commands. A test that cannot fail proves nothing: when you add or change one,
  show it failing without the fix.
- Match the existing style: dense tests, behavior asserted not implementation, a
  `@group known-defect` tag only while a test still asserts defective behavior.
- The suite uses PHPUnit 8.5, which runs on PHP 7.3 too: keep it. Some assertion names differ in
  PHPUnit 9 (`assertRegExp` is the PHPUnit 8 name; 9 calls it `assertMatchesRegularExpression`),
  so moving PHPUnit is a separate decision, not part of this step. (Correction, 2026-11-23: it is part of
  the step; PHPUnit moved to 9.6, see `AGENTS.md`, "Keep everything current".)

GitHub and the session:
- This session's git access cannot push tags, delete branches, or read Dependabot alerts
  (HTTP 403). Do not try to work around it; give the owner the exact commands instead. Pushing
  branches works.
- `master` is never touched (prime directive 1). The `modernize-26` branch no longer exists
  (retired 2026-11-20); do not recreate it.
- Commit in coherent groups with their documentation, run `bin/check-docs`, and push to `php-7.3`
  regularly. Commit messages end with the attribution lines the system instructions give.
- Change-log entries are never rewritten; a correction is a new entry, and a moved file needs an
  old-to-new path map in it.

## 8. Known leftovers that are not part of this phase

Do not fix these as part of the PHP step; raise them with the owner if they matter.

- A correct 405 with an `Allow` header for an unsupported API method is deferred to v2.X
  (`docs/modernization/DEFERRED-V2.md`); in v1.X it answers 500.
- Seven classes cannot be loaded in either version, so they are untested and unusable:
  `flat\cloud\authorize\AuthorizeNetCIM`, `flat\core\util\validate\collection`, and the six
  MongoDB SSL file classes under `flat\db\driver\mongo\ssl\`. The list is in
  `docs/modernization/php-7.2/notes/API-COMPARISON.md`.
- Six other empty (0-byte) files that are not exceptions were deliberately left
  (`src/flat/cloud/disqus.php` and five under `src/flat/cloud/pci/`), per MOD-125 in the php-7.2
  change log.
- Further mutation-probe seeds (also with `--services`) can still be run; the first sample is
  closed.
- The consumer check against the owner's closed-source application, and integration tests with
  flat-webapp, belong to the owner's timing.

## 9. Definition of done for the phase

- The whole suite passes on PHP 7.3 (and only 7.3), with 100% class, method and line coverage, the
  inventory regenerated, and nothing weakened (a diff of the tests shows no loosened assertion).
- The exact PHP patch version and the pinned image digests are recorded.
- No error is hidden anywhere (prime directive 2): the search of section 3, step 5, is repeated and its
  result recorded: no `@`, no ignore list, and no `error_reporting` change except the one approved
  exception.
- The API comparison against `v1.72.0` is done and every difference has a verdict; modernizations
  changed nothing a caller can see.
- Every behavior change has a consumer-impact entry and a change-log entry; modernizations and
  compatibility fixes are recorded in their own categories.
- Dependency audit re-run on both locks; the note in the phase folder says, for each dismissed
  alert, whether it can be fixed now, and the owner has the list to reopen or resolve them.
- flat-webapp's `php-7.3` branch runs on PHP 7.3 against flat (section 4).
- Release notes are drafted for the owner; `bin/check-docs` is clean.
- Open questions are answered or recorded, and the owner has declared the phase complete.
