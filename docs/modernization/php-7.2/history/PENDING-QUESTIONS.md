# Pending behavior questions

> **Historical, kept for the record:** replaced by the owner questions in [../active/KNOWN-DEFECTS.md](../active/KNOWN-DEFECTS.md).

No owner answers have been inferred from silence. This document separates current
coverage blockers from behavior changes that can wait while existing behavior is
characterized. Changes and unreachable-code proofs are in [CHANGELOG.md](../CHANGELOG.md).

## Coverage decisions answered by the owner

All three former blockers have explicit answers and implementations in progress
through MOD-105/106/107; no owner answer was inferred from silence.

1. **SSH SFTP timeout:** restart after each successful read. Implemented with an
   idle clock and native feof, preserving callback/return behavior and cleaning up
   streams on failure.
2. **Mongo connections:** connect=true actively verifies connectivity and retries
   failures. Implemented with actual ping verification for new/stale eager clients,
   successful-verification timestamps, eviction on failure and positive configured
   retry counts. Explicit connect=false remains lazy; existing TTL reuse remains.
3. **Unfinished alerts:** retire unfinished things while preserving tested
   subscriptions and synchronous listeners. The publisher fails explicitly, the
   pthread class is retired, and missing subscription storage requires a provider.

The focused run passes 150 tests / 361 assertions with complete line coverage for
all changed components. The final full-suite coverage run is recorded separately
in [COVERAGE-BASELINE.md](COVERAGE-BASELINE.md) and [CHANGELOG.md](../CHANGELOG.md). No further owner input is
currently required to finish the coverage work; older questions below concern
characterized behavior and later changes.

## Unanswered behavior questions that can remain characterized for now

| Earlier question | Contract still needing owner intent | Current treatment |
| --- | --- | --- |
| 2 | PDO fetch true/class/callback/false options: make their documented meanings work? | Explicit fetch options still throw; real fetch_callback rows are tested separately. Local closures cannot escape the throw and are retained with proof in comments. Native SQLite rowCount gate behavior stays characterized. |
| 3 | Special HTML: emit multiple strings/callbacks in supplied order? | Existing first-item/self-dependency defect is characterized; cyclic queue is not executed. |
| 4 | Base validator: implement pass/fail fallback values, failure callback and exception_on_fail? | Preserve pass-through/null behavior. NULL defaults cannot merge via isset; unreachable dispatch is retained with proof in comments. |
| 5 | Image constructor: minimum bounds or fixed targets with proportions? | Existing constructor behavior is characterized; no new sizing policy chosen. |
| 7 | PCRE engine errors: throw preg_error or return false? | Preserve native false result under the original loose comparison. The impossible subsequent throw is retained with proof in a comment. |
| 8 | Retain or retire legacy payment wrappers overall? | Preserve APIs and repair clear declaration/argument defects. Subscription create still returns null despite its string docblock; pagination offset still does not advance; scalar response_error still ignores its second argument. No new policies invented. |
| 9 | Retain or retire SSH overall? | Preserve public operations and repair clear guards/arguments/exception names. SFTP idle timing is now resolved under MOD-105. No real SSH-server authentication is claimed. |
| 10 | Root legacy ORM custom driver selection and get result? | Default declared session fallback now works with native persistence. Nonempty constructor arrays remain malformed and get still discards its result; undefined-param branches are retained with proof in comments. |
| 12 | Yasumi get_date: Unix timestamp or formatted string? | Test real Yasumi 1.8.0 without changing wrapper behavior. Valid date/year fields can remain unset, cached providers unattached, locale ignored and raw timestamps rejected by providers. |
| 13 | API requestMethod: incoming HEAD or routed GET? | Preserve missing-method exception; its unassigned private-field return is retained with proof in a comment. |
| 14 | Validation factory: collect validator/data pairs or only other factories? | Preserve default constraint; a legal override tests pair collection. Exception names now resolve to the actual parent. |
| 16 | Legacy DB load_read: declared read() method or distinct op_read hook? | Preserve literal-self warning/NULL behavior. PHP 7.2 constants cannot hold objects, so the unreachable read body is retained with proof in a comment. |

## Resolved from an existing explicit contract

Earlier question 1 asked about equal-priority shutdown order. The existing
register() documentation explicitly specifies newest-first ties. Collision
insertion was repaired to preserve every callback accordingly; this was not
recorded as an owner reply. Repeated explicit invocation remains repeatable.
