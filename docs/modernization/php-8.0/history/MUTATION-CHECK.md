> Archived: completed PHP 8.0 mutation sample. Current work: [handoff](../active/SESSION-HANDOFF.md).

# CLI mutation check

The seed-80 sample of src/flat/core/cli.php ran eight valid mutants: seven killed,
one survivor, no invalid mutants or timeouts. The survivor changed the strict
in_array flag in line's error-option check to false.

An unrelated option with value true must not set the error flag. The existing
line-handler test now asserts that case. Rerunning all three mutants at that
location kills all three, with no invalid mutants, timeouts or source changes
left behind. MOD-354 records the test change.

The eight older PHP 7.3 survivors remain a separate triage follow-up in the
current handoff; this small sample does not claim exhaustive mutation coverage.
