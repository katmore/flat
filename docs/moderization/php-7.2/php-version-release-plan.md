# PHP version development and release plan

Current owner decision: all current work is isolated on `php-7.2` and will be
committed/pushed there only. `modernize-26` is left unchanged for the maintainer's
own housekeeping. Master is not modified. The future version-step principles
below remain the release plan; no PHP upgrade is authorized by the branch move.


## Purpose

Modernize Flat while preserving compatibility with applications that already
depend on it. Each completed PHP version baseline must remain available on a
dedicated branch so consumers can select the version appropriate to their
application and regressions can be traced.

## Development baseline

- Continue development on `modernize-26`.
- Begin with PHP 7.2.34, the legacy baseline. Do not raise the PHP version while
  establishing the framework's unit tests and functional tests with `flat-webapp`.
- Run PHP, Composer, PHPUnit, and required services inside Docker containers
  configured in the repository. Do not install development tooling on the host.
- Characterization tests preserve existing behavior, including explicitly
  documented quirks. Changes to that behavior require a deliberate maintainer
  decision; passing coverage percentages alone do not establish completeness.
- Maintain a running change log in `docs/modernization-26.md`, recording what
  changed, why, compatibility effects, validation, and suggested commit messages.

## Release cycle

For each PHP minor version:

1. Develop and validate the current target on `modernize-26`, including framework
   unit tests and functional tests with `flat-webapp`.
2. Wait for the maintainer to decide that this version's work is complete.
3. Create a branch named `php-ver-X.X` from that completed state of
   `modernize-26`, replacing `X.X` with the PHP major and minor version. For the
   initial baseline, the branch is `php-ver-7.2`.
4. Merge that verified release branch into `master`.
5. Return to `modernize-26` and begin work on the next PHP minor version.
6. Repeat the same validation and completion gate before the next release.

Advance one minor version at a time: PHP 7.2, 7.3, 7.4, 8.0, 8.1, and onward.
Do not skip intermediate versions. The `php-ver-X.X` branches are completed
baselines, not parallel development branches for each upgrade.

## Compatibility and validation

- Each release branch retains Docker configuration for its matching runtime;
  record the exact PHP patch version and tool versions used for validation.
- Re-run the established unit and functional suites for each upgrade. Record
  failures, skips, coverage gaps, and remaining limitations explicitly.
- Keep runtime compatibility fixes distinguishable from deliberate behavior
  changes in the change log and commit history.
- Do not silently change assertions to accommodate a regression or remove a
  compatibility check merely to make an upgrade pass.
- Preserve previous release branches as reference points for consumers and
  regression investigation. Changes to a completed baseline require a separate
  maintainer decision.

## Maintainer control

The maintainer decides when coverage and functional validation are sufficiently
complete. Creating release branches, merging into `master`, pushing changes, and
advancing the PHP target require explicit maintainer instruction. Documenting
this plan does not authorize those actions.
