# Current work, php-7.4

Work and push only on php-7.4. Phase rules: [AGENTS.md](../../../../AGENTS.md),
[project plan](../../README.md), [phase overview](../README.md).

## Next actions

1. Revisit the [eight older mutation survivors](../../php-7.3/history/MUTATION-PROBE.md)
   and triage them as test gaps or equivalent changes.
2. Complete [release closeout](../notes/EXIT-REVIEW.md), including the published
   patch and flat-webapp lock update on the owner's instruction. Draft:
   [v1.74.1 release notes](../RELEASE-NOTES-V1.74.1.md).
3. The private consumer scan and live Authorize.Net approval/decline checks need the
   owner's environment and timing. The ordinary suite uses the documented mock.
4. Start PHP 8.0 only after the owner declares this phase complete and approves
   the step. Do not change master or publish a release without their instruction.

## Validation

Run `docker/bin/test` for framework checks and `docker/bin/check-docs` after
editing documentation. The current baseline and test commands are in the phase
overview. Completed reviews, old handoffs and resolved defects are in
[history](../history/); lasting tool/environment information is in [notes](../notes/).
