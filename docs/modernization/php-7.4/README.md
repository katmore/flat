# php-7.4 phase

The third modernization phase: the step from PHP 7.3 to PHP 7.4. The project plan, branch model and the rules
every phase follows are in [../README.md](../README.md). The work plan and traps are in
[HANDOFF.md](HANDOFF.md).

## Status: in progress (started 2026-11-26)

- Baseline: 1,624 tests pass on PHP 7.3.33 at the starting commit, `673df9f`. The test environment is
  still PHP 7.3.
- Open owner questions: Q51 and Q52 in [active/KNOWN-DEFECTS.md](active/KNOWN-DEFECTS.md).
- flat-webapp has no `php-7.4` branch yet; its part of the phase waits for it.

## Resume here

1. Read `AGENTS.md`, the phase rules in [../README.md](../README.md), then [HANDOFF.md](HANDOFF.md).
2. Run `docker/bin/test` and confirm it passes before changing anything.
3. After tests change, regenerate the inventory:
   `tmp=$(mktemp) && docker/bin/test inventory docs/modernization/php-7.4/active/KNOWN-DEFECTS.md > "$tmp" && chmod 644 "$tmp" && mv "$tmp" docs/modernization/php-7.4/TEST-INVENTORY.md`
4. Run `docker/bin/check-docs` after every documentation change and read its whole output.

## Contents

- [HANDOFF.md](HANDOFF.md): The work plan: rules, steps, what 7.4 unlocks, decisions, traps.
- [CHANGELOG.md](CHANGELOG.md): Every change in this phase (MOD-280 onward).
- [TEST-INVENTORY.md](TEST-INVENTORY.md): All tests by file. Generated; do not edit by hand.

[active/](active/): Work in progress

- [active/KNOWN-DEFECTS.md](active/KNOWN-DEFECTS.md): Defects found in this phase and the owner questions.
- [active/CONSUMER-IMPACT.md](active/CONSUMER-IMPACT.md): Behavior changes applications may notice.

[notes/](notes/): Lasting reference (nothing yet; `.gitkeep` keeps the folder in Git)

[history/](history/): Finished or replaced documents (none yet; `.gitkeep` keeps the folder in Git)
