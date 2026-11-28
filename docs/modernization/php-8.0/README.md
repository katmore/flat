# PHP 8.0 phase

## Status: implementation under review

Requires PHP 8.0 or later; tested on pinned PHP 8.0.30. The owner declares phase
completion and publishes releases. Work branch: php-8.0.

- Full Docker suite: 1,728 tests, no errors/failures/skips, 100% configured loaded-code coverage.
- All 602 framework API entries load. API changes are the approved match
  base-class rename and reserved-word table expansion. Syntax modernization
  leaves public signatures unchanged.
- No open adversarial-review defects ([defect index](active/KNOWN-DEFECTS.md)).
  PHP 8.0 consumers must apply the four approved Composer advisory exceptions
  described in [consumer impact](active/CONSUMER-IMPACT.md#mod-367-php-80-composer-resolution-q64).
- Current compatible tools/dependencies are installed. Four inherited advisories
  require PHP 8.1 fixes; no new YAML advisory is accepted.

## Verification

```sh
docker/bin/test all --coverage
docker/bin/test-javascript
docker/bin/check-docs
docker/bin/check-resolution # online: fresh PHP 8.0 resolution and advisory checks
```

After tests change, regenerate the inventory safely:

```sh
tmp=$(mktemp) && docker/bin/test inventory docs/modernization/php-8.0/active/KNOWN-DEFECTS.md > "$tmp" && chmod 644 "$tmp" && mv "$tmp" docs/modernization/php-8.0/TEST-INVENTORY.md
```

## Records

[Current handoff](active/SESSION-HANDOFF.md), [defect index](active/KNOWN-DEFECTS.md),
[adversarial review](history/ADVERSARIAL-REVIEW.md), [fix verification](history/REVIEW-VERIFICATION.md),
[consumer impact](active/CONSUMER-IMPACT.md), [change log](CHANGELOG.md),
[test inventory](TEST-INVENTORY.md), [environment](notes/ENVIRONMENT.md),
[API comparison](notes/API-COMPARISON.md), [modernizations](notes/MODERNIZATIONS.md),
[dependency alerts](notes/DEPENDENCY-ALERTS.md), [consumer check](notes/CONSUMER-CHECK.md).

Completed reviews and checks are preserved in history. Private consumer, live
payment and sample-app runtime integration follow-ups remain in the handoff.
