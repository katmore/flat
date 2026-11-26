# Change log, php-7.4 phase

Every change of this phase: what, why, effect on behavior, how it was verified, and how to undo it. Numbering
continues from the php-7.3 phase.

## MOD-280 — The php-7.4 phase folder

- Kind: documentation. `docs/modernization/php-7.4/` has the same skeleton as the earlier phases; the owner's
  brief for the phase is saved there as `HANDOFF.md`. The Progress list of `docs/modernization/README.md`
  marks php-7.3 complete and php-7.4 in progress. `AGENTS.md` points to this phase: its work plan, the PHP
  version (7.4) and the branch to push to. No rule changed.
- The owner created flat's `php-7.4` branch at `673df9f` (`master`, `v1.73.2`).
- Behavior: none.
- Verified: on `673df9f` before any change, `docker/bin/test all --coverage` passed 1,624 tests and 6,954
  assertions on PHP 7.3.33; `docker/bin/check-docs`.
- Undo: revert the commit.
