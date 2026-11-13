# Utility, XML, and event coverage progress

## Contracts and decisions

- List-check factory: the maintainer confirmed that the **last supplied value
  defines the list**. Tests use a final array-valued member and preserve this
  behavior; no append-every-member change was made.
- Pending questions concern scalar-validation fallback semantics, whether relative
  time unit tables are subclass customization points, and list failure-callback
  dispatch. No behavior changes are inferred while those answers are pending.

## Tests added so far

- `Core/Util/UtilityTest.php`: strict boolean options and scalar modes, recursive
  array copy and object conversion, identity/aliasing, empty values, timecode
  conversion, relative-time units and parsing, and reserved-word-safe names.
- `Core/Util/ValueTest.php`: stringability of scalars, arrays, objects, and
  resources; current scalar-validation return behavior.
- `Core/Util/ListcheckTest.php`: the confirmed last-value list contract, repeated
  configuration, empty configuration, and loose membership comparison.
- `Core/Xml/EncodeTest.php`: parsed XML documents for scalar and nested values,
  type attributes, escaped content/keys, invalid node-name fallback, metadata,
  checksums, instance/string access, and the compatibility alias.
- `Core/EventTest.php`: handler replacement and ordering, listener arguments and
  priority, data aggregation, no-overwrite semantics, invalid names/keys, and
  handler-failure propagation. No external services are needed.

## Findings to preserve or resolve later

- Copying falsy inputs, including numeric zero and empty arrays, returns null.
  Default object cloning is shallow; explicit conversion options recurse.
- Time-ago instances store `relative_to` but do not pass it from `time_ago()` or
  `__toString()`. Tests currently preserve existing behavior; no fix was made.
- Scalar validation references undefined variables and ignores supplied options.
  Its purpose/fallback policy was queried before changing this implementation.
- XML checksums currently hash `"null"` rather than the serialized input. Tests
  record that behavior; no checksum policy was silently corrected.
- XML encoding drops falsy scalar values into its empty-value representation.
- Listener argument priority is handler result, stored key/value data, event
  data, then call data. Handler arguments aggregate multiple input categories.
  Null stored values can be replaced even with the no-overwrite flag because
  the implementation uses isset. These are tested, not silently redesigned.

## Verified progress (MOD-013)

PHP 7.2.34, PHPUnit 8.5.56, Xdebug 2.9.8, using the retained Docker-only harness:
**164 tests passing, 1,915 assertions**. No skips or failures in this run.

Framework: **8.43% lines (991/11,756)**, **10.84% methods (115/1,061)**,
**10.27% classes (34/331)**. No new source edits or coverage exclusions in this
batch. The denominator changes as newly loaded code replaces static estimates.

New complete targets:

| Target | Lines | Methods |
| --- | ---: | ---: |
| `core/util/deepcopy.php` | 29/29 | 2/2 |
| `core/util/reserved.php` | 5/5 | 1/1 |
| `core/util/time.php` | 6/6 | 2/2 |
| `core/util/validate/assoc.php` | 16/16 | 3/3 |
| `core/event.php` | 36/36 | 6/6 |

XML encoding, relative time, scalar validation, and list failure handling remain
in progress. They are not claimed as 100%. Existing complete targets remain
covered; detailed reports are in `/workspace/flat-environment/php72-tools`.

## Confirmations and approved fixes (MOD-014–MOD-016)

- Scalar validation now returns scalar inputs unchanged, including falsy scalars;
  validation mode returns true/false; explicit fallback values retain their types.
  This is a deliberate change from the previous always-null implementation.
- List rejection invokes the registered callback with the factory and rejected
  value. Callback exceptions propagate. Last-supplied-value list semantics remain.
- Relative-time subclasses may customize units. A legitimate no-match test covers
  the remaining return; no live code was disabled to reach 100%.
- Numeric string conversion now checks is_numeric first. The maintainer approved
  preserving nonnumeric text rather than the PHP 7.2 loose-comparison corruption.
- Core value validation, list factory, relative time, and the scalar wrapper have
  reached 100% measured line/method coverage. The scalar wrapper's empty-input
  behavior is still under discussion and has not been silently corrected.
- Current DOM conversion tests exposed a related legacy quirk: repeated elements
  deep-copy the first value, so an initially empty string becomes null. This is
  recorded as current behavior, not treated as an intended XML design decision.

## Updated copying contract (MOD-022)

The maintainer prioritized fixing index-zero loss. Deepcopy now preserves falsy
scalar values and empty arrays instead of changing them to null. This also fixes
repeated-node DOM conversion's empty first value. `arr(null)` still returns null,
while `arr([])` returns an empty array. Existing default object cloning stays
shallow. The earlier null-conversion findings above describe the pre-fix state;
corresponding tests now verify the approved preservation behavior.

## MOD-051 validation findings

Filter validation now supplies one list-valued member to the factory (preserving
last-member semantics) and throws its concrete invalid_filter. Invalid regex
syntax uses the real preg_error error-text helper. Base fallback options and PCRE
execution-error policy are pending owner answers; current ignored options and
non-string pattern ArgumentCountError are explicitly characterized.

## Native regex diagnostics and hostname definition (MOD-071, MOD-074)

PCRE error tests trigger real engine failures on PHP 7.2, including compile,
backtrack, recursion and invalid UTF-8 offsets. Tests restore JIT and limit INI
settings; the existing helper's false-versus-exception decision is still pending.
The hostname class now implements the actual regexp definer interface and wraps
its unchanged public raw regex constant in delimiters. Tests exercise its existing
ASCII label grammar with the generic matcher, without DNS lookups or new policy.
Validation failure messages read the provided scalar argument rather than an
undefined variable; no base-validator option behavior changed.


Shutdown continuation (MOD-088): the existing register documentation establishes newest-first equal-priority callbacks. Collision insertion now preserves every callback and the queue upper bound; explicit repeated invocation remains repeatable. SSH continuation (MOD-091) uses a declared namespaced transport double with real local OpenSSL/files/processes; no native SSH authentication or SFTP transfer success is claimed.


Validation continuation (MOD-097): the interface-mismatch exception now has a real canonical parent plus its historical name. PCRE engine failures still return false: native returns 1/0/false exhaust all paths before the old throw. That unreachable statement is retained with proof in a conservative comment rather than excluded from measurement.


Current validator behavior (MOD-099) stays pass-through/null: isset cannot overwrite its three NULL option defaults, so their unreachable dispatch is retained with proof in comments. The option feature question remains open. Holiday tests (MOD-101) use actual Yasumi 1.8.0 and native dates, exposing unset fields/cache attachment/invalid provider argument defects without choosing a new date contract.

Alert default broker configuration is verified against real RabbitMQ (MOD-103). Default CRUD still names an absent relative class; trigger still warns about undefined meta/driver and fails before publishing. No pthread listener, publisher routing or success behavior has been invented.


Owner-approved continuation (MOD-105/107): SFTP uses an idle clock reset after every nonempty successful read; "0" is data, and finally blocks close both streams. Real local reads plus the explicit SSH transport/clock boundary verify transfer/idle behavior; no real SSH server claim is made. Alert publisher/thread scaffolding is retired: trigger throws explicitly without running callbacks, the pthread class is no longer declared, and storage requires an explicit provider instead of an impossible default. Subscriptions, synchronous listeners and native RabbitMQ fallback remain supported/tested.
