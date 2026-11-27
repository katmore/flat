# PHP 8.0 phase

## Status: validated implementation

Requires PHP 8.0 or later; tested on pinned PHP 8.0.30. The owner declares phase
completion and publishes releases. Work branch: php-8.0.

- Full Docker suite: 1,718 tests, 8,112 assertions, 100% configured loaded-code coverage.
- All 602 framework API entries load. The approved match base-class rename is
  the source API change; syntax modernization leaves the reflected API unchanged.
- Current compatible tools/dependencies are installed. Four inherited advisories
  require PHP 8.1 fixes; no new YAML advisory is accepted.

## Verification

```sh
docker/bin/test all --coverage
docker/bin/test-javascript
docker/bin/check-docs
```

After tests change, regenerate the inventory safely:

```sh
tmp=$(mktemp) && docker/bin/test inventory docs/modernization/php-8.0/active/KNOWN-DEFECTS.md > "$tmp" && chmod 644 "$tmp" && mv "$tmp" docs/modernization/php-8.0/TEST-INVENTORY.md
```

## Records

[Current handoff](active/SESSION-HANDOFF.md), [defect index](active/KNOWN-DEFECTS.md),
[consumer impact](active/CONSUMER-IMPACT.md), [change log](CHANGELOG.md),
[test inventory](TEST-INVENTORY.md), [environment](notes/ENVIRONMENT.md),
[API comparison](notes/API-COMPARISON.md), [modernizations](notes/MODERNIZATIONS.md),
[dependency alerts](notes/DEPENDENCY-ALERTS.md), [consumer check](notes/CONSUMER-CHECK.md).

Completed reviews and checks are preserved in history. Private consumer, live
payment and sample-app runtime integration follow-ups remain in the handoff.
