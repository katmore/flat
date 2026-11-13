# Mapper contract, coverage, and changes

## Confirmed purpose and boundaries

`flat\core\mapper` is a trait providing property mapping and conversion helpers.
Before altering access behavior, the maintainer confirmed:

- `each_property()` is **public-properties-only**. Its private/protected access
  flags are obsolete. The options parameter remains for existing call sites.
  This decision applies to iteration, not to the separate read/write APIs.
- Preserve the existing `ReflectionProperty::setAcessible()` failures for now.
  Correcting the spelling would enable previously failing operations and is a
  separate behavior change requiring a maintainer decision.

The working rule is to ask about unresolved purpose before imposing new behavior.
Tests distinguish the supported contract from observed legacy quirks.

## Added unit coverage

`tests/Unit/Core/MapperTest.php` contributes 27 cases, using small fixture classes
that expose the trait's protected methods and implement its marker interfaces.
It covers:

- Reads by name and reflection; null values, missing/invalid/private properties,
  ignored reads, and object/class-name/scalar fallback sentinels.
- Writes to declared, protected, explicitly permitted private, and dynamic
  properties; method-name collisions; missing/empty names; ignored failures;
  marker-interface options.
- Public associative/stdClass exports, alternate input objects, null omission,
  and PHP 7.2's handling of dynamic properties during ReflectionClass enumeration.
- Array/object input mapping, positional/named/default argument lists, nested
  argument arrays, empty lists, and positional parameter mapping.
- Public iteration, selected properties, transformations, null-sentinel updates,
  private-property skipping, and legacy failures for protected/missing entries.
- All three preserved misspelled-reflection-call failures.

## Conservative source changes

Only `src/flat/core/mapper.php` changed. Original code remains inside conspicuous
`CONSERVATIVELY DISABLED` comments:

1. Default iterator filter branches for private/protected flags: flags are always
   false and supplied options are never merged. The maintainer confirmed these
   flags are obsolete, so no new option behavior was implemented.
2. Iterator private/protected write branches: private properties cannot enter
   the property list; explicitly listed protected properties fail the reflection
   read before the callback/write block on PHP 7.2. The test preserves that failure.
3. The setter's failed-string-type branch after casting to string: a successful
   cast is always a string. The live cast is retained; its exceptions still occur
   before the old type check could run.
4. The failed-object-type branch immediately after casting to object: a successful
   object cast always yields an object. The live cast and fallback are retained.

The public-only contract was added to the method comment. No method signatures,
valid access paths, exception policy, runtime requirements, or coverage filters
changed. No ReflectionProperty spelling fixes were made.

## Findings preserved for later

- Private reads with `get_private_property=true`, associative export with private
  access, and stdClass export with private access still throw `Error` at
  `setAcessible()`. Tests pin the failure; the maintainer explicitly deferred fixes.
- A missing selected property produces a `cannot_get_property` exception whose
  message omits the requested name; the catch references undefined `$property`.
  The current harness returns the exception rather than elevating its notice.
- Protected properties supplied explicitly to public-only iteration still cause
  `ReflectionException`; this cleanup does not introduce a new skip/error policy.
- Dynamic properties of stdClass and scalar-cast objects are absent from these
  exports on PHP 7.2 because this implementation enumerates ReflectionClass
  properties. Declared public properties are exported. No semantics were inferred
  or changed to make dynamic export work.

These findings do not take priority over coverage work. Fixes need an explicit
behavior decision and a running change-log entry.

## Verification and metrics

Same Docker harness as [../history/COVERAGE-BASELINE.md](../history/COVERAGE-BASELINE.md): PHP 7.2.34, PHPUnit 8.5.56,
Xdebug 2.9.8. No host PHP tooling was installed.

| Metric | Before mapper batch | After mapper batch |
| --- | ---: | ---: |
| Tests passing | 68 | **95** |
| Assertions | 1,653 | **1,722** |
| Framework line coverage | 4.04% (485/12,019) | **5.34% (641/12,001)** |
| Framework method coverage | 6.22% (66/1,061) | **7.45% (79/1,061)** |
| Framework class coverage | 6.04% (20/331) | **7.25% (24/331)** |
| Mapper line coverage | partial | **100% (165/165)** |
| Mapper method coverage | partial | **100% (12/12)** |

Configuration and all six original target classes remain at 100% lines/methods.
These are the legacy driver's line/method counts, not branch-coverage results.
Preserving a failing branch does not prove the code after its error executes.

The expanded 95-test suite also passed against the **original HEAD mapper**, with
exactly 1,722 assertions, before the cleaned file was restored. This differential
check supports retaining current behavior across the disabled blocks. There were
no failed or skipped tests in either successful run.

Run through the repository-owned Docker wrapper:

```sh
bin/test all --coverage
```

Current reports are under `build/tests/all/`. The earlier mapper differential
check is historical evidence; its private diagnostic helper is no longer a
supported test entry point. See [../../TESTING.md](../../TESTING.md) for portable setup.
