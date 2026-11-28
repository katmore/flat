# API comparison

Full Reflection dumps cover all 602 framework entries, including files never
loaded by the execution suite. All 602 load on PHP 8.0 after the approved rename.

## PHP 7.4 to PHP 8.0

- One approved rename: validate\match becomes validate\match_rule (Q63).
  It is an empty abstract base class; its child namespace is unchanged.
- PHP itself adds Stringable to 302 classes and infers string return metadata
  on 16 __toString methods. These are native engine additions.
- No other public/protected names, signatures, parameters, defaults, properties
  or constants change in the runtime migration.

## Syntax modernization

The 602-entry dumps immediately before and after MOD-348 are identical.
Private storage promotion, match, string predicates and the other rewrites
therefore leave the reflected public API unchanged.

The comparison tool now handles PHP 8 union and mixed types (MOD-349).
The owner's release decision must account for the approved public base-class rename.

Final generated snapshot: [API-PHP80.json](API-PHP80.json). The final dependency
and crypto refresh leaves this 602-entry snapshot identical to the post-modernization dump.

## Reviewed behavior fixes

MOD-366 expands reserved::keywords to include PHP 8.0 reserved identifiers.
This is the only reflected API difference from the preceding phase snapshot;
all 602 entries still load and public/protected signatures remain unchanged.
