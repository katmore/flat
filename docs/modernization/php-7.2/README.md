# php-7.2 phase

The first modernization phase: flat as it stood in 2020, running on PHP 7.2,
with a full test suite and its known bugs fixed. The project plan, branch
model and the rules every phase follows are in [../README.md](../README.md).

## Status: complete (owner, 2026-11-20)

- **Tests:** 1,428 tests pass on PHP 7.2.34 with 100% class, method and line
  coverage. How to run them: [../TESTING.md](../TESTING.md).
- **Known defects:** 79 tracked, all resolved (fixed, already fixed, or
  confirmed as intended); none open.
  Record: [active/KNOWN-DEFECTS.md](active/KNOWN-DEFECTS.md).
- **Test-strength tasks:** TS-01 to TS-05 done (first mutation-probe sample:
  [active/MUTATION-PROBE.md](active/MUTATION-PROBE.md)); TS-06 done (the 54 survivor
  lines are pinned or shown equivalent).
- **flat-webapp:** repaired on its `php-7.2` branch and runnable with Docker
  (the `serve` script in that repository's docker folder); integration tests are on hold until the
  owner has run it: [active/WEBAPP-REVIEW.md](active/WEBAPP-REVIEW.md),
  [active/WEBAPP-TESTS.md](active/WEBAPP-TESTS.md).
- **Phase complete.** The owner declared it complete on 2026-11-20 and it is merged to
  `modernize-26`. This branch, `php-7.2`, is kept permanently as the record of the phase.

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
   the test, remove its `@group known-defect`, add an entry to
   [active/CONSUMER-IMPACT.md](active/CONSUMER-IMPACT.md), mark the entry
   fixed, and add an entry to [CHANGELOG.md](CHANGELOG.md).
5. Before touching an area, read its file in [notes/](notes/) for how it is
   tested and which quirks are deliberate.
6. After tests change, regenerate the test inventory:
   `tmp=$(mktemp) && docker/bin/test inventory docs/modernization/php-7.2/active/KNOWN-DEFECTS.md > "$tmp" && mv "$tmp" docs/modernization/php-7.2/TEST-INVENTORY.md`
   (writing to a temporary file first keeps the old inventory if the command fails).
7. Update the status section above when the numbers change.

## Next

1. This phase is closed. Do not change this branch except to fix documentation.
2. The next phase (php-7.3) starts only on the owner's explicit go-ahead. Rules for every
   phase are in [../README.md](../README.md).
3. Integration tests against flat-webapp wait for the owner's instructions
   ([active/WEBAPP-TESTS.md](active/WEBAPP-TESTS.md)).

## Contents

- [README.md](README.md): This page: status, how to resume, contents. Read it when: always first.
- [RELEASE-NOTES.md](RELEASE-NOTES.md): The notes for release `v2.72.0`: what it is, what to check before upgrading from 1.x. Read it when: publishing or reading about the release.
- [CHANGELOG.md](CHANGELOG.md): Every change in this phase (MOD-001 onward): what, why, behavior effect, verification, undo. Read it when: before changing code; to trace or revert a change.

[active/](active/): Work in progress; edited while the phase is open

- [active/KNOWN-DEFECTS.md](active/KNOWN-DEFECTS.md): Every known defect (KD) and test-strength task (TS), with category, fix and status; open owner questions at the bottom. Read it when: choosing what to fix; answering questions.
- [active/MUTATION-PROBE.md](active/MUTATION-PROBE.md): Record of the mutation probe: what it found, what is pinned by tests, what is equivalent, and what remains (TS-06). Read it when: before running the probe again; when extending tests.
- [active/WEBAPP-REVIEW.md](active/WEBAPP-REVIEW.md): What a trial run and read-through of flat-webapp found (it does not run as shipped). Read it when: before touching flat-webapp.
- [active/WEBAPP-TESTS.md](active/WEBAPP-TESTS.md): Plan for integration tests that run the flat-webapp application over HTTP. Read it when: reviewing or starting that work.
- [active/CONSUMER-IMPACT.md](active/CONSUMER-IMPACT.md): Behavior changes that applications using flat may notice, and what to search their code for. Read it when: before releasing; when checking a consumer application.
- [TEST-INVENTORY.md](TEST-INVENTORY.md): All tests by file, with data-provider, service-group and known-defect markers. Generated from PHPUnit by `docker/bin/test inventory`; do not edit by hand. Read it when: reviewing what the suite claims.

[notes/](notes/): How each area is tested, its deliberate quirks and decisions; lasting reference

- [notes/API-COMPARISON.md](notes/API-COMPARISON.md): The public API of `v1.5.5` against `v2.72.0`, compared by Reflection, every difference with a verdict; the two full dumps are `notes/API-V1.5.5.json` and `notes/API-V2.72.0.json`. Read it when: checking the "drop-in replacement" claim.
- [notes/COLLECTIONS-ROUTING-CLI.md](notes/COLLECTIONS-ROUTING-CLI.md): Collections, routing controller, CLI. Read it when: working on those areas.
- [notes/DATA-XML.md](notes/DATA-XML.md): Data objects, XML encoding/decoding (incl. the 0.3 format). Read it when: working on those areas.
- [notes/DATABASE-ASSETS-MINIFIER.md](notes/DATABASE-ASSETS-MINIFIER.md): PDO, Mongo, assets, JavaScript minifier. Read it when: working on those areas.
- [notes/DEBUG-TEMPLATES-TEXT.md](notes/DEBUG-TEMPLATES-TEXT.md): Debug output, templates, text encoding. Read it when: working on those areas.
- [notes/MAPPER.md](notes/MAPPER.md): The core mapper and its public-only contract. Read it when: working on those areas.
- [notes/PAYMENT-ALERT.md](notes/PAYMENT-ALERT.md): Authorize.Net wrappers, alerts. Read it when: working on those areas.
- [notes/SESSION-HTTP.md](notes/SESSION-HTTP.md): Sessions, nonces, cURL requests/responses. Read it when: working on those areas.
- [notes/UTILITY-EVENT.md](notes/UTILITY-EVENT.md): Utilities, validation, events. Read it when: working on those areas.

[history/](history/): Finished or replaced; kept for the record. Each file starts with a banner saying what replaced it

- [history/COVERAGE-BASELINE.md](history/COVERAGE-BASELINE.md): Coverage checkpoints while the tests were written. Read it when: researching how coverage was reached.
- [history/PENDING-QUESTIONS.md](history/PENDING-QUESTIONS.md): Earlier owner-question list. Read it when: not needed now (replaced by active/KNOWN-DEFECTS.md).
- [history/TESTING-FOLLOWUPS.md](history/TESTING-FOLLOWUPS.md): Earlier deferred findings. Read it when: not needed now (replaced by active/KNOWN-DEFECTS.md).
- [history/BRANCH-INVESTIGATION.md](history/BRANCH-INVESTIGATION.md): Branch situation before the move to `php-7.2`. Read it when: rarely.
- [history/AGENT-HANDOFF.md](history/AGENT-HANDOFF.md): Handoff between earlier agents. Read it when: rarely.
- [history/LEGACY-TESTING.md](history/LEGACY-TESTING.md): The old test setup outside the repository. Read it when: rarely.

## Conventions

Naming and layout follow the conventions for all modernization phases in
[../README.md](../README.md#conventions).
