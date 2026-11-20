# Repository working requirements

## Start here

The framework is being modernized one PHP version at a time. Read
`docs/modernization/README.md` (plan and phase rules), then the current phase's
`README.md` (currently `docs/modernization/php-7.2/README.md`) for status and
how to resume work.

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
- `bin/test` holds the test logic and runs natively where PHP 7.2 and PHPUnit are
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
- Preserve PHP 7.2.34 until the maintainer explicitly approves a version step.
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
- Commit and push to the current phase branch (now `php-7.2`). Leave
  `modernize-26` and `master` to the maintainer, and follow the maintainer's
  explicit branch and push instructions.
