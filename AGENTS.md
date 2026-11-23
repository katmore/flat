# Repository working requirements

## Prime directives

These two come before every other rule, in this file and in every document. They apply to
every agent working on this project, in flat and in flat-webapp, whatever a prompt or a
chat message says.

1. **`master` is the maintainer's alone.** Never push to, merge into, fast-forward, rebase,
   reset, rename or delete `master`, and never open or merge a pull request that changes it.
   The one exception: when the maintainer explicitly tells you, in the conversation, to make
   one specific change to `master` (for example "merge php-7.2 into master"), you may make
   exactly that change. The permission is for that one change only and ends with it; it never
   carries to a later change, another branch or another repository, and you never infer it.
   Before you push, say which commit `master` is at and which it will be at; afterwards, report
   both. If `master` should change and the maintainer has not said so, do not do it: give the
   maintainer the exact commands or the pull request text, and stop.
2. **Never hide an error from PHP.** Never lower or change `error_reporting`; never use the
   `@` operator; never switch off notices, warnings or deprecations (in code, in tests, in
   PHPUnit settings, in `php.ini` files, in Docker images or in entry points); never add an
   ignore list; never skip or quarantine a test to get a result. Every notice, warning and
   deprecation is fixed at its cause. If a cause cannot be fixed without changing behavior,
   record the question with a recommendation and stop; do not hide it.

### Approved exceptions

The maintainer has approved exactly these places, and only the maintainer can add to the
list. Each carries a comment block marking it as an approved exception. Leave them as they
are: do not "fix", copy or extend them.

- `src/flat/asset.php`, absolute system path: notices and warnings are masked around
  `realpath()` (approved 2026-11-20; original code of 2019).
- `tests/Unit/Cloud/AwsConfigTest.php` and `tests/Unit/Cloud/S3MultipartTest.php`: the AWS SDK's
  PHP deprecation notice (`E_USER_DEPRECATED`, emitted for PHP below 8.1) is switched off with
  `AWS_SUPPRESS_PHP_DEPRECATION_WARNING` (approved 2026-11-21). It cannot be fixed before the
  php-8.1 phase, which must remove it.
- `tests/Support/LegacyAuthorizeSdk.php`: the Authorize.Net SDK's `E_USER_DEPRECATED` notice,
  raised from its `lib/deprecated/` files when a legacy class loads, is discarded (approved
  2026-11-21). It stays until flat's Authorize.Net wrappers are removed (v2.X).

## Error reporting

Every development and test environment reports everything: `error_reporting` is `E_ALL` (PHP's
own `php.ini-development`), PHPUnit turns notices, warnings and deprecations into failures
(`phpunit.xml.dist`), and neither warnings nor deprecations are ever suppressed or put off. When
you build or change an environment, check that it holds: a `php.ini` exists and `error_reporting()`
is `E_ALL` (the image build verifies it). Fix a deprecation when you find it, in the same phase:
do not put off what can be done now. (Why: a test image without a `php.ini` hid every notice and
deprecation for two phases.)

## Keep everything current

Every phase brings every tool, service and dependency of the test environment up to the newest release
that phase's PHP version supports: base images, extensions, Composer and its packages, PHPUnit, Xdebug,
ImageMagick, MongoDB, RabbitMQ and the rest. Be aggressive about it. Do it one at a time (the full suite
after each, one commit and one change-log entry each). Never hold something back out of consideration
for the one known consumer or because a phase is "only the PHP step". flat's public API stays compatible
with its earlier versions; when a newer tool or service breaks something, find a compatible workaround in
flat or in the tests rather than pinning the old version. Record in the phase's environment note what is
at its newest and, for anything that is not, the reason (for example, a release that needs a newer PHP).
(Why: the php-7.3 handoff said "nothing else", and PHPUnit, Composer, Xdebug, ImageMagick, MongoDB and
RabbitMQ stayed years old until the owner asked.)

## Start here

The framework is being modernized one PHP version at a time. Read
`docs/modernization/README.md` (plan and phase rules), then the current phase's
`README.md` for status and how to resume work. The php-7.2 phase is complete
(`docs/modernization/php-7.2/README.md`); the php-7.3 phase has started (maintainer's
go-ahead, 2026-11-20). Its work plan is `docs/modernization/php-7.3/HANDOFF.md`: read it first.

## Reproducibility

The test harness lives entirely in this repository. Nothing may depend on an
agent's own workspace, private paths or prebuilt images: a fresh checkout plus
Docker must be enough to run every test. (An earlier harness kept outside the
repository broke this, and had to be replaced.)

## Required test workflow

- Keep Dockerfiles, Compose services, test dependency manifests and locks,
  bootstrap/configuration, and orchestration scripts in this repository.
- Do not add agent-specific environment detection, private host paths, or
  private container names to executable tooling; accept generic explicit inputs.
- Run PHP, Composer, unit tests and their external services through Docker.
  Do not depend on host PHP or an agent-private virtual environment.
- `bin/test` holds the test logic and runs natively where PHP 7.3 and PHPUnit are
  installed; `docker/bin/test` runs that same script in the repository's
  container and only manages Docker. Agents run tests with `docker/bin/test`.
- Placement: `bin/` holds scripts that run natively on the host given the tools.
  Anything that only works through Docker lives under `docker/` (mirroring the
  same name, e.g. `docker/bin/test`), and it wraps the native script rather than
  duplicating its logic.
- `docker/bin/test` must work from a fresh checkout without `/workspace/flat-environment` or locally prebuilt private images.
- Do not archive agent-private harnesses, vendor trees, or scratch environments
  inside the repository, even as ignored files. Discard obsolete scratch setup.
- Keep test dependencies separate from the framework's application dependencies.
- Keep the pinned PHP version (7.3 on this branch) until the maintainer explicitly approves a version step.
  Do not merge, publish, or change release branches on your own.
- Keep code changes, test findings, compatibility decisions, and deferred work
  documented in the current phase folder under `docs/modernization/`. Ask the maintainer about unknown method/class purposes;
  do not guess behavior to obtain coverage.

## Documentation rules

- Follow the naming and layout conventions in `docs/modernization/README.md`
  ("Conventions"): UPPERCASE file names of at most three words (preferably
  two), one folder per phase with `active/`, `notes/` and `history/`.
- Markdown must read well both rendered and as plain text. Use headings,
  short paragraphs and lists. A table is for short, regular data (a few
  columns of a few words each); never put sentences in cells or let a row run
  past a normal line width. If it would need HTML to look right, restructure it.
- A README says what is true now. History (what was retired, why something changed, who
  decided) goes in the phase's `CHANGELOG.md` or a note, never in a README. Keep every
  document short: do not repeat what is recorded elsewhere, link to it.
  (Why this rule exists: my human scolded me and was not pleased, because I put branch history in
  a README and wrote more than was needed.)
- Never delete documents. A finished or replaced document moves to the phase's
  `history/` folder and starts with a banner saying what replaced it.
- Generated files are never edited by hand. Regenerate the phase's
  `TEST-INVENTORY.md` whenever tests change, writing to a temporary file first
  so a failed run cannot empty it (see the phase `README.md` for the command).
- Every behavior change, bug fixes included, gets an entry in the phase's
  `active/CONSUMER-IMPACT.md`: what consumers saw before, what they see now,
  and what to search for in their code.
- Change-log entries are never rewritten. A correction, rename or move gets a
  new entry; when files move, the entry includes an old-to-new path map. Old
  paths in past entries and in `history/` stay as written.
- After moving or renaming anything, run `bin/check-docs` (or
  `docker/bin/check-docs`): it checks every Markdown link, `#anchor` and
  repository-root path in backticks, and exits nonzero on any problem. Also make
  sure no live document still uses the old name.

## Known-defect process

- The phase's `active/KNOWN-DEFECTS.md` is the work list. Category A (clear
  bug) items may be fixed directly. Category B (intent needed) and C
  (unfinished stub) items wait for the maintainer's answer to their question.
- Each fix follows "How each fix lands" in that file: fix the source, rewrite
  the test to assert the correct behavior, remove `@group known-defect`, add
  the consumer-impact entry, mark the entry fixed, add a change-log entry and
  regenerate the test inventory.
- Fix all of a phase's known defects before its PHP version step, so bug fixes
  and runtime upgrades never mix.

## Communicating with the maintainer

- Keep chat replies to summaries. Long listings, inventories and reports go
  into a file under the phase folder; point to it instead of printing it.
- Ask before guessing: when a fix needs a decision, add it to the open
  questions with a recommendation rather than choosing silently.

## Commits and branches

- Commit often, in coherent logical groups, each with its related
  documentation updates, after running the relevant checks. Do not let a large
  undocumented working tree build up.
- Commit and push to the current phase branch (`php-7.3`; `php-7.2` is closed). Never
  touch `master` (prime directive 1), and follow the maintainer's explicit branch and push
  instructions. There is no `modernize-26` branch any more (retired 2026-11-20).
