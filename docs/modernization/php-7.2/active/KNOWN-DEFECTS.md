# Known defects to fix before the PHP 7.3 step

Owner decision (2026-11): every known defect is fixed on `php-7.2` before the
PHP version climb starts, so bug fixes and runtime upgrades never mix.

This list comes from reading all 700 test methods and the testing documents.
Each entry's test currently asserts the defective behavior (characterization).
Its test carries `@group known-defect` until the fix lands, so
`docker/bin/test unit --group known-defect` lists what remains.

## Categories

- **A, clear bug**: one obviously correct behavior, usually stated by the
  code's own docblock, exception names or neighbouring code. Fixed directly.
- **B, intent needed**: the code is wrong, but the correct behavior is the
  owner's choice. Each has a recommendation below; nothing is fixed until the
  owner answers.
- **C, unfinished stub**: never implemented. The owner decides whether to
  implement or retire it.

## How each fix lands

1. Change the source.
2. Rewrite the test to assert the correct behavior, drop "Currently"/"Still"
   from its name and remove `@group known-defect`.
3. Add an entry to [CONSUMER-IMPACT.md](CONSUMER-IMPACT.md): every fix is a silent change for
   consumers that might have depended on, or worked around, the old behavior.
4. Mark the entry here as fixed, with its change-log ID.
5. Regenerate [../TEST-INVENTORY.md](../TEST-INVENTORY.md) with `docker/bin/test inventory`
   (see the phase README for the full command).

## Inventory

### KD-01 — PDO statement

Executing without a callback references an undefined data class (`Error`)

- Test: `Db/Pdo/StatementTest::testExecutedRowsWithoutACallbackStillReferenceUndefinedDataClass`
- Category: A
- Proposed fix: Map rows like the bare-record path does
- Status: fixed (MOD-133)

### KD-02 — PDO statement

`execute` gates on SELECT `rowCount`, so SQLite reports `not_found` despite returning rows

- Test: `StatementTest::testExecuteCurrentlyReportsNotFoundForSqliteSelectRowCount`
- Category: A
- Proposed fix: Fetch rows instead, as MOD-033 did for `prepare_map`
- Status: fixed (MOD-133)

### KD-03 — PDO statement

Every explicit `fetch` option (true/class/callback/false) throws `bad_option`

- Test: `StatementTest::testEveryExplicitFetchOptionCurrentlyThrows`
- Category: B
- Proposed fix: Q1
- Status: fixed (MOD-133)

### KD-04 — Mongo CA file

A failed certificate write returns `null` after a warning

- Test: `Db/Mongo/CaFileTest::testFailedFileWriteCurrentlyReturnsNull`
- Category: A
- Proposed fix: Throw an existing system/IO error
- Status: fixed (MOD-133)

### KD-05 — DB controller

`load_read` tests the literal constant `self`, warns and returns `null`

- Test: `Db/LegacyControllerTest::testStaticReadCurrentlyWarnsAboutItsLiteralSelfAndReturnsNull`
- Category: B
- Proposed fix: Q2
- Status: fixed (MOD-133)

### KD-06 — API response

Unknown error with a class caller calls the nonexistent `ReflectionMethod::getClassName`

- Test: `Api/ResponseTest::testUnknownErrorWithClassCallerCurrentlyCallsMissingReflectionMethod`
- Category: A
- Proposed fix: Use the declaring class name
- Status: fixed (MOD-124)

### KD-07 — API controller

`requestMethod()` reads a never-assigned field and always throws

- Test: `Api/ControllerTest::testUnassignedRequestMethodGetterCurrentlyThrows`
- Category: B
- Proposed fix: Q3
- Status: fixed (MOD-127)

### KD-08 — API controller

`set_input` without a method raises `unknown_method` instead of `missing_method`

- Test: `Api/ControllerTest::testMissingMethodCurrentlyRaisesUnknownMethod`
- Category: A
- Proposed fix: Raise `missing_method` (verify against source first)
- Status: fixed (MOD-127)

### KD-09 — API status

Code-class formatting indexes strings instead of class metadata (8 warnings, placeholders left in output)

- Test: `Api/StatusTest::testCurrentFormattingUsesStringOffsetsInsteadOfClassMetadata`
- Category: A
- Proposed fix: Read the class type/description metadata
- Status: fixed (MOD-127)

### KD-10 — API status

`not_found` status reports code 500

- Test: `Core/LegacyErrorMessagesTest::testLegacyNotFoundStatusCurrentlyReportsServerErrorCode`
- Category: A
- Proposed fix: Return 404
- Status: fixed (MOD-124)

### KD-11 — Asset

Error message says "could resolve"

- Test: `LegacyErrorMessagesTest::testErrorsRetainOperationContext` (row `not_resolvable`)
- Category: A
- Proposed fix: "could not resolve"
- Status: fixed (MOD-124)

### KD-12 — Asset

New inline JS source raises `TypeError` looking for a file path

- Test: `Asset/AssetTest::testNewInlineSourceCurrentlyCannotResolveAFilePath`
- Category: A
- Proposed fix: Print the inline source
- Status: fixed (MOD-130)

### KD-13 — Asset

Multiple HTML items create a self-dependency (cyclic queue)

- Test: `AssetTest::testMultipleHtmlItemsCurrentlyCreateAnAccidentalSelfDependency`
- Category: B
- Proposed fix: Q4
- Status: fixed (MOD-130)

### KD-14 — Asset

Unknown resource types and the style-source tag produce no output

- Test: `AssetTest::testUnimplementedResourceAndTagCasesCurrentlyProduceNoOutput`
- Category: C
- Proposed fix: Q5
- Status: fixed (MOD-130)

### KD-15 — Shutdown

Integer weight `0` joins the default queue; out-of-range weights (±51) are silently accepted

- Test: `Core/ShutdownTest::testZeroAndOutOfRangeWeightsCurrentlyJoinTheDefaultQueue`
- Category: A
- Proposed fix: `0` is a weight (docs: -50 to 50); out of range throws `bad_priority`
- Status: fixed (MOD-129)

### KD-16 — Shutdown

Explicit invocation followed by PHP shutdown runs callbacks twice (`_clear()` was commented out by the original author)

- Test: `ShutdownTest::testRepeatedInvocationCurrentlyRepeatsCallbacks`
- Category: B
- Proposed fix: Q6
- Status: fixed (MOD-112)

### KD-17 — Events

`hookable` drops event data and reads an undefined local

- Test: `Core/LegacyListenerTest::testHookableCurrentlyDropsEventDataAndReportsItsUndefinedLocal`
- Category: A
- Proposed fix: Pass the supplied event data to the handler
- Status: fixed (MOD-126)

### KD-18 — Events

`stub::get_listeners` reads an undeclared property

- Test: `LegacyListenerTest::testStubCurrentlyReadsASeparateUndeclaredListenerProperty`
- Category: A
- Proposed fix: Return the listeners `add_listener` stored
- Status: fixed (MOD-126)

### KD-19 — Yasumi wrapper

Valid date/year fields stay unset; the provider cache is not attached; providers reject the wrapper's raw timestamp

- Test: `Vendor/YasumiDateQueryTest::testValidDateAndExplicitYearCurrentlyLeaveTheirBackingFieldsUnset`, `::testProviderCacheCurrentlyDoesNotAttachItsHitToTheNextInstance`, `::testRealProviderRejectsTheWrappersRawTimestamp`
- Category: B
- Proposed fix: Q7
- Status: fixed (MOD-134)

### KD-20 — Validation factory

The default constraint rejects the pair its own helper builds

- Test: `Core/Util/ValidationFactoryTest::testDefaultConstraintCurrentlyRejectsThePairBuiltByItsHelper`
- Category: B
- Proposed fix: Q8
- Status: fixed (MOD-134)

### KD-21 — Validation

`value_on_pass`, `value_on_fail`, `exception_on_fail` and `fail_handler` are documented but ignored

- Test: `Core/Util/ValidationTest::testNullDefaultOptionsCurrentlyCannotBeOverridden`
- Category: B
- Proposed fix: Q9
- Status: fixed (MOD-134)

### KD-22 — Validation

A PCRE engine error returns `false` instead of the existing `preg_error`

- Test: `ValidationTest::testPcreExecutionErrorCurrentlyReturnsFalse`
- Category: B
- Proposed fix: Q10
- Status: fixed (MOD-134)

### KD-23 — Image

Constructor with both bounds leaves the image unchanged

- Test: `Core/Util/Image/ImageTest::testBothConstructorBoundsCurrentlyLeaveTheImageUnchanged`
- Category: B
- Proposed fix: Q11
- Status: fixed (MOD-134)

### KD-24 — HTML form

`serialize` builds the form and returns `null`

- Test: `Core/Html/FormTest::testSerializeCurrentlyDiscardsItsConstructedForm`
- Category: A
- Proposed fix: Return the built form
- Status: fixed (MOD-126)

### KD-25 — Collection

Data validator and filter run twice per added member

- Test: `Core/CollectionTest::testCurrentCallbacksRunTwicePerAddedMember`
- Category: A
- Proposed fix: Run each once
- Status: fixed (MOD-126)

### KD-26 — Map from object

Non-objects give an empty map; objects fail with `ArgumentCountError`

- Test: `Core/LegacyMappingTest::testNonObjectInputCurrentlyProducesAnEmptyMap`, `::testObjectMappingCurrentlyOmitsRequiredValueArgument`
- Category: A
- Proposed fix: Pass the missing value argument
- Status: fixed (MOD-126)

### KD-27 — CLI

Missing `$argv` throws an unqualified `flat\core\RuntimeException` (`Error`)

- Test: `Core/CliTest::testMissingArgvPreservesCurrentExceptionNamespaceFailure`
- Category: A
- Proposed fix: Throw `\RuntimeException`
- Status: fixed (MOD-124)

### KD-28 — Core mapper

Private-access options fail on the `setAcessible` typo

- Test: `Core/MapperTest::testKnownPrivateAccessReflectionTypo`
- Category: B
- Proposed fix: Q12
- Status: fixed (MOD-113)

### KD-29 — Debug

`data2str` uses the object template for arrays and leaves placeholders for small scalars

- Test: `Core/DebugTest::testDataDescriptionsCharacterizeCurrentFormatting`
- Category: A
- Proposed fix: Use the matching template and fill it
- Status: fixed (MOD-132)

### KD-30 — Debug

`kill` options overwrite the options array and read an undefined `fh_options`

- Test: (`../history/TESTING-FOLLOWUPS.md`; not yet asserted)
- Category: A
- Proposed fix: Keep the options array; define the handle options
- Status: fixed (MOD-132)

### KD-31 — cURL response

Missing curl info throws a nonexistent exception class (`Error`)

- Test: `Core/Curl/ResponseTest::testCurrentMissingInfoExceptionClassFailure`
- Category: A
- Proposed fix: Declare/use the exception
- Status: fixed (MOD-124)

### KD-32 — cURL config

Selecting the Chrome user agent is overwritten by the raw selector (`0`)

- Test: `Core/Curl/ConfigTest::testCurrentChromeSelectionIsOverwrittenByRawSelector`
- Category: A
- Proposed fix: Keep the selected user-agent string
- Status: fixed (MOD-132)

### KD-33 — Legacy ORM

The static `get_data` facade discards its result

- Test: `Data/Orm/LegacyOrmTest::testDefaultFacadeUsesRealNativeSessionsWhileRetainingItsVoidGetResult`
- Category: A
- Proposed fix: Return the stored data
- Status: fixed (MOD-133)

### KD-34 — Legacy ORM

A nonempty constructor array cannot act as a driver

- Test: `LegacyOrmTest::testNonemptyConstructorArrayStillCannotActAsADriver`
- Category: B
- Proposed fix: Q13
- Status: fixed (MOD-133)

### KD-35 — ORM session driver

`destroy` regenerates the id after ending the session (warning)

- Test: `Data/Orm/SessionDriverTest::testDestroyCurrentlyAttemptsRegenerationAfterEndingTheSession`
- Category: A
- Proposed fix: Regenerate before destroying, or not at all
- Status: fixed (MOD-133)

### KD-36 — Data mapper

`webview` discards its HTML-encoded clone

- Test: `Data/MapperTest::testWebviewCurrentlyDiscardsTheHtmlEncodedClone`
- Category: A
- Proposed fix: Return the encoded clone
- Status: fixed (MOD-126)

### KD-37 — Data mapper

`field_list($instance)` fails before creating its reflection

- Test: `Data/MapperTest::testFieldListWithInstanceCurrentlyFailsBeforeCreatingReflection`
- Category: A
- Proposed fix: Create the reflection for the instance
- Status: fixed (MOD-126)

### KD-38 — Validation failure

The message argument is ignored

- Test: `Data/ErrorDetailsTest::testValidationFailureCollectsOnlyStringFieldsAndCurrentlyIgnoresMessage`
- Category: A
- Proposed fix: Store and return the message
- Status: fixed (MOD-126)

### KD-39 — Payments

Transaction adapter's `isset` guard drops SDK fields initialized to `null`, including `amount`

- Test: `Cloud/AuthorizeTransactionTest::testAdapterRetainsMappedInitializedFieldsAndCurrentNullFieldLimitation`
- Category: A
- Proposed fix: Map every declared SDK field
- Status: fixed (MOD-135)

### KD-40 — Payments

Subscription `create` drops `billTo` (same `isset` rule)

- Test: `Cloud/AuthorizeSubscriptionTest::testCreateMapsItsAmountAndOptionalOrderWithoutGuessingAnIdentifierReturn`
- Category: A
- Proposed fix: Map declared SDK fields
- Status: fixed (MOD-135)

### KD-41 — Payments

Subscription `create` returns `null`; its docblock promises the subscription id

- Test: same test
- Category: B
- Proposed fix: Q14
- Status: fixed (MOD-135)

### KD-42 — Payments

Subscription search pagination offset never advances

- Test: (`../history/PENDING-QUESTIONS.md` row 8)
- Category: A
- Proposed fix: Advance the offset per page
- Status: fixed (MOD-135)

### KD-43 — Payments

Scalar `response_error` ignores its second argument

- Test: `Cloud/AuthorizeTest::testResponseWithoutMessagesAndLegacyScalarErrors`
- Category: B
- Proposed fix: Q15
- Status: fixed (MOD-135)

### KD-44 — Payments

Transaction validation accepts any return except explicit `false`

- Test: `AuthorizeTransactionTest::testExplicitFalseValidationIsRejectedAndOtherReturnTypesCurrentlyPass`
- Category: A
- Proposed fix: Accept only `true`
- Status: fixed (MOD-135)

### KD-45 — Payments

Customer convenience operations (void, refund, authonly, authcapture, captureonly, get_transaction) return `null`

- Test: `Cloud/AuthorizeCustomerTest::testUnimplementedConvenienceOperationsCurrentlyReturnNull`
- Category: C
- Proposed fix: Q16
- Status: fixed (MOD-114)

### KD-46 — IP lookup

Static `load` does nothing

- Test: `Cloud/IpInfoDbTest::testStaticLoadCurrentlyDoesNothing`
- Category: C
- Proposed fix: Q16
- Status: fixed (MOD-114)

### KD-47 — Exceptions

`bad_interface` message drops the class when a method is given and ignores the reason

- Test: `Core/BadInterfaceTest::testMessageFormattingRetainsCurrentMethodAndReasonBehavior`
- Category: A
- Proposed fix: Include class, method and reason
- Status: fixed (MOD-124)

### KD-48 — Exceptions

SSH `auth\not_ready` and `auth\failure\pubkey` replace their prefix with the detail (`": detail"`)

- Test: `LegacyErrorMessagesTest::testErrorsRetainOperationContext` (2 rows)
- Category: A
- Proposed fix: Keep the prefix like the sibling classes
- Status: fixed (MOD-124)

### KD-49 — Exceptions

`duplicate_key` with an array index raises "Array to string conversion"

- Test: `LegacyErrorMessagesTest::testEmptyNonScalarDuplicateIndexUsesItsEmptyDescription`
- Category: A
- Proposed fix: Describe non-scalar indexes like non-scalar values
- Status: fixed (MOD-124)

### KD-50 — Phone

`+1 800 555 1234` stores the whole number as its extension; `ext 42`/`x89` extraction fails

- Test: `Data/PhoneTest::testCurrentCountryOneAndTextExtensionExtractionFailures`
- Category: A
- Proposed fix: Strip country code 1; extract extensions
- Status: fixed (MOD-131)

### KD-51 — Phone

International numbers get no country code for most prefixes (comparisons are assignments)

- Test: `PhoneTest::testCurrentInternationalClassificationLeavesComparisonAssignmentsUnset`
- Category: A
- Proposed fix: Fix the comparisons (no new country table)
- Status: fixed (MOD-131)

### KD-52 — UUID

`uuid::get(2)` without a namespace returns a v4 UUID

- Test: (`../history/TESTING-FOLLOWUPS.md`)
- Category: A
- Proposed fix: Throw `invalid_version`
- Status: fixed (MOD-129)

### KD-53 — Templates

Absolute file resolution converts a null `$file`; failure paths leave output buffers open

- Test: (`../history/TESTING-FOLLOWUPS.md`)
- Category: A
- Proposed fix: Resolve the design path; close buffers on failure
- Status: fixed (MOD-132)

### KD-54 — Templates

`tmpl/display` declares a class extending an interface; loading it aborts PHP

- Test: (`../history/TESTING-FOLLOWUPS.md`)
- Category: A
- Proposed fix: Implement the interface
- Status: already fixed (MOD-040; verified in MOD-132)

### KD-55 — Random strings

Invoking an instance ignores its configured character pool

- Test: (`../history/TESTING-FOLLOWUPS.md`)
- Category: B
- Proposed fix: Q17
- Status: fixed (MOD-129)

### KD-56 — Random numbers

`rand::get(1, 0)`: a zero maximum means "default", so inverted bounds pass

- Test: (`../history/TESTING-FOLLOWUPS.md`)
- Category: B
- Proposed fix: Q18
- Status: confirmed, no change (MOD-129)

### KD-57 — Views

The `view_resolved` event payload uses an undefined `$resolved`

- Test: (follow-ups; source checked 2026-11)
- Category: A
- Proposed fix: Use the matched route
- Status: fixed (MOD-127)

### KD-58 — API controller

A `HEAD` request sets a flag that is never cleared, so a reused controller keeps treating later requests as `HEAD`

- Test: (follow-ups; source checked 2026-11)
- Category: A
- Proposed fix: Reset the flag per request
- Status: fixed (MOD-127)

### KD-59 — API controller

`TRACE` echoes input via `get_as_assoc()`, which cannot see the input map's dynamic properties

- Test: (follow-ups; source checked 2026-11)
- Category: A
- Proposed fix: Echo the actual input fields
- Status: fixed (MOD-127)

### KD-60 — ORM

`delete()` silently does nothing if the object was read first (`_read_done` guard)

- Test: `Orm/LifecycleTest::testDeleteAndTheCurrentReadDoneGuard`
- Category: B
- Proposed fix: Q19
- Status: fixed (MOD-133)

### KD-61 — ORM

`crud_error` stores its details in the operation field

- Test: (follow-ups; not yet verified)
- Category: A
- Proposed fix: Store operation and details separately
- Status: fixed (MOD-133)

### KD-62 — Mongo

`load_client()` without options loops over `null` (warning)

- Test: (follow-ups; source checked 2026-11)
- Category: A
- Proposed fix: Treat missing options as `[]`
- Status: fixed (MOD-133)

### KD-63 — S3

Multipart threshold is 10 GiB though the comment says 10 MB; a single S3 upload cannot exceed 5 GB, so 5 GB to 10 GiB files would fail

- Test: (follow-ups; source checked 2026-11)
- Category: B
- Proposed fix: Q20
- Status: fixed (MOD-135)

### KD-64 — S3

Large (multipart) uploads do not pass on the file's MIME type

- Test: (follow-ups; not yet verified)
- Category: A
- Proposed fix: Pass the content type to the multipart upload
- Status: fixed (MOD-135)

### KD-65 — Data mapper

`dump` HTML-encoding ignores its encoded clone (same pattern as KD-36)

- Test: (follow-ups; not yet verified)
- Category: A
- Proposed fix: Use the encoded clone
- Status: fixed (MOD-126)

### KD-66 — Exceptions

Exception wrapping `print_r`s traces including arguments and can exhaust memory on large objects

- Test: (follow-ups; not yet verified)
- Category: B
- Proposed fix: Q21
- Status: fixed (MOD-135)

### KD-67 — SSH

Temporary key files are not cleaned up when connection setup fails part-way; key encryption uses no explicit cipher

- Test: (follow-ups; not yet verified)
- Category: B
- Proposed fix: Q22
- Status: fixed (MOD-135)

### KD-68 — Validation controller

The validation body is unfinished; it inspects fields and then does nothing

- Test: (follow-ups; not yet verified)
- Category: C
- Proposed fix: Q23
- Status: fixed (MOD-134)

### KD-69 — Data mapper

`field_list()` caches one list for every `flat\data` subclass, so later classes get the first class's fields (and `is_field()` answers wrongly)

- Test: `Data/MapperTest::testFieldListUsesTheInstanceAndCachesPerClass`
- Category: A
- Proposed fix: Cache per class
- Status: fixed (MOD-126)

### KD-70 — cURL config

The constructor's second loop re-assigns every text argument, so unset `request_method`, `user_agent`, `referrer` and `cache_dir` become `null` instead of their declared defaults (`GET`, `flat/curl`)

- Test: `Core/Curl/ConfigTest::testExplicitConfigurationAndNumericDefaults`
- Category: B
- Proposed fix: Q24
- Status: fixed (MOD-136)

### KD-71 — UUID

`uuid::__invoke($version, $namespace, $name)` passed `$version=4, $namespace=null, $name=null` (assignments), so it always returned a random v4 UUID; its test asserted that

- Test: `Core/UuidTest::testInvokeGeneratesTheRequestedVersion`
- Category: A
- Proposed fix: Pass the arguments through
- Status: fixed (MOD-137)

### KD-72 — Random numbers

`$rand()` on an object built with bounds (`new rand(5, 10)`) ignores them and uses 0 to `getrandmax()`; `generate()` uses them

- Test: `Core/RandTest::testInvocationWithoutArgumentsUsesTheConstructedBounds`
- Category: B
- Proposed fix: Q25
- Status: fixed (MOD-140; `$rand()` without arguments now uses the constructed bounds)

### KD-73 — Config

A key starting with the `flat/` namespace removed its last five characters instead of the prefix (`flat/settings/a` became `flat/s`), so such keys never resolved; a test asserted that ("do not correct it here")

- Test: `Core/ConfigTest::testPathsAreNormalizedAndBaseNamesAreRemoved`
- Category: A
- Proposed fix: Remove the prefix from the front
- Status: fixed (MOD-139)

### KD-74 — IP lookup

`ipinfodb::location_ttl = 604800` is commented "1 day" but 604800 seconds is 7 days (a day is 86400)

- Test: `Cloud/IpInfoDbTest::testCachedRecordExpiresExactlyAtItsTimeToLive` (pins the boundary, not the length)
- Category: B
- Proposed fix: Q26
- Status: fixed (MOD-140; comment corrected, 7 days kept)

### KD-75 — Mongo driver

`getMongoDBClient()` guarded its default with `if (!$options===null)`, which parses as `(!$options)===null` and can never be true

- Test: `Db/Mongo/CrudTest::testClientOptionsChooseTheServer`
- Category: A
- Proposed fix: `if ($options===null)` (no behavior change: `isset()` on `null` already worked)
- Status: fixed (MOD-139)

### KD-76 — XML encoding

The XML encoder labels any string `strtotime()` can parse as `xs:DateTime`, including single letters such as `'a'` (a timezone abbreviation)

- Test: `Core/Xml/EncodeTest::testIsoDatesAndTimesAreTypedAsDateTime`, `testOtherTextIsAStringEvenWhenStrtotimeParsesIt`
- Category: B
- Proposed fix: Q27
- Status: fixed (MOD-146; only ISO-8601 style dates and times are typed `xs:DateTime`)

### KD-77 — XML encoding

Any string of hex digits is typed `xs:hexBinary` by `ctype_xdigit()`, including odd lengths such as `'a'` or `'abc'`; `xs:hexBinary` needs an even number of digits. Found when KD-76 stopped hiding it (`'a'` used to be called a date).

- Test: `Core/Xml/EncodeTest::testOnlyWholeBytesOfHexDigitsAreTypedAsHexBinary`
- Category: B
- Proposed fix: Q28
- Status: fixed (MOD-147; only an even number of hex digits is typed `xs:hexBinary`)

## Test-strength tasks

Gaps in what existing tests prove, carried over from
`../history/TESTING-FOLLOWUPS.md`. No behavior change; each adds or
strengthens assertions.

### TS-01 — UUID

`testGenerateUsesConstructorState` only checks a default v4; add configured v3/v5 state

- Status: done (MOD-137)

### TS-02 — Random numbers

No test of invocation without arguments after constructing with custom bounds

- Status: done (MOD-137; found KD-72)

### TS-03 — Input map

No test that `0`, `false` and `"0"` count as empty input

- Status: done (MOD-137)

### TS-04 — CSV

No test that `fields2csv([])` returns a newline

- Status: done (MOD-137)

### TS-05 — Whole suite

Run a broader mutation probe; the 14-mutation sample in MOD-111 found two gaps

- Status: first sample done (MOD-139; [MUTATION-PROBE.md](MUTATION-PROBE.md))

### TS-06 — Whole suite

The 54 survivor lines from the first probe sample are all closed: pinned by tests or shown equivalent (reasons in [MUTATION-PROBE.md](MUTATION-PROBE.md#ts-06-outcome)); further seeds can be run any time

- Status: done (MOD-141)

Reviewed and **not** treated as defects: `scalar` empty-input fallback (owner
contract), Mongo affected-record policy, `app_error` summaries, `input\map`
treating `0`/`false`/`"0"` as empty (TS-03), `csv::fields2csv([])` returning a newline (TS-04), Markdown/HTML/CSV
"not ready" unserializers (explicitly declared unavailable).

## Owner questions

Each has a recommendation; "agree" is enough. Questions 1-24 are answered (the owner
agreed with every recommendation); questions 25 and 26 are open.

1. **KD-03 PDO fetch options.** Recommend: implement the documented meanings
   (true = generic data rows, class = rows as that class, callback = per row,
   false = raw arrays).
   **Answered: agreed** (owner, 2026-11-13).

2. **KD-05 DB `load_read`.** Recommend: call the subclass's `op_read()` when it
   implements `\flat\db\op\read` (the original body already did this).
   **Answered: agreed** (owner, 2026-11-13).

3. **KD-07 API `requestMethod()`.** Recommend: return the method actually
   dispatched to the handler (`GET` for a `HEAD` request served by `GET`).
   **Answered: agreed** (owner, 2026-11-13).

4. **KD-13 HTML specials.** Recommend: emit multiple strings/callbacks in the
   supplied order, with no self-dependency.
   **Answered: agreed** (owner, 2026-11-13).

5. **KD-14 unknown asset types/tags.** Recommend: throw the existing
   `bad_resource`/invalid-type error instead of printing nothing.
   **Answered: agreed** (owner, 2026-11-13).

6. **KD-16 repeated shutdown invocation.** Recommend: each callback runs at
   most once (restore `_clear()` after invocation).
   **Answered: agreed.** Fixed in MOD-112.
7. **KD-19 Yasumi `get_date`.** Recommend: Unix timestamp, set for valid
   dates, and convert to `DateTime` when calling providers.
   **Answered: agreed** (owner, 2026-11-13).

8. **KD-20 validation factory.** Recommend: the default collects
   validator/data pairs, which is what its own helper builds.
   **Answered: agreed** (owner, 2026-11-13).

9. **KD-21 validation options.** Recommend: implement all four documented
   options.
   **Answered: agreed** (owner, 2026-11-13).

10. **KD-22 PCRE engine errors.** Recommend: throw `preg_error` (it already
    explains the native error); `false` hides bad input.
    **Answered: agreed** (owner, 2026-11-13).

11. **KD-23 image constructor with both bounds.** Recommend: treat them as
    minimums, scaling proportionally like `resize_min_proportional`.
    **Answered: agreed** (owner, 2026-11-13).

12. **KD-28 core mapper private access.** Recommend: remove the obsolete
    private-access options and throw a clear error, since you confirmed
    iteration is public-only (MOD-011).
    **Answered: agreed.** Fixed in MOD-113 for the three read paths, which always
    crashed. `set_private_property` already worked and is kept unchanged.
13. **KD-34 legacy ORM constructor array.** Recommend: treat the array as
    driver configuration (resolve the driver from it).
    **Answered: agreed** (owner, 2026-11-13).

14. **KD-41 subscription `create` return.** Recommend: return the gateway's
    subscription id, as the docblock says.
    **Answered: agreed** (owner, 2026-11-13).

15. **KD-43 scalar `response_error`.** Recommend: the second argument is the
    message text; the first stays the code.
    **Answered: agreed** (owner, 2026-11-13).

16. **KD-45/46 unfinished payment conveniences and IP `load`.** Recommend:
    retire them (throw `BadMethodCallException`, as MOD-107 did for alerts)
    unless the consumer uses them.
    **Answered: agreed.** Fixed in MOD-114; the identical empty subscription
    `get_transaction` was retired with them.
17. **KD-55 random string invoke.** Recommend: an instance reuses its own
    configured pool and length.
    **Answered: agreed** (owner, 2026-11-13).

18. **KD-56 `rand::get` zero maximum.** Recommend: keep `0` meaning "default
    maximum", but reject a minimum above the resolved maximum.
    **Answered: agreed** (owner, 2026-11-13).

19. **KD-60 ORM delete after read.** Recommend: deleting a record that was read
    first must work; the guard looks meant to stop a repeated delete, not a
    delete after a read.
    **Answered: agreed** (owner, 2026-11-13).

20. **KD-63 S3 multipart threshold.** Recommend: 100 MB (Amazon's suggested
    multipart size); anything above 5 GB must use multipart regardless.
    **Answered: agreed** (owner, 2026-11-13).

21. **KD-66 exception trace formatting.** Recommend: summarize traces without
    argument values, like `app_error` summaries already do.
    **Answered: agreed** (owner, 2026-11-13).

22. **KD-67 SSH temporary keys.** Recommend: always remove temporary key files
    on failure, and encrypt them with an explicit cipher (AES-256).
    **Answered: agreed** (owner, 2026-11-13).

23. **KD-68 validation controller.** Recommend: retire it (clear exception)
    unless the consumer uses it; nothing in flat calls its unfinished body.
    **Answered: agreed** (owner, 2026-11-13).
24. **KD-70 cURL config defaults.** Restoring every declared default changes
    outgoing requests: requests would send `User-Agent: flat/curl` (today none),
    and requests with a body would get an explicit `GET` method (today `null`, so
    curl sends them as POST). Recommend: restore the `flat/curl` user agent and
    the other text defaults, but leave `request_method` unset unless given, so
    requests with a body keep being sent as POST.
    **Answered: agreed** (owner, 2026-11-13). Fixed in MOD-136.
25. **KD-72 `rand` invocation.** Same question as Q17 for `rand\str`: should
    `$rand()` on an object built with bounds use those bounds? Recommend: yes,
    when called without arguments (explicit arguments still win), so an object
    built with `new rand(5, 10)` behaves the same through `generate()` and
    `$rand()`.
    **Answered: agreed** (owner, 2026-11-14). Fixed in MOD-140.
26. **KD-74 IP location cache lifetime.** The constant is 604800 seconds (7 days)
    but its comment says "1 day". Which is intended? Recommend: keep 7 days (a
    week is a sensible lifetime for IP location data, and a change would send
    seven times as many lookups to the paid external API) and correct the comment.
    **Answered: agreed** (owner, 2026-11-14). Comment corrected in MOD-140.
27. **KD-76 XML `xs:DateTime` label.** The encoder marks a string as `xs:DateTime` whenever
    `strtotime()` accepts it, so `'a'`, `'z'` or `'mon'` come out as dates. Should only
    strings that look like ISO-8601 dates or times get that label? Recommend: yes
    (anything else stays a string); this changes the type label consumers see in XML
    for such strings.
    **Answered: agreed** (owner, 2026-11-14). Fixed in MOD-146.
28. **KD-77 XML `xs:hexBinary` label.** Strings made only of hex digits get
    `xs:hexBinary` whatever their length, so `'a'`, `'abc'` and `'123'`-like text
    (numbers are typed earlier) are labeled binary. Should only an even number of
    hex digits count? Recommend: yes (a valid `xs:hexBinary` has whole bytes), with the
    same consumer-visible effect as KD-76: some strings change from `xs:hexBinary` to
    `xs:string`.
    **Answered: agreed** (owner, 2026-11-14). Fixed in MOD-147.
29. **Functional tests: where they live.** Plan: [WEBAPP-TESTS.md](WEBAPP-TESTS.md).
    Recommend: in this repository in a new Functional folder under the tests folder as a separate
    `functional` suite, with the flat-webapp application fetched at a pinned
    commit at image build time, never copied into the repository.
30. **Functional tests: network at build time.** The pinned application and its
    Composer packages must be downloaded once when the image is built. Recommend:
    accept that (the base images are fetched the same way); the unit suite
    stays offline.
31. **Functional tests: baseline.** `master`, `devel` and `modernize-26` of
    flat-webapp are the same commit today (`7186607`). Recommend: pin that commit
    and revisit only if the branches diverge.

