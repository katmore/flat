# php-7.4 phase

## Status: in progress

- PHP 7.4.33; the Docker suite passed 1,703 tests and 7,470 assertions with
  100% class, method and line coverage. Environment: [notes](notes/ENVIRONMENT.md).
- All eleven adversarial-review defects are fixed. The [review](history/ADVERSARIAL-REVIEW.md)
  and [resolved registry](history/KNOWN-DEFECTS.md) are archived.
- **KD-128 remains open:** the mutation runner. Current work is in the
  [defect list](active/KNOWN-DEFECTS.md) and [handoff](active/SESSION-HANDOFF.md).
- Published release: v1.74.0. The v1.74.1 patch is pending; its notes are a draft.
  flat-webapp still locks v1.74.0; its tests also passed against the patched source.
- No open owner questions. Publishing releases, changing master and starting
  PHP 8.0 require the owner's instruction.

## Resume

Read [AGENTS.md](../../../AGENTS.md), the [project rules](../README.md), the
[phase plan](HANDOFF.md), then the current handoff. Run `docker/bin/test` for
framework checks and `docker/bin/check-docs` after documentation changes.

After tests change, regenerate the current inventory safely:

```sh
tmp=$(mktemp) && docker/bin/test inventory docs/modernization/php-7.4/active/KNOWN-DEFECTS.md > "$tmp" && chmod 644 "$tmp" && mv "$tmp" docs/modernization/php-7.4/TEST-INVENTORY.md
```

## Active work

- [Open defects](active/KNOWN-DEFECTS.md): KD-128 only.
- [Current handoff](active/SESSION-HANDOFF.md): Next actions and unresolved follow-ups.
- [Pending consumer impact](active/CONSUMER-IMPACT.md): v1.74.1 changes to check before release.

## Reference

- [Change log](CHANGELOG.md)
- [Published release notes](RELEASE-NOTES.md)
- [Draft v1.74.1 release notes](RELEASE-NOTES-V1.74.1.md)
- [Current test inventory](TEST-INVENTORY.md)
- [Exit review](notes/EXIT-REVIEW.md)
- [Environment](notes/ENVIRONMENT.md), [vendored patch](notes/VENDORED.md),
  [API comparison](notes/API-COMPARISON.md), [consumer checks](notes/CONSUMER-CHECK.md),
  [dependency alerts](notes/DEPENDENCY-ALERTS.md), [class rename record](notes/ALIASES.md)

## Archive

Completed work is in [history](history/): the [adversarial review](history/ADVERSARIAL-REVIEW.md),
[previous defect registry](history/KNOWN-DEFECTS.md), [full impact ledger](history/CONSUMER-IMPACT.md)
and [previous handoff](history/SESSION-HANDOFF.md). Use the active files for current tasks.
