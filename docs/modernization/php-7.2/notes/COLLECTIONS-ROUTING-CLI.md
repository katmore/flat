# Collections, routing, CLI, and event-factory testing

## Current verification after approved repairs

The full PHP 7.2.34 suite now passes **334 tests / 2,439 assertions**, with no
errors, failures, or skips. The two errors from MOD-021 are resolved.

Framework coverage: **20.89% lines (2,311/11,062)**, **26.20% methods
(278/1,061)**, **19.64% classes (65/331)**.

New complete targets in this batch:

| Target | Lines | Methods |
| --- | ---: | ---: |
| Collection class | 17/17 | 3/3 |
| Class/interface constraint controller | 53/53 | 5/5 |
| Validator controller | 24/24 | 3/3 |
| Routing controller | 137/137 | 4/4 |
| CLI helper | 204/204 | 24/24 |

These measurements do not imply whole-framework coverage is complete. The
collection controller trait still needs the maintainer's key/range decisions.

### Approved behavioral repairs

1. **Index zero and copying:** deepcopy now preserves falsy scalar values and
   empty arrays. `arr(null)` retains null behavior. This prevents zero index
   offsets becoming null and also preserves empty first values in repeated-node
   DOM conversion. Default object cloning remains shallow; recursive object-clone
   policy was not expanded. `load_copy` reads source members/index, initializes
   fresh member storage safely, and creates an independent collection for the
   selected destination type. Default-constructible destination classes are
   covered, along with invalid/abstract fallback destinations.
2. **Declared constraints:** fully qualified class/interface names activate the
   controllers. Class constraints validate class names rather than interface
   names. Invalid members now raise constraint failures instead of slipping
   through. This is an intentional compatibility-affecting change the maintainer
   explicitly approved.
3. **Validator hooks:** the validator controller retains the collection and calls
   its declared `member_validate` / `member_filter` methods. Successful filtered
   values reach `member_success`; ignored members do not. Tests exercise true,
   null, empty, false, reason strings, exception objects, ignore objects, and
   filter outcomes. Existing rejection of a truthy nonstring/nontrue return is
   characterized; that return-policy inconsistency was not redesigned.
4. **Routing subclasses:** the special-node map is accessed through base-owned
   storage, with the existing hash per node set retained. Subclasses and custom
   SPECIAL_NODES lists now work without private-property access errors.

### CLI completion without behavior changes

- The private `_argv_to_command` helper has no callers in source or tests; its
  original definition is retained in a CONSERVATIVELY DISABLED comment.
- `printf` requires a format argument, so its zero-argument body branch cannot
  execute. That branch is retained in a CONSERVATIVELY DISABLED comment.
- A test-local namespaced getopt primitive supplies a controlled result/index
  to verify operand slicing. Calls without the fixture state forward to native
  getopt. Fixture state and global argv are restored after the test.
- The missing-argv exception namespace and input-line sentinel quirks remain
  unchanged and documented for later decisions.

### Pending collection contracts

The maintainer was asked whether unkeyed iteration should return numeric
positions or null, and whether range limit means a count or an exclusive end.
No key/range behavior change has been inferred. Out-of-range exception repair is
included in the pending range question. Callback duplication also remains as
previously documented; the new interface enforcement does not silently change
callback invocation counts.


## Earlier blocked verification (MOD-021)

The latest complete PHP 7.2.34 run executed **310 tests**, making **2,366
assertions**, with **2 errors**, no assertion failures, and no skipped tests.
The two errors expose existing behavior whose changes require pending maintainer
answers; this is not a green readiness result.

Coverage collected during that run:

| Scope | Lines | Methods |
| --- | ---: | ---: |
| Whole framework | **20.07% (2,221/11,065)** | **24.67% (262/1,062)** |
| Core event factory | **100% (41/41)** | **100% (8/8)** |
| XPath node list | **100% (20/20)** | **100% (9/9)** |
| Routing controller | 99.27% (136/137) | 75% (3/4) |
| CLI helper | 95.67% (199/208) | 88% (22/25) |
| Collection controller trait | 93.51% (72/77) | 84.62% (11/13) |
| Collection class | 41.18% (7/17) | 0% (0/3) |

Whole-framework class coverage is **17.22% (57/331)**. Existing complete targets
remain covered. These are line/method/class counts, not branch coverage. The last
fully passing complete run before adding the collection/routing probes was
**264 tests / 2,236 assertions**, at 15.06% line coverage.

## New tests and findings

### Collections

`tests/Unit/Core/CollectionTest.php` exercises empty/populated iteration, member
lookup, ignored members, wrapper copying, range limits/callback arguments,
callback ordering, and thrown validator errors.

Confirmed by the new failing regression: passing an index map containing offset
zero through deepcopy changes zero to null. The copied member is present, but
get('newKey') cannot retrieve it. The assertion is retained; the test is not
weakened to hide the failure.

Other findings are documented without silently redesigning them:

- Validation/filter callbacks run twice per added member. A filter adding one
  therefore changes 1 to 3; validator callbacks observe 1 then 2.
- Range limit behaves as an exclusive end index when start is nonzero, rather
  than a count. Only tested/current behavior is characterized here.
- The out-of-range exception uses an unresolved relative namespace and raises
  Error instead of the declared collection exception.
- Collection interface controllers use unresolved relative interface names, so
  ordinary class/interface constraints and validator interfaces are inactive.
- Static load_copy refers to $this and cannot populate a copy.
- Iterator key lookup compares a member object to position values, rather than
  comparing the current position. This is another deferred behavior issue.

Pending questions explicitly ask whether to enforce the declared interfaces,
repair copying, and preserve falsy deepcopy values. No changes have been made to
these implementations while those decisions are pending.

### Isolated routing

`tests/Unit/Core/Route/ControllerTest.php` uses in-memory fixture app classes and
factories; it does not run flat-webapp or require external services. Cases cover
resource normalization, route weights, iteration/traversal, unresolved suffixes,
transforms, cycles/demands, ignore interfaces, prepared/input/event/status
injection, string factories, reserved names, and invalid controller parameters.

A subclass-specific regression fails because `_transformSpecialNodes` accesses
private cache storage with late static binding. Base-controller cases pass.
Support for controller subclasses and custom SPECIAL_NODES tables was queried
before changing this behavior. The failure remains visible pending the answer.

### CLI

`tests/Unit/Core/CliTest.php` covers initialized/uninitialized arguments, commands,
resources, parameters, long/short options, the documented enumeration example,
input callbacks, line width/indentation, quiet mode, and print/dump/error handlers.
Static state is saved/restored per test. Dump assertions allow Xdebug's optional
source-location prefix while checking actual emitted values.

Deferred findings: missing global argv attempts to instantiate an unresolved
RuntimeException; the private `_argv_to_command` helper has no callers; the
zero-argument printf branch is unreachable because its format parameter is
required. Input-line iteration uses a truthiness sentinel rather than the
specified null sentinel. No CLI source changes have been made yet.

### Event factory

`tests/Unit/Core/EventFactoryTest.php` covers explicit/on-demand creation,
replacement handlers, listeners, stored data, per-trigger callbacks, ignored
invalid callbacks, duplicate policy, unknown types, and non-triggerable events.
The factory now reaches 100% lines/methods without source changes.

## Next actions after answers

Apply only the agreed collection/copy/cache changes, add the corresponding
regressions, and rerun the complete suite. Then continue through remaining
uncovered code. Preserve unapproved behavior findings in documentation rather
than expanding the source patch by assumption.

## Confirmed key/range contracts implemented (MOD-026)

The maintainer answered both pending questions: registered names for named
members, numeric positions for unkeyed members, and a count-based range limit.
Those changes are now implemented and tested, including range callback keys.
A registered numeric key zero is distinguished from an unsuccessful lookup;
exhausted iterators return null. `range(2, 1)` returns two members, clipping at the
end when necessary. The declared out_of_range exception now works, and negative
starts are normalized before validation. The collection iterator position is
unaffected by range calls.

Full run: **338 passing tests / 2,454 assertions**. The collection controller trait
now has **100% lines (78/78) and methods (14/14)**. Framework coverage is
**20.98% lines**, **26.55% methods**, **20.24% classes**. Earlier pending questions
and failures above are historical and are resolved by the explicit decisions.
