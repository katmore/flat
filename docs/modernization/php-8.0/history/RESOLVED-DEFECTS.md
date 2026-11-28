> Archived: resolved findings. Current work: [defect index](../active/KNOWN-DEFECTS.md).

# Known defects

## KD-129 / Q63: reserved class name (fixed, owner approved)

PHP 8 reserves match. The empty abstract class in
`src/flat/core/util/validate/match.php` cannot be parsed. The complete API scan
finds it even though the loaded-code tests never instantiate it.

Recommendation: rename only the class and file to
`flat\core\util\validate\match_rule`; the match child namespace stays valid.
No repository code references or extends the empty class. External subclasses
must update their parent name. AGENTS.md requires the owner's decision and
forbids class aliases. The owner approved match_rule on 2026-11-27; MOD-347 implements it.

Next identifiers: KD-130 and Q64. Preserve the positional event fix (MOD-344).

## KD-131 / Q65: literal string flags (fixed, owner approved)

The owner approved strict flag lookup on 2026-11-28. MOD-366 makes
named-flag lookups strict in PDO, data mapping/scalars, templates, assets,
sessions, XML and cURL. Numeric/boolean/null option values no longer enable
unrelated flags. Named associative settings retain their existing semantics.
Regression tests exercise field rendering, fallback mapping, scalar display,
templates, prepared statements, sessions, XML and cURL. General data-list
membership is unchanged.

## KD-132 / Q66: reserved class names (fixed, owner approved)

The owner approved updating the reserved-word table on 2026-11-28. MOD-366
adds match, mixed, the missing earlier keywords/type names and eight magic
constants that also cannot name a PHP 8.0 class. Case, namespace segments and
custom delimiters retain the existing underscore-escaping convention. Tests
actually declare every escaped keyword as a class and check the missing names.
Names that PHP 8.0 permits (e.g. resource, numeric and integer) are not reserved.

## KD-133: failed-constructor temporary files (fixed)

MOD-366 cleans up private cookie and download files whenever construction
throws, including transfer and HTML-parser failures. A retained exception can
retain its cURL handle, so failure cleanup disables that handle's private
cookie-jar output before removing the file. Caller-owned paths are preserved.
Tests retain exceptions while checking file removal, release them to check
that files are not recreated, and verify caller cookies are kept.

## KD-130 / Q64: fresh Composer resolution (resolved workaround, owner approved)

The owner approved only PKSA-4t1p-xpk2-nsss, PKSA-dxyf-6n16-t87m,
PKSA-y93m-jgy6-r9ns and PKSA-61k5-cqr9-b8b4 on 2026-11-28. MOD-367 adds
apply=block exceptions to the runtime harness and documents the identical
consumer-root configuration. Fresh default consumer resolution fails; with only
those exceptions both consumer/runtime resolution pass. Audits still report all
four and other advisories still block resolution. These packages remain
vulnerable; PHP 8.1 remediation is carried in the current handoff.

Current next identifiers: KD-134 and Q67.
