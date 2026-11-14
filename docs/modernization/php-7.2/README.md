# php-7.2 phase

The first modernization phase: flat as it stood in 2020, running on PHP 7.2,
with a full test suite and its known bugs fixed. The project plan, branch
model and the rules every phase follows are in [../README.md](../README.md).

## Status (2026-11-14)

- **Tests:** 1,405 tests pass on PHP 7.2.34 with 100% class, method and line
  coverage. How to run them: [../TESTING.md](../TESTING.md).
- **Known defects:** 76 tracked, 75 resolved (fixed, already fixed, or
  confirmed as intended), 1 open: KD-76 waits for the owner's answer to Q27.
  Record: [active/KNOWN-DEFECTS.md](active/KNOWN-DEFECTS.md).
- **Test-strength tasks:** TS-01 to TS-05 done (first mutation-probe sample:
  [active/MUTATION-PROBE.md](active/MUTATION-PROBE.md)); TS-06 done (the 54 survivor
  lines are pinned or shown equivalent).
- **Not started:** functional tests against `flat-webapp`.
- **Not done until** the owner declares the phase complete.

## Resume here

For a person or an agent picking this up:

1. Read the phase rules in [../README.md](../README.md) and the repository
   rules in [`AGENTS.md`](../../../AGENTS.md).
2. Run the tests (`docker/bin/test`, see [../TESTING.md](../TESTING.md)) and
   confirm they pass before changing anything.
3. Pick open work from [active/KNOWN-DEFECTS.md](active/KNOWN-DEFECTS.md).
   Category A items can be fixed directly; B and C items follow the owner's
   answer to their question (all answered so far). `docker/bin/test unit --group known-defect` lists the
   tests that still assert defective behavior.
4. For each fix, follow "How each fix lands" in
   [active/KNOWN-DEFECTS.md](active/KNOWN-DEFECTS.md): fix the source, rewrite
   the test, remove its `@group known-defect`, add a row to
   [active/CONSUMER-IMPACT.md](active/CONSUMER-IMPACT.md), mark the entry
   fixed, and add an entry to [CHANGELOG.md](CHANGELOG.md).
5. Before touching an area, read its file in [notes/](notes/) for how it is
   tested and which quirks are deliberate.
6. After tests change, regenerate the test inventory:
   `tmp=$(mktemp) && docker/bin/test inventory docs/modernization/php-7.2/active/KNOWN-DEFECTS.md > "$tmp" && mv "$tmp" docs/modernization/php-7.2/TEST-INVENTORY.md`
   (writing to a temporary file first keeps the old inventory if the command fails).
7. Update the status section above when the numbers change.

## Next

1. Answer Q27; optionally run further probe seeds (also with `--services`).
2. Write functional tests with `flat-webapp`.
3. Owner decides the phase is complete; then it moves to `modernize-26`.

## Contents

| Path | What it is | Read it when |
| --- | --- | --- |
| [README.md](README.md) | This page: status, how to resume, contents | Always first |
| [CHANGELOG.md](CHANGELOG.md) | Every change in this phase (MOD-001 onward): what, why, behavior effect, verification, undo | Before changing code; to trace or revert a change |
| **[active/](active/)** | **Work in progress; edited while the phase is open** | |
| [active/KNOWN-DEFECTS.md](active/KNOWN-DEFECTS.md) | Every known defect (KD) and test-strength task (TS), with category, fix and status; open owner questions at the bottom | Choosing what to fix; answering questions |
| [active/MUTATION-PROBE.md](active/MUTATION-PROBE.md) | Record of the mutation probe: what it found, what is pinned by tests, what is equivalent, and what remains (TS-06) | Before running the probe again; when extending tests |
| [active/CONSUMER-IMPACT.md](active/CONSUMER-IMPACT.md) | Behavior changes that applications using flat may notice, and what to search their code for | Before releasing; when checking a consumer application |
| [TEST-INVENTORY.md](TEST-INVENTORY.md) | All tests by file, with data-provider, service-group and known-defect markers. Generated from PHPUnit by `docker/bin/test inventory`; do not edit by hand | Reviewing what the suite claims |
| **[notes/](notes/)** | **How each area is tested, its deliberate quirks and decisions; lasting reference** | |
| [notes/COLLECTIONS-ROUTING-CLI.md](notes/COLLECTIONS-ROUTING-CLI.md) | Collections, routing controller, CLI | Working on those areas |
| [notes/DATA-XML.md](notes/DATA-XML.md) | Data objects, XML encoding/decoding (incl. the 0.3 format) | " |
| [notes/DATABASE-ASSETS-MINIFIER.md](notes/DATABASE-ASSETS-MINIFIER.md) | PDO, Mongo, assets, JavaScript minifier | " |
| [notes/DEBUG-TEMPLATES-TEXT.md](notes/DEBUG-TEMPLATES-TEXT.md) | Debug output, templates, text encoding | " |
| [notes/MAPPER.md](notes/MAPPER.md) | The core mapper and its public-only contract | " |
| [notes/PAYMENT-ALERT.md](notes/PAYMENT-ALERT.md) | Authorize.Net wrappers, alerts | " |
| [notes/SESSION-HTTP.md](notes/SESSION-HTTP.md) | Sessions, nonces, cURL requests/responses | " |
| [notes/UTILITY-EVENT.md](notes/UTILITY-EVENT.md) | Utilities, validation, events | " |
| **[history/](history/)** | **Finished or replaced; kept for the record. Each file starts with a banner saying what replaced it** | |
| [history/COVERAGE-BASELINE.md](history/COVERAGE-BASELINE.md) | Coverage checkpoints while the tests were written | Researching how coverage was reached |
| [history/PENDING-QUESTIONS.md](history/PENDING-QUESTIONS.md) | Earlier owner-question list | Replaced by active/KNOWN-DEFECTS.md |
| [history/TESTING-FOLLOWUPS.md](history/TESTING-FOLLOWUPS.md) | Earlier deferred findings | Replaced by active/KNOWN-DEFECTS.md |
| [history/BRANCH-INVESTIGATION.md](history/BRANCH-INVESTIGATION.md) | Branch situation before the move to `php-7.2` | Rarely |
| [history/AGENT-HANDOFF.md](history/AGENT-HANDOFF.md) | Handoff between earlier agents | Rarely |
| [history/LEGACY-TESTING.md](history/LEGACY-TESTING.md) | The old test setup outside the repository | Rarely |

## Conventions

Naming and layout follow the conventions for all modernization phases in
[../README.md](../README.md#conventions).
