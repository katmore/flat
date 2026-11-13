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
3. Add a row to [CONSUMER-IMPACT.md](CONSUMER-IMPACT.md): every fix is a silent change for
   consumers that might have depended on, or worked around, the old behavior.
4. Mark the entry here as fixed, with its change-log ID.
5. Regenerate [../TEST-INVENTORY.md](../TEST-INVENTORY.md) with `docker/bin/test inventory`
   (see the phase README for the full command).

## Inventory

| ID | Area | Defect | Test | Cat. | Proposed fix | Status |
| --- | --- | --- | --- | --- | --- | --- |
| KD-01 | PDO statement | Executing without a callback references an undefined data class (`Error`) | `Db/Pdo/StatementTest::testExecutedRowsWithoutACallbackStillReferenceUndefinedDataClass` | A | Map rows like the bare-record path does | open |
| KD-02 | PDO statement | `execute` gates on SELECT `rowCount`, so SQLite reports `not_found` despite returning rows | `StatementTest::testExecuteCurrentlyReportsNotFoundForSqliteSelectRowCount` | A | Fetch rows instead, as MOD-033 did for `prepare_map` | open |
| KD-03 | PDO statement | Every explicit `fetch` option (true/class/callback/false) throws `bad_option` | `StatementTest::testEveryExplicitFetchOptionCurrentlyThrows` | B | Q1 | open |
| KD-04 | Mongo CA file | A failed certificate write returns `null` after a warning | `Db/Mongo/CaFileTest::testFailedFileWriteCurrentlyReturnsNull` | A | Throw an existing system/IO error | open |
| KD-05 | DB controller | `load_read` tests the literal constant `self`, warns and returns `null` | `Db/LegacyControllerTest::testStaticReadCurrentlyWarnsAboutItsLiteralSelfAndReturnsNull` | B | Q2 | open |
| KD-06 | API response | Unknown error with a class caller calls the nonexistent `ReflectionMethod::getClassName` | `Api/ResponseTest::testUnknownErrorWithClassCallerCurrentlyCallsMissingReflectionMethod` | A | Use the declaring class name | fixed (MOD-124) |
| KD-07 | API controller | `requestMethod()` reads a never-assigned field and always throws | `Api/ControllerTest::testUnassignedRequestMethodGetterCurrentlyThrows` | B | Q3 | open |
| KD-08 | API controller | `set_input` without a method raises `unknown_method` instead of `missing_method` | `Api/ControllerTest::testMissingMethodCurrentlyRaisesUnknownMethod` | A | Raise `missing_method` (verify against source first) | open |
| KD-09 | API status | Code-class formatting indexes strings instead of class metadata (8 warnings, placeholders left in output) | `Api/StatusTest::testCurrentFormattingUsesStringOffsetsInsteadOfClassMetadata` | A | Read the class type/description metadata | open |
| KD-10 | API status | `not_found` status reports code 500 | `Core/LegacyErrorMessagesTest::testLegacyNotFoundStatusCurrentlyReportsServerErrorCode` | A | Return 404 | fixed (MOD-124) |
| KD-11 | Asset | Error message says "could resolve" | `LegacyErrorMessagesTest::testErrorsRetainOperationContext` (row `not_resolvable`) | A | "could not resolve" | fixed (MOD-124) |
| KD-12 | Asset | New inline JS source raises `TypeError` looking for a file path | `Asset/AssetTest::testNewInlineSourceCurrentlyCannotResolveAFilePath` | A | Print the inline source | open |
| KD-13 | Asset | Multiple HTML items create a self-dependency (cyclic queue) | `AssetTest::testMultipleHtmlItemsCurrentlyCreateAnAccidentalSelfDependency` | B | Q4 | open |
| KD-14 | Asset | Unknown resource types and the style-source tag produce no output | `AssetTest::testUnimplementedResourceAndTagCasesCurrentlyProduceNoOutput` | C | Q5 | open |
| KD-15 | Shutdown | Integer weight `0` joins the default queue; out-of-range weights (±51) are silently accepted | `Core/ShutdownTest::testZeroAndOutOfRangeWeightsCurrentlyJoinTheDefaultQueue` | A | `0` is a weight (docs: -50 to 50); out of range throws `bad_priority` | open |
| KD-16 | Shutdown | Explicit invocation followed by PHP shutdown runs callbacks twice (`_clear()` was commented out by the original author) | `ShutdownTest::testRepeatedInvocationCurrentlyRepeatsCallbacks` | B | Q6 | fixed (MOD-112) |
| KD-17 | Events | `hookable` drops event data and reads an undefined local | `Core/LegacyListenerTest::testHookableCurrentlyDropsEventDataAndReportsItsUndefinedLocal` | A | Pass the supplied event data to the handler | fixed (MOD-126) |
| KD-18 | Events | `stub::get_listeners` reads an undeclared property | `LegacyListenerTest::testStubCurrentlyReadsASeparateUndeclaredListenerProperty` | A | Return the listeners `add_listener` stored | fixed (MOD-126) |
| KD-19 | Yasumi wrapper | Valid date/year fields stay unset; the provider cache is not attached; providers reject the wrapper's raw timestamp | `Vendor/YasumiDateQueryTest::testValidDateAndExplicitYearCurrentlyLeaveTheirBackingFieldsUnset`, `::testProviderCacheCurrentlyDoesNotAttachItsHitToTheNextInstance`, `::testRealProviderRejectsTheWrappersRawTimestamp` | B | Q7 | open |
| KD-20 | Validation factory | The default constraint rejects the pair its own helper builds | `Core/Util/ValidationFactoryTest::testDefaultConstraintCurrentlyRejectsThePairBuiltByItsHelper` | B | Q8 | open |
| KD-21 | Validation | `value_on_pass`, `value_on_fail`, `exception_on_fail` and `fail_handler` are documented but ignored | `Core/Util/ValidationTest::testNullDefaultOptionsCurrentlyCannotBeOverridden` | B | Q9 | open |
| KD-22 | Validation | A PCRE engine error returns `false` instead of the existing `preg_error` | `ValidationTest::testPcreExecutionErrorCurrentlyReturnsFalse` | B | Q10 | open |
| KD-23 | Image | Constructor with both bounds leaves the image unchanged | `Core/Util/Image/ImageTest::testBothConstructorBoundsCurrentlyLeaveTheImageUnchanged` | B | Q11 | open |
| KD-24 | HTML form | `serialize` builds the form and returns `null` | `Core/Html/FormTest::testSerializeCurrentlyDiscardsItsConstructedForm` | A | Return the built form | fixed (MOD-126) |
| KD-25 | Collection | Data validator and filter run twice per added member | `Core/CollectionTest::testCurrentCallbacksRunTwicePerAddedMember` | A | Run each once | fixed (MOD-126) |
| KD-26 | Map from object | Non-objects give an empty map; objects fail with `ArgumentCountError` | `Core/LegacyMappingTest::testNonObjectInputCurrentlyProducesAnEmptyMap`, `::testObjectMappingCurrentlyOmitsRequiredValueArgument` | A | Pass the missing value argument | fixed (MOD-126) |
| KD-27 | CLI | Missing `$argv` throws an unqualified `flat\core\RuntimeException` (`Error`) | `Core/CliTest::testMissingArgvPreservesCurrentExceptionNamespaceFailure` | A | Throw `\RuntimeException` | fixed (MOD-124) |
| KD-28 | Core mapper | Private-access options fail on the `setAcessible` typo | `Core/MapperTest::testKnownPrivateAccessReflectionTypo` | B | Q12 | fixed (MOD-113) |
| KD-29 | Debug | `data2str` uses the object template for arrays and leaves placeholders for small scalars | `Core/DebugTest::testDataDescriptionsCharacterizeCurrentFormatting` | A | Use the matching template and fill it | open |
| KD-30 | Debug | `kill` options overwrite the options array and read an undefined `fh_options` | (`../history/TESTING-FOLLOWUPS.md`; not yet asserted) | A | Keep the options array; define the handle options | open |
| KD-31 | cURL response | Missing curl info throws a nonexistent exception class (`Error`) | `Core/Curl/ResponseTest::testCurrentMissingInfoExceptionClassFailure` | A | Declare/use the exception | fixed (MOD-124) |
| KD-32 | cURL config | Selecting the Chrome user agent is overwritten by the raw selector (`0`) | `Core/Curl/ConfigTest::testCurrentChromeSelectionIsOverwrittenByRawSelector` | A | Keep the selected user-agent string | open |
| KD-33 | Legacy ORM | The static `get_data` facade discards its result | `Data/Orm/LegacyOrmTest::testDefaultFacadeUsesRealNativeSessionsWhileRetainingItsVoidGetResult` | A | Return the stored data | open |
| KD-34 | Legacy ORM | A nonempty constructor array cannot act as a driver | `LegacyOrmTest::testNonemptyConstructorArrayStillCannotActAsADriver` | B | Q13 | open |
| KD-35 | ORM session driver | `destroy` regenerates the id after ending the session (warning) | `Data/Orm/SessionDriverTest::testDestroyCurrentlyAttemptsRegenerationAfterEndingTheSession` | A | Regenerate before destroying, or not at all | open |
| KD-36 | Data mapper | `webview` discards its HTML-encoded clone | `Data/MapperTest::testWebviewCurrentlyDiscardsTheHtmlEncodedClone` | A | Return the encoded clone | fixed (MOD-126) |
| KD-37 | Data mapper | `field_list($instance)` fails before creating its reflection | `Data/MapperTest::testFieldListWithInstanceCurrentlyFailsBeforeCreatingReflection` | A | Create the reflection for the instance | fixed (MOD-126) |
| KD-38 | Validation failure | The message argument is ignored | `Data/ErrorDetailsTest::testValidationFailureCollectsOnlyStringFieldsAndCurrentlyIgnoresMessage` | A | Store and return the message | fixed (MOD-126) |
| KD-39 | Payments | Transaction adapter's `isset` guard drops SDK fields initialized to `null`, including `amount` | `Cloud/AuthorizeTransactionTest::testAdapterRetainsMappedInitializedFieldsAndCurrentNullFieldLimitation` | A | Map every declared SDK field | open |
| KD-40 | Payments | Subscription `create` drops `billTo` (same `isset` rule) | `Cloud/AuthorizeSubscriptionTest::testCreateMapsItsAmountAndOptionalOrderWithoutGuessingAnIdentifierReturn` | A | Map declared SDK fields | open |
| KD-41 | Payments | Subscription `create` returns `null`; its docblock promises the subscription id | same test | B | Q14 | open |
| KD-42 | Payments | Subscription search pagination offset never advances | (`../history/PENDING-QUESTIONS.md` row 8) | A | Advance the offset per page | open |
| KD-43 | Payments | Scalar `response_error` ignores its second argument | `Cloud/AuthorizeTest::testResponseWithoutMessagesAndLegacyScalarErrors` | B | Q15 | open |
| KD-44 | Payments | Transaction validation accepts any return except explicit `false` | `AuthorizeTransactionTest::testExplicitFalseValidationIsRejectedAndOtherReturnTypesCurrentlyPass` | A | Accept only `true` | open |
| KD-45 | Payments | Customer convenience operations (void, refund, authonly, authcapture, captureonly, get_transaction) return `null` | `Cloud/AuthorizeCustomerTest::testUnimplementedConvenienceOperationsCurrentlyReturnNull` | C | Q16 | fixed (MOD-114) |
| KD-46 | IP lookup | Static `load` does nothing | `Cloud/IpInfoDbTest::testStaticLoadCurrentlyDoesNothing` | C | Q16 | fixed (MOD-114) |
| KD-47 | Exceptions | `bad_interface` message drops the class when a method is given and ignores the reason | `Core/BadInterfaceTest::testMessageFormattingRetainsCurrentMethodAndReasonBehavior` | A | Include class, method and reason | fixed (MOD-124) |
| KD-48 | Exceptions | SSH `auth\not_ready` and `auth\failure\pubkey` replace their prefix with the detail (`": detail"`) | `LegacyErrorMessagesTest::testErrorsRetainOperationContext` (2 rows) | A | Keep the prefix like the sibling classes | fixed (MOD-124) |
| KD-49 | Exceptions | `duplicate_key` with an array index raises "Array to string conversion" | `LegacyErrorMessagesTest::testEmptyNonScalarDuplicateIndexUsesItsEmptyDescription` | A | Describe non-scalar indexes like non-scalar values | fixed (MOD-124) |
| KD-50 | Phone | `+1 800 555 1234` stores the whole number as its extension; `ext 42`/`x89` extraction fails | `Data/PhoneTest::testCurrentCountryOneAndTextExtensionExtractionFailures` | A | Strip country code 1; extract extensions | open |
| KD-51 | Phone | International numbers get no country code for most prefixes (comparisons are assignments) | `PhoneTest::testCurrentInternationalClassificationLeavesComparisonAssignmentsUnset` | A | Fix the comparisons (no new country table) | open |
| KD-52 | UUID | `uuid::get(2)` without a namespace returns a v4 UUID | (`../history/TESTING-FOLLOWUPS.md`) | A | Throw `invalid_version` | open |
| KD-53 | Templates | Absolute file resolution converts a null `$file`; failure paths leave output buffers open | (`../history/TESTING-FOLLOWUPS.md`) | A | Resolve the design path; close buffers on failure | open |
| KD-54 | Templates | `tmpl/display` declares a class extending an interface; loading it aborts PHP | (`../history/TESTING-FOLLOWUPS.md`) | A | Implement the interface | open |
| KD-55 | Random strings | Invoking an instance ignores its configured character pool | (`../history/TESTING-FOLLOWUPS.md`) | B | Q17 | open |
| KD-56 | Random numbers | `rand::get(1, 0)`: a zero maximum means "default", so inverted bounds pass | (`../history/TESTING-FOLLOWUPS.md`) | B | Q18 | open |

| KD-57 | Views | The `view_resolved` event payload uses an undefined `$resolved` | (follow-ups; source checked 2026-11) | A | Use the matched route | open |
| KD-58 | API controller | A `HEAD` request sets a flag that is never cleared, so a reused controller keeps treating later requests as `HEAD` | (follow-ups; source checked 2026-11) | A | Reset the flag per request | open |
| KD-59 | API controller | `TRACE` echoes input via `get_as_assoc()`, which cannot see the input map's dynamic properties | (follow-ups; source checked 2026-11) | A | Echo the actual input fields | open |
| KD-60 | ORM | `delete()` silently does nothing if the object was read first (`_read_done` guard) | `Orm/LifecycleTest::testDeleteAndTheCurrentReadDoneGuard` | B | Q19 | open |
| KD-61 | ORM | `crud_error` stores its details in the operation field | (follow-ups; not yet verified) | A | Store operation and details separately | open |
| KD-62 | Mongo | `load_client()` without options loops over `null` (warning) | (follow-ups; source checked 2026-11) | A | Treat missing options as `[]` | open |
| KD-63 | S3 | Multipart threshold is 10 GiB though the comment says 10 MB; a single S3 upload cannot exceed 5 GB, so 5 GB to 10 GiB files would fail | (follow-ups; source checked 2026-11) | B | Q20 | open |
| KD-64 | S3 | Large (multipart) uploads do not pass on the file's MIME type | (follow-ups; not yet verified) | A | Pass the content type to the multipart upload | open |
| KD-65 | Data mapper | `dump` HTML-encoding ignores its encoded clone (same pattern as KD-36) | (follow-ups; not yet verified) | A | Use the encoded clone | fixed (MOD-126) |
| KD-66 | Exceptions | Exception wrapping `print_r`s traces including arguments and can exhaust memory on large objects | (follow-ups; not yet verified) | B | Q21 | open |
| KD-67 | SSH | Temporary key files are not cleaned up when connection setup fails part-way; key encryption uses no explicit cipher | (follow-ups; not yet verified) | B | Q22 | open |
| KD-68 | Validation controller | The validation body is unfinished; it inspects fields and then does nothing | (follow-ups; not yet verified) | C | Q23 | open |
| KD-69 | Data mapper | `field_list()` caches one list for every `flat\data` subclass, so later classes get the first class's fields (and `is_field()` answers wrongly) | `Data/MapperTest::testFieldListUsesTheInstanceAndCachesPerClass` | A | Cache per class | fixed (MOD-126) |

## Test-strength tasks

Gaps in what existing tests prove, carried over from
`../history/TESTING-FOLLOWUPS.md`. No behavior change; each adds or
strengthens assertions.

| ID | Area | Gap | Status |
| --- | --- | --- | --- |
| TS-01 | UUID | `testGenerateUsesConstructorState` only checks a default v4; add configured v3/v5 state | open |
| TS-02 | Random numbers | No test of invocation without arguments after constructing with custom bounds | open |
| TS-03 | Input map | No test that `0`, `false` and `"0"` count as empty input | open |
| TS-04 | CSV | No test that `fields2csv([])` returns a newline | open |
| TS-05 | Whole suite | Run a broader mutation probe; the 14-mutation sample in MOD-111 found two gaps | open |

Reviewed and **not** treated as defects: `scalar` empty-input fallback (owner
contract), Mongo affected-record policy, `app_error` summaries, `input\map`
treating `0`/`false`/`"0"` as empty (TS-03), `csv::fields2csv([])` returning a newline (TS-04), Markdown/HTML/CSV
"not ready" unserializers (explicitly declared unavailable).

## Owner questions

Each has a recommendation; "agree" is enough. All 23 are answered: the owner
agreed with every recommendation.

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
