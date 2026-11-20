# Modernizations with PHP 7.3 (no behavior change)

What was rewritten to use a PHP 7.3 feature, to show the progression (decision D4). Each is its
own commit and change-log entry. Proof for each: the whole suite passes unchanged, and the API
comparison shows no difference ([API-COMPARISON.md](API-COMPARISON.md)).

## M1 — `array_key_first()` (MOD-191)

- `src/flat/db/driver/mongo/crud.php`, option `start`: `array_keys($opt['start'])[0]` is now
  `array_key_first($opt['start'])`. The array is checked just before to be an array with
  exactly one element, so both give the same key; the old form could only differ for an empty
  array (it noticed an undefined offset), which the guard already excludes.

## Looked at and not changed

- `reset()` and `key()` in `flat\core\collection` and `collection\controller` implement
  `Iterator` over the internal array pointer; they are not a "first key" trick.
- No manual `is_array() || instanceof Countable` checks exist, so `is_countable()` has no
  place to go. Trailing commas in calls and flexible heredocs would only touch lines for
  style, so they were left.
- `JSON_THROW_ON_ERROR` was not used: it changes how failures surface (a behavior change).
