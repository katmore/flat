> Archived: completed phase material or a superseded work snapshot. Current status and work: [phase overview](../README.md). The record below describes its original checkpoint.

# Mutation probe, php-7.3 sample

One sample of the mutation probe on the 7.3 environment (HANDOFF step 7), to see whether the
move changed how well the suite catches planted bugs. Tool and earlier record:
[../../php-7.2/active/MUTATION-PROBE.md](../../php-7.2/history/MUTATION-PROBE.md).

## Result (2026-11-20)

- `docker/bin/mutate --limit=40 --seed=73 src/flat/core` (unit suite only): 1,501 candidate
  mutants, 40 run, none invalid. **32 caught, 8 survived: 80.0% caught.** The php-7.2 sample
  caught 73.4%, so nothing got worse; the probe does not depend on the PHP version.
- The 8 survivors are not triaged (not part of the PHP step; HANDOFF section 8 lists further
  mutation work as a leftover). They are, by file and line:
  - `src/flat/core/session.php`, lines 302 and 303: `0` to `1` in `ini_set("session.use_cookies", 0)` and
    `use_only_cookies`
  - `src/flat/core/cli.php`, line 154, `0` to `1` in the command-argument index
  - `src/flat/core/xml/encode.php`, line 254 (`1` to `0`) and `:297` (`'anyURI'=>true` to `false`)
  - `src/flat/core/controller/tmpl.php`, line 351, `&&` to `||` in the design-class check
  - `src/flat/core/util/image/dim.php`, line 79, `>` to `>=` (oversized width)
  - `src/flat/core/controller/route.php`, line 185, `false` to `true` ($check_next_for_resolver)
- None of these lines appears in the php-7.2 record, so they are new to it. Recommended next
  step, after the PHP step: triage them the way TS-06 did (pin by a test, or show equivalent).
