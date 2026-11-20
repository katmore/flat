# Modernization Project

### flat is being modernized

Development on the flat framework largely halted in 2020. There are various issues including bugs that need to be addressed including updating the PHP version used. This is a bit of an undertaking.

This modernization is taking place in multiple deliberate steps to avoid consumer disruption.
The branch **modernize-26** contains the latest release phase of the modernized flat.

The phases are going to correspond with the major versions of PHP starting at PHP 7.2 up to present released version.
Each phase will have it's own branch, i.e. **php-7.2** for php-7.2 (the last PHP version flat was using when development stalled in 2020). Once the phases are caught up to the latest stable release of PHP, **modernize-26** branch will be merged with the **master** branch and the modernization project will be considered complete.

### flat-webapp branches
The sample application `katmore/flat-webapp` follows the same pattern: a **php-7.2** branch (created from its `master`, commit `7186607`), later phases in their own branches, and each finished phase merged to its **modernize-26** branch. Its `devel` branch is being retired. Integration tests against it: [php-7.2/active/WEBAPP-TESTS.md](./php-7.2/active/WEBAPP-TESTS.md).

### Releases
Each finished phase is published as a GitHub Release whose tag is `v1.<PHP major><PHP minor>.0`: `v1.72.0` for PHP 7.2, then `v1.73.0`, `v1.74.0`, `v1.80.0`, and so on. The release notes for a phase are in its folder, for example [php-7.2/RELEASE-NOTES.md](./php-7.2/RELEASE-NOTES.md). Publishing a release needs the maintainer's explicit go-ahead.

The major version follows semantic versioning. It stays at 1 while the public API is compatible (the bug fixes of the modernization are not API changes). It goes to 2, for example `v2.73.0` for PHP 7.3, only when a phase makes a change that breaks existing callers, and only on the maintainer's decision. The minor number keeps naming the PHP version.

### Branches during and after the project
While the modernization runs, every phase keeps its own branch (`php-7.2`, `php-7.3`, ...) as described above, merged to `modernize-26` when the phase is complete. Do not delete a phase branch before the project is finished. When the maintainer declares the whole project complete, the phase branches may be deleted: each finished phase's Release tag is the permanent record, so make sure every phase has its Release first. Only the maintainer decides this.

### Progress
 * php-7.2: complete (2026-11-20) and merged to `modernize-26`; the `php-7.2` branch is kept permanently. Status: [php-7.2/README.md](./php-7.2/README.md); changes: [CHANGELOG.md](./php-7.2/CHANGELOG.md)
 * php-7.3: not started; begins only on the maintainer's explicit go-ahead

### Rules for every phase
 * Advance one PHP minor version at a time and skip none: 7.2, 7.3, 7.4, 8.0, 8.1, and onward.
 * Fix the phase's known bugs before starting the next phase, so bug fixes and PHP upgrades never mix.
 * Run PHP, Composer, the tests and their services in Docker configured in this repository, and record the exact PHP patch and tool versions used.
 * Rerun the whole test suite (and the integration tests, once the owner has asked for them) for every phase; record failures, skips and coverage gaps.
 * Never weaken an assertion or drop a check to make an upgrade pass. Changing tested behavior is a deliberate maintainer decision.
 * Keep runtime-compatibility fixes separate from behavior changes in each phase's CHANGELOG.md and in the commits, and list consumer-visible changes in the phase's `active/CONSUMER-IMPACT.md`.
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
