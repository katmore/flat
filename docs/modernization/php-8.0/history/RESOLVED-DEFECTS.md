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
