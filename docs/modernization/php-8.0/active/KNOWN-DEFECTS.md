# Known defects

Open items come from the [adversarial review](ADVERSARIAL-REVIEW.md) (2026-11-28).
KD-129/Q63 is fixed with the approved match_rule rename:
[resolved record](../history/RESOLVED-DEFECTS.md).

## KD-130 / Q64: not installable on PHP 8.0 with default Composer (B)

Every PHP 8.0-compatible mongodb/mongodb and aws/aws-sdk-php release has an
advisory, and Composer's default insecure-version blocking makes resolution fail.
Recommendation: before the release, document the tested consumer workaround
(`config.audit.ignore` for the four advisory IDs) in consumer impact and the
release notes. Add a fresh-resolution check to the harness. Owner approved the four advisory IDs on 2026-11-28; validation and documentation are in progress.

KD-131/Q65, KD-132/Q66 and KD-133 are fixed in MOD-366:
[resolved records](../history/RESOLVED-DEFECTS.md).

Next identifiers: KD-134 and Q67.
