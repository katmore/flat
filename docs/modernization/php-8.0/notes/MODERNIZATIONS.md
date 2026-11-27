# PHP 8.0 modernizations

The modernization changes are kept separate from runtime compatibility fixes.
Public names, parameters, defaults, return values and payload formats stay compatible.

## Implemented

- str_contains for boolean substring checks; strpos stays where its position is used.
- str_starts_with and str_ends_with for nonnumeric prefix and suffix checks.
- match and throw expressions for validated integer asset dispatch and PCRE errors.
- Object ::class for values known to be objects.
- Stringable for the existing object-to-string capability check.
- Noncapturing catch clauses where the caught exception is unused.
- Nullsafe access to the optional LibXMLError message.
- Constructor promotion for private collection-member storage, keeping its untyped
  parameter and existing name.

Restrictive public union/mixed types and attributes are not added merely to use
syntax: they would change reflection contracts or require a new consumer protocol.
Nullsafe access is used only where it would simplify a real nullable-object path.

Verification and exact results belong in the phase change log and API comparison.
