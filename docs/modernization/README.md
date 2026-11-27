# Modernization Project

### flat is being modernized

Development on the flat framework largely halted in 2020. There are various issues including bugs that need to be addressed including updating the PHP version used. This is a bit of an undertaking.

This modernization is taking place in multiple deliberate steps to avoid consumer disruption.

The phases are going to correspond with the major versions of PHP starting at PHP 7.2 up to present released version.
Each phase has its own branch, i.e. **php-7.2** for php-7.2 (the last PHP version flat was using when development stalled in 2020). **master** carries the latest completed phase; the maintainer merges a phase into it when the phase is done.

### flat-webapp branches
The sample application `katmore/flat-webapp` follows the same pattern: a **php-7.2** branch (created from its `master`, commit `7186607`), later phases in their own branches, and each finished phase merged to its `master` by the maintainer. Its `devel` branch is being retired. Integration tests against it: [php-7.2/active/WEBAPP-TESTS.md](./php-7.2/active/WEBAPP-TESTS.md).

### Releases
Each finished phase is published as a GitHub Release whose tag is `v1.<PHP major><PHP minor>.0`: `v1.72.0` for PHP 7.2, then `v1.73.0`, `v1.74.0`, `v1.80.0`, and so on. The release notes for a phase are in its folder, for example [php-7.2/RELEASE-NOTES.md](./php-7.2/RELEASE-NOTES.md). Publishing a release needs the maintainer's explicit go-ahead.

The major version follows semantic versioning. It stays at 1 while the public API is compatible (the bug fixes of the modernization are not API changes). It goes to 2, for example `v2.73.0` for PHP 7.3, only when a phase makes a change that breaks existing callers, and only on the maintainer's decision. The minor number keeps naming the PHP version.

### Branches during and after the project
- **`master`:** the latest completed phase. Only the maintainer changes it, and only that way: an agent may change it only on the maintainer's explicit one-time instruction (prime directive 1 in `AGENTS.md`).
- **`php-7.N`:** the work branch of one phase. When the phase is complete the maintainer merges it to `master` and tags the release; the branch is kept permanently as the record of the phase. Agents commit and push to the current phase branch only.
- **Tags and Releases:** `v1.<PHP major><PHP minor>.0`, plus patch releases (`v1.72.1`). Consumers pin these, not branches.
- When the maintainer declares the whole project complete, the phase branches may be deleted: each finished phase's Release tag is the permanent record, so make sure every phase has its Release first. Only the maintainer decides this.

### Progress
 * php-7.2: complete (2026-11-20), released as `v1.72.0`, `v1.72.1` and `v1.72.2`, and merged to `master`; the `php-7.2` branch is kept permanently. Status: [php-7.2/README.md](./php-7.2/README.md); changes: [CHANGELOG.md](./php-7.2/CHANGELOG.md)
 * php-7.3: complete, released as `v1.73.0`, `v1.73.1` and `v1.73.2`, and merged to `master`; the `php-7.3` branch is kept permanently. Status: [php-7.3/README.md](./php-7.3/README.md); changes: [CHANGELOG.md](./php-7.3/CHANGELOG.md)
 * php-7.4: in progress. Status: [php-7.4/README.md](./php-7.4/README.md); changes: [CHANGELOG.md](./php-7.4/CHANGELOG.md)

### Rules for every phase
 * Advance one PHP minor version at a time and skip none: 7.2, 7.3, 7.4, 8.0, 8.1, and onward.
 * Fix the phase's known bugs before starting the next phase, so bug fixes and PHP upgrades never mix.
 * Run PHP, Composer, the tests and their services in Docker configured in this repository, and record the exact PHP patch and tool versions used.
 * Bring every tool, service and dependency of the test environment up to the newest release the phase's PHP supports, aggressively and one at a time (full suite, own commit and change-log entry each). The known consumer's versions never hold them back; the public API stays compatible through workarounds, not by pinning old tools. Details: `AGENTS.md`, "Keep everything current".
 * Rerun the whole test suite (and the integration tests, once the owner has asked for them) for every phase; record failures, skips and coverage gaps.
 * Never weaken an assertion or drop a check to make an upgrade pass. Changing tested behavior is a deliberate maintainer decision.
 * Keep runtime-compatibility fixes separate from behavior changes in each phase's CHANGELOG.md and in the commits, and list consumer-visible changes in the phase's `active/CONSUMER-IMPACT.md`.
 * At the end of every phase, re-run `composer audit --locked` on both test locks and revisit the dismissed Dependabot alerts: record which can be fixed on the new PHP, and give the maintainer the list to reopen or resolve.
 * The maintainer decides when a phase is complete. Creating or merging release branches and moving to the next PHP version need the maintainer's explicit go-ahead.

### Conventions
 * Each phase has a folder named after its branch (`php-7.2/`, `php-7.3/`, ...) with the same skeleton: `README.md` (status, how to resume, contents), `CHANGELOG.md`, `TEST-INVENTORY.md`, and the folders `active/` (current work), `notes/` (lasting reference per area) and `history/` (finished or replaced).
 * Nothing is deleted. Superseded documents move to `history/` and start with a banner saying what replaced them.
 * File names are UPPERCASE words joined by hyphens, with the `.md` extension: `KNOWN-DEFECTS.md`.
 * File names have at most three words, preferably two. The folder says what kind of document it is; the file name says what it is about.
 * Folder names are lowercase.
 * `TEST-INVENTORY.md` is generated, never edited by hand: `docker/bin/test inventory <phase>/active/KNOWN-DEFECTS.md > <phase>/TEST-INVENTORY.md`.
 * Markdown is written to read well both rendered and as plain text: headings, short paragraphs and lists; tables only for short, regular data (a few short cells per row), never for sentences.
 * How to run the tests, for every phase: [TESTING.md](./TESTING.md).
 * Changes wanted but held back for the `v2.X` releases: [DEFERRED-V2.md](./DEFERRED-V2.md).
