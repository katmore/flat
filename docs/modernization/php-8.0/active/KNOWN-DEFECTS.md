# Known defects

Open items come from the [adversarial review](ADVERSARIAL-REVIEW.md) (2026-11-28).
KD-129/Q63 is fixed with the approved match_rule rename:
[resolved record](../history/RESOLVED-DEFECTS.md).

## KD-130 / Q64: not installable on PHP 8.0 with default Composer (B)

Every PHP 8.0-compatible mongodb/mongodb and aws/aws-sdk-php release has an
advisory, and Composer's default insecure-version blocking makes resolution fail.
Recommendation: before the release, document the tested consumer workaround
(`config.audit.ignore` for the four advisory IDs) in consumer impact and the
release notes. Add a fresh-resolution check to the harness. Owner decision.

## KD-131 / Q65: PHP 8 comparisons change non-strict flag lookups (B)

An option array holding integer 0 no longer enables every string flag. Two
consumer-visible differences are demonstrated in the review. Recommendation:
keep PHP 8's behavior, which is the evident intent. Record it in consumer impact
and pin it with tests. Owner decision: should flag lookups become strict?

## KD-132 / Q66: make_safe_classname misses reserved words (B)

`match` and `mixed` (new in PHP 8.0) and older words are not escaped, so the
returned name cannot be declared. Recommendation: add the missing words.
This changes the output for those inputs.

## KD-133: failed request leaks the temporary cookie jar (A, pre-existing)

The jar from `accept_cookies` stays when the constructor throws `curlexec_error`.
It happens on PHP 7.4 as well. Fix: delete the jar before rethrowing.

Next identifiers: KD-134 and Q67.
