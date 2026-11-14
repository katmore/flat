# Modernization Project

### flat is being modernized

Development on the flat framework largely halted in 2020. There are various issues including bugs that need to be addressed including updating the PHP version used. This is a bit of an undertaking.

This modernization is taking place in multiple deliberate steps to avoid consumer disruption.
The branch **modernize-26** contains the latest release phase of the modernized flat.

The phases are going to correspond with the major versions of PHP starting at PHP 7.2 up to present released version.
Each phase will have it's own branch, i.e. **php-7.2** for php-7.2 (the last PHP version flat was using when development stalled in 2020). Once the phases are caught up to the latest stable release of PHP, **modernize-26** branch will be merged with the **master** branch and the modernization project will be considered complete.

### Progress
 * php-7.2: in progress. Status and how to resume: [php-7.2/README.md](./php-7.2/README.md); changes: [CHANGELOG.md](./php-7.2/CHANGELOG.md)

### Rules for every phase
 * Advance one PHP minor version at a time and skip none: 7.2, 7.3, 7.4, 8.0, 8.1, and onward.
 * Fix the phase's known bugs before starting the next phase, so bug fixes and PHP upgrades never mix.
 * Run PHP, Composer, the tests and their services in Docker configured in this repository, and record the exact PHP patch and tool versions used.
 * Rerun the whole test suite (and the flat-webapp functional tests) for every phase; record failures, skips and coverage gaps.
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
