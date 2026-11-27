# php-7.4 phase

The third modernization phase: the step from PHP 7.3 to PHP 7.4. The project plan, branch model and the rules
every phase follows are in [../README.md](../README.md). The work plan and traps are in
[HANDOFF.md](HANDOFF.md).

## Status: in progress (started 2026-11-26)

- The test image is PHP 7.4.33 and the Composer platform pins are 7.4.33.
- `docker/bin/test all --coverage`: 1,637 tests, 7,209 assertions, all pass; 100% coverage; no PHP message anywhere
  (MOD-298). flat's test tools and dependencies are at their newest 7.4-compatible releases
  ([notes/ENVIRONMENT.md](notes/ENVIRONMENT.md)).
- Next: the end-of-phase work (see [active/SESSION-HANDOFF.md](active/SESSION-HANDOFF.md)).
- No open owner questions: Q51 to Q54 are answered ([active/KNOWN-DEFECTS.md](active/KNOWN-DEFECTS.md)).
- flat-webapp runs on PHP 7.4.33 (images and platform pins moved; 67 tests pass). Its application lock still
  holds flat `v1.73.2` and the packages that go with it.

## Resume here

1. Read `AGENTS.md`, the phase rules in [../README.md](../README.md), [HANDOFF.md](HANDOFF.md), then
   [active/SESSION-HANDOFF.md](active/SESSION-HANDOFF.md).
2. Run `docker/bin/test` and compare it with the status above before changing anything.
3. After tests change, regenerate the inventory:
   `tmp=$(mktemp) && docker/bin/test inventory docs/modernization/php-7.4/active/KNOWN-DEFECTS.md > "$tmp" && chmod 644 "$tmp" && mv "$tmp" docs/modernization/php-7.4/TEST-INVENTORY.md`
4. Run `docker/bin/check-docs` after every documentation change and read its whole output.

## Contents

- [HANDOFF.md](HANDOFF.md): The work plan: rules, steps, what 7.4 unlocks, decisions, traps.
- [CHANGELOG.md](CHANGELOG.md): Every change in this phase (MOD-280 onward).
- [TEST-INVENTORY.md](TEST-INVENTORY.md): All tests by file. Generated; do not edit by hand.

[active/](active/): Work in progress

- [active/SESSION-HANDOFF.md](active/SESSION-HANDOFF.md): Where the last session stopped and what is next.
- [active/KNOWN-DEFECTS.md](active/KNOWN-DEFECTS.md): Defects found in this phase and the owner questions.
- [active/CONSUMER-IMPACT.md](active/CONSUMER-IMPACT.md): Behavior changes applications may notice.

[notes/](notes/): Lasting reference

- [notes/ENVIRONMENT.md](notes/ENVIRONMENT.md): The test environment on 7.4: what is newest and why the rest is held.
- [notes/VENDORED.md](notes/VENDORED.md): The corrected copy of the Mongo adapter's read-preference trait.

[history/](history/): Finished or replaced documents (none yet; `.gitkeep` keeps the folder in Git)
