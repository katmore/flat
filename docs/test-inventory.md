# Test inventory

Every PHPUnit test method in `tests/Unit`, grouped by file, for review.
Generated from the test sources; regenerate it after tests change rather than
editing it by hand.

- 704 test methods in 92 files. 118 use a data provider, so the
  suite runs more cases than methods.
- 46 methods currently assert defective behavior (`@group known-defect`);
  their IDs refer to `docs/known-defects.md`.
- "Reads as" splits the method name into words; the name is the test's claim.

## Files

### `tests/Unit/Api/ControllerTest.php` (14)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 35 | `testMethodDispatchHeadTraceAndNoInterface` | Method Dispatch Head Trace And No Interface |  |
| 47 | `testAnyInterfaceFallsBackToSpecificMethodOrUsesItsOwnResponse` | Any Interface Falls Back To Specific Method Or Uses Its Own Response |  |
| 59 | `testResponseHandlerCanObserveOrReplaceResponses` | Response Handler Can Observe Or Replace Responses |  |
| 68 | `testConstructorMapsInputsAndInvokesSuccessAndAlwaysCallbacks` | Constructor Maps Inputs And Invokes Success And Always Callbacks | data provider |
| 77 | `testStringMethodConstructorAndDirectCallback` | String Method Constructor And Direct Callback |  |
| 84 | `testResponseExceptionsFromHandlerReachConstructorErrorAndAlwaysCallbacks` | Response Exceptions From Handler Reach Constructor Error And Always Callbacks |  |
| 94 | `testReturnedErrorResponseSuppliesItsOwnMetadata` | Returned Error Response Supplies Its Own Metadata |  |
| 101 | `testNonErrorFailureStatusUsesNullOptionalErrorMetadata` | Non Error Failure Status Uses Null Optional Error Metadata |  |
| 108 | `testOrdinaryHandledExceptionReturnsWithoutAnAlwaysResponse` | Ordinary Handled Exception Returns Without An Always Response |  |
| 114 | `testUnhandledConstructorExceptionPropagates` | Unhandled Constructor Exception Propagates |  |
| 119 | `testInvalidResponseIsRejected` | Invalid Response Is Rejected |  |
| 126 | `testUnassignedRequestMethodGetterCurrentlyThrows` | Unassigned Request Method Getter Currently Throws | **known defect KD-07** |
| 131 | `testInvalidFallbackMethodIsRejected` | Invalid Fallback Method Is Rejected |  |
| 136 | `testMissingMethodCurrentlyRaisesUnknownMethod` | Missing Method Currently Raises Unknown Method | **known defect KD-08** |

### `tests/Unit/Api/ResponseTest.php` (11)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 22 | `testSuccessAndExplicitStatusMapping` | Success And Explicit Status Mapping |  |
| 40 | `testErrorMessagesDataAndStatusCodes` | Error Messages Data And Status Codes | data provider |
| 56 | `testNotFoundPreservesFalsyAndAlreadyMessagedPayloads` | Not Found Preserves Falsy And Already Messaged Payloads |  |
| 65 | `testBadRequestAddsMessageToArrayOrObjectWithoutOverwritingIt` | Bad Request Adds Message To Array Or Object Without Overwriting It |  |
| 73 | `testResponseExceptionsPreserveTheResponseAndStatus` | Response Exceptions Preserve The Response And Status |  |
| 84 | `testUnknownErrorWithoutClassCallerGetsGenericMessage` | Unknown Error Without Class Caller Gets Generic Message |  |
| 91 | `testUnknownErrorWithClassCallerCurrentlyCallsMissingReflectionMethod` | Unknown Error With Class Caller Currently Calls Missing Reflection Method | **known defect KD-06** |
| 97 | `testMissingStatusReportsTheResponseAndInvalidValue` | Missing Status Reports The Response And Invalid Value |  |
| 106 | `testHeadersExposeFieldAndValueAndReplaceDuplicateFields` | Headers Expose Field And Value And Replace Duplicate Fields |  |
| 123 | `testAnyMethodFallbackResourcesReturnTheirStatusAndMessage` | Any Method Fallback Resources Return Their Status And Message |  |
| 133 | `testEmptyHeaderFieldThrowsItsDeclaredLegacyException` | Empty Header Field Throws Its Declared Legacy Exception |  |

### `tests/Unit/Api/StatusTest.php` (3)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 17 | `testConcreteStatusesProvideExpectedCodeAndMessage` | Concrete Statuses Provide Expected Code And Message | data provider |
| 33 | `testCodeClassesAndNumericInputBoundaries` | Code Classes And Numeric Input Boundaries |  |
| 47 | `testCurrentFormattingUsesStringOffsetsInsteadOfClassMetadata` | Current Formatting Uses String Offsets Instead Of Class Metadata | **known defect KD-09** |

### `tests/Unit/Asset/AssetTest.php` (22)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 66 | `testSocialProfileAssetsResolveUserBasesAndIgnoreResources` | Social Profile Assets Resolve User Bases And Ignore Resources |  |
| 80 | `testUrlsResourcesHashesAndInvocation` | Urls Resources Hashes And Invocation |  |
| 100 | `testExplicitHashesAndInlineScriptSource` | Explicit Hashes And Inline Script Source |  |
| 115 | `testFilesystemPathsExistenceAndDataUris` | Filesystem Paths Existence And Data Uris |  |
| 127 | `testFilesystemPathFailures` | Filesystem Path Failures | data provider |
| 143 | `testLinkedTagsFileContentsAndDeprecatedAliases` | Linked Tags File Contents And Deprecated Aliases |  |
| 156 | `testInvalidFileContentTypeIsRejected` | Invalid File Content Type Is Rejected |  |
| 163 | `testDirectoryFlagsRequireDirectoriesAndAcceptRealDirectories` | Directory Flags Require Directories And Accept Real Directories |  |
| 172 | `testOverriddenSystemPathsAndReadFailuresRemainGuarded` | Overridden System Paths And Read Failures Remain Guarded | data provider |
| 181 | `testExplicitCssContentAndExtensionFallbackTags` | Explicit Css Content And Extension Fallback Tags |  |
| 186 | `testHtmlSpecialAcceptsImplicitPositionAndSingleItemsWithoutExecutingCycles` | Html Special Accepts Implicit Position And Single Items Without Executing Cycles |  |
| 201 | `testUnimplementedResourceAndTagCasesCurrentlyProduceNoOutput` | Unimplemented Resource And Tag Cases Currently Produce No Output | **known defect KD-14** |
| 206 | `testExplicitOutputAssetsResourcesAndCallbacksAreRegistered` | Explicit Output Assets Resources And Callbacks Are Registered |  |
| 216 | `testRegistrationsDeduplicateAndRespectResourceAndPositionFilters` | Registrations Deduplicate And Respect Resource And Position Filters |  |
| 232 | `testInvalidRegistrationPositionFails` | Invalid Registration Position Fails | data provider |
| 238 | `testDependenciesRunBeforeParentsAndAreOnlyEmittedOnce` | Dependencies Run Before Parents And Are Only Emitted Once |  |
| 251 | `testHtmlCallbacksAndArraysAreRegistered` | Html Callbacks And Arrays Are Registered |  |
| 263 | `testMultipleHtmlItemsCurrentlyCreateAnAccidentalSelfDependency` | Multiple Html Items Currently Create An Accidental Self Dependency | **known defect KD-13** |
| 272 | `testNewInlineSourceCurrentlyCannotResolveAFilePath` | New Inline Source Currently Cannot Resolve A File Path | **known defect KD-12** |
| 277 | `testBuiltinDependenciesAreRegisteredOncePerAssetClass` | Builtin Dependencies Are Registered Once Per Asset Class |  |
| 283 | `testResolvedHandlerCanRewriteResources` | Resolved Handler Can Rewrite Resources |  |
| 290 | `testStringConversionReportsUrlFailureAsNotice` | String Conversion Reports Url Failure As Notice |  |

### `tests/Unit/Asset/CascadeTest.php` (4)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 31 | `testFullAndAncestorCascadeClassesResolve` | Full And Ancestor Cascade Classes Resolve |  |
| 46 | `testControllerResolutionSubstitutesOnlyInstantiableClasses` | Controller Resolution Substitutes Only Instantiable Classes |  |
| 57 | `testArrayDependencyGroupsPreserveTheirOrder` | Array Dependency Groups Preserve Their Order |  |
| 63 | `testNestedDependencyInputsAndParameterForms` | Nested Dependency Inputs And Parameter Forms |  |

### `tests/Unit/Asset/LegacyOptionsTest.php` (2)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 12 | `testFlagsSupportListAndAssociativeForms` | Flags Support List And Associative Forms |  |
| 22 | `testOptionsReturnPresentValuesOrFallback` | Options Return Present Values Or Fallback |  |

### `tests/Unit/Cloud/AuthorizeCustomerTest.php` (14)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 62 | `testNativeLegacyClientIsPreparedAndCachedWhenSubclassProvidesTheHook` | Native Legacy Client Is Prepared And Cached When Subclass Provides The Hook |  |
| 73 | `testGetMapsProfileAndMissingProfileReturnsNull` | Get Maps Profile And Missing Profile Returns Null |  |
| 84 | `testProfileAliasDoesNotReplaceExplicitId` | Profile Alias Does Not Replace Explicit Id |  |
| 93 | `testCreateForwardsValidationMode` | Create Forwards Validation Mode | data provider |
| 111 | `testExplicitPaymentObjectIsForwarded` | Explicit Payment Object Is Forwarded |  |
| 119 | `testInvalidRequestsStopBeforeClientCalls` | Invalid Requests Stop Before Client Calls | data provider |
| 148 | `testUpdateAndDeleteForwardIds` | Update And Delete Forward Ids | data provider |
| 160 | `testClientRejectionBecomesResponseError` | Client Rejection Becomes Response Error | data provider |
| 174 | `testRetiredConvenienceOperationsFailClearlyWithoutCallingTheGateway` | Retired Convenience Operations Fail Clearly Without Calling The Gateway | data provider |
| 197 | `testPaymentMethodCreationAndUpdateForwardValidatedIds` | Payment Method Creation And Update Forward Validated Ids |  |
| 207 | `testMissingPaymentIdDoesNotRaiseAnIndexZeroNotice` | Missing Payment Id Does Not Raise An Index Zero Notice |  |
| 217 | `testPaymentMethodRejectionUsesExistingResponseError` | Payment Method Rejection Uses Existing Response Error | data provider |
| 228 | `testTransactionSpecificationsNormalizeAndReachTheClient` | Transaction Specifications Normalize And Reach The Client | data provider |
| 254 | `testTransactionClientRejectionIsPropagatedBeforeDecoding` | Transaction Client Rejection Is Propagated Before Decoding |  |

### `tests/Unit/Cloud/AuthorizeDataTest.php` (12)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 17 | `testIntervalNormalizesScalarLengths` | Interval Normalizes Scalar Lengths | data provider |
| 24 | `testScheduleDefaultsAndScalarOccurrenceNormalization` | Schedule Defaults And Scalar Occurrence Normalization | data provider |
| 33 | `testNewScheduleUsesCurrentTimezoneAndInvalidTimezoneClearsDate` | New Schedule Uses Current Timezone And Invalid Timezone Clears Date |  |
| 43 | `testValidTimezoneConversionUsesNativeDateTimeZone` | Valid Timezone Conversion Uses Native Date Time Zone |  |
| 48 | `testSubscriptionCacheRetainsMappedMetadata` | Subscription Cache Retains Mapped Metadata |  |
| 53 | `testSubscriptionErrorsRetainTheirIdentifiersAndLegacySpellings` | Subscription Errors Retain Their Identifiers And Legacy Spellings |  |
| 63 | `testSubscriptionPaymentAdaptersCopyFieldsIntoRealSdkDtos` | Subscription Payment Adapters Copy Fields Into Real Sdk Dtos |  |
| 87 | `testResponseSubclassCanSupplyStatusInteger` | Response Subclass Can Supply Status Integer | data provider |
| 97 | `testFailureDetailsComeFromSdkUnlessAlreadyProvided` | Failure Details Come From Sdk Unless Already Provided |  |
| 109 | `testUnexpectedResponseRetainsOptionalDetails` | Unexpected Response Retains Optional Details |  |
| 117 | `testFactoryMapsActualSdkCodesToResponseKindsAndStatus` | Factory Maps Actual Sdk Codes To Response Kinds And Status | data provider |
| 129 | `testUnknownSdkResponseCodeStillReturnsNull` | Unknown Sdk Response Code Still Returns Null |  |

### `tests/Unit/Cloud/AuthorizeSubscriptionTest.php` (18)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 61 | `testNativeArbConstructionUsesTheApplicationPreparationHookAndCachesItsClient` | Native Arb Construction Uses The Application Preparation Hook And Caches Its Client |  |
| 69 | `testExistingSubscriptionOperationsRequireTheirIdentifier` | Existing Subscription Operations Require Their Identifier | data provider |
| 75 | `testUpdateAmountAndCancelPassValidatedIdentifiersToTheirClient` | Update Amount And Cancel Pass Validated Identifiers To Their Client |  |
| 84 | `testRetiredGetTransactionFailsClearlyWithoutCallingTheGateway` | Retired Get Transaction Fails Clearly Without Calling The Gateway |  |
| 93 | `testUpdateAmountRetainsItsExistingIntegerRestriction` | Update Amount Retains Its Existing Integer Restriction | data provider |
| 99 | `testStatusAndUpdatabilityUseTheExistingStatusMethod` | Status And Updatability Use The Existing Status Method | data provider |
| 107 | `testGatewayRejectionsUseTheDeclaredResponseError` | Gateway Rejections Use The Declared Response Error | data provider |
| 118 | `testPaymentFormsProduceRealSdkSubscriptionObjects` | Payment Forms Produce Real Sdk Subscription Objects | data provider |
| 135 | `testInvalidPaymentShapesFailBeforeClientTransmission` | Invalid Payment Shapes Fail Before Client Transmission | data provider |
| 149 | `testCreateMapsItsAmountAndOptionalOrderWithoutGuessingAnIdentifierReturn` | Create Maps Its Amount And Optional Order Without Guessing An Identifier Return | **known defect KD-40** |
| 161 | `testCreateRejectsMissingOrMalformedDocumentedFields` | Create Rejects Missing Or Malformed Documented Fields | data provider |
| 172 | `testCreateAcceptsAnExistingScheduleObject` | Create Accepts An Existing Schedule Object |  |
| 178 | `testInfoSurfacesAListRejectionAfterSuccessfulStatusLookup` | Info Surfaces A List Rejection After Successful Status Lookup |  |
| 184 | `testInfoStopsAfterFullPageExceedsDeclaredTotal` | Info Stops After Full Page Exceeds Declared Total |  |
| 190 | `testInfoCircuitBreakerBoundsRepeatedFullPages` | Info Circuit Breaker Bounds Repeated Full Pages |  |
| 200 | `testInfoFindsASubscriptionInActualXmlDetails` | Info Finds A Subscription In Actual Xml Details |  |
| 207 | `testInfoMissingTotalUsesUnexpectedResponseWithoutAnIndexZeroWarning` | Info Missing Total Uses Unexpected Response Without An Index Zero Warning |  |
| 212 | `testInactiveInfoWithoutAMatchUsesNotFound` | Inactive Info Without A Match Uses Not Found |  |

### `tests/Unit/Cloud/AuthorizeTest.php` (7)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 45 | `testAuthenticationUsesSubclassCredentials` | Authentication Uses Subclass Credentials |  |
| 53 | `testSuccessfulResponseIsReturnedAndEndpointPassedToController` | Successful Response Is Returned And Endpoint Passed To Controller |  |
| 60 | `testRejectedResponseRetainsSdkMessages` | Rejected Response Retains Sdk Messages |  |
| 74 | `testNullResponseBecomesResponseError` | Null Response Becomes Response Error |  |
| 85 | `testControllerExceptionIsWrappedUsingExistingDiagnosticFormat` | Controller Exception Is Wrapped Using Existing Diagnostic Format |  |
| 95 | `testResponseWithoutMessagesAndLegacyScalarErrors` | Response Without Messages And Legacy Scalar Errors | **known defect KD-43** |
| 108 | `testParameterErrorsExposeDetails` | Parameter Errors Expose Details |  |

### `tests/Unit/Cloud/AuthorizeTransactionTest.php` (6)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 17 | `testTransactionKindsAreLoadableTypedData` | Transaction Kinds Are Loadable Typed Data | data provider |
| 27 | `testLegacyClassNameHookStillUsesItsOriginalCustomerPrefix` | Legacy Class Name Hook Still Uses Its Original Customer Prefix |  |
| 32 | `testSdkAdaptersAcceptOnlyTheirMatchingType` | Sdk Adapters Accept Only Their Matching Type | data provider |
| 46 | `testAdapterRetainsMappedInitializedFieldsAndCurrentNullFieldLimitation` | Adapter Retains Mapped Initialized Fields And Current Null Field Limitation | **known defect KD-39** |
| 56 | `testExplicitFalseValidationIsRejectedAndOtherReturnTypesCurrentlyPass` | Explicit False Validation Is Rejected And Other Return Types Currently Pass | **known defect KD-44** |
| 66 | `testLegacyPaymentTypeNamesAutoloadWithoutRenamingTheirImplementations` | Legacy Payment Type Names Autoload Without Renaming Their Implementations |  |

### `tests/Unit/Cloud/AwsConfigTest.php` (1)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 21 | `testS3ClientConfigurationUsesRealSdkWithoutMakingRequests` | S3 Client Configuration Uses Real Sdk Without Making Requests |  |

### `tests/Unit/Cloud/AwsTest.php` (8)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 41 | `testExplicitClientIsCheckedAndCached` | Explicit Client Is Checked And Cached |  |
| 47 | `testProviderClientIsResolvedOnce` | Provider Client Is Resolved Once |  |
| 53 | `testMissingClientReportsUnavailable` | Missing Client Reports Unavailable |  |
| 58 | `testBodyUploadCanonicalizesKeysAndWaitsForTheUploadedObject` | Body Upload Canonicalizes Keys And Waits For The Uploaded Object | data provider |
| 65 | `testSmallFileUploadUsesFileAndOptionalContentType` | Small File Upload Uses File And Optional Content Type |  |
| 76 | `testFileUploadRejectsInvalidPathsBeforeClientCalls` | File Upload Rejects Invalid Paths Before Client Calls | data provider |
| 88 | `testS3ParameterMappingUrlsAndBodySetterUsingAnExplicitClientBoundary` | S3 Parameter Mapping Urls And Body Setter Using An Explicit Client Boundary |  |
| 101 | `testConstructorUploadAndFileSetterUseTheSameBoundary` | Constructor Upload And File Setter Use The Same Boundary |  |

### `tests/Unit/Cloud/IpInfoDbTest.php` (5)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 40 | `testCacheHitUsesStoredRecordWithoutHttp` | Cache Hit Uses Stored Record Without Http |  |
| 53 | `testMissingOrExpiredCacheFetchesMapsAndStoresResponse` | Missing Or Expired Cache Fetches Maps And Stores Response | data provider |
| 73 | `testHttpFailureCarriesStatusContext` | Http Failure Carries Status Context |  |
| 85 | `testTransportFailureCarriesCurlContext` | Transport Failure Carries Curl Context |  |
| 98 | `testRetiredStaticLoadFailsClearlyWithoutTouchingTheCache` | Retired Static Load Fails Clearly Without Touching The Cache |  |

### `tests/Unit/Cloud/S3MultipartTest.php` (3)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 49 | `testThresholdRoutesToRealSdkMultipartWorkflow` | Threshold Routes To Real Sdk Multipart Workflow |  |
| 59 | `testPartFailureAbortsTheStartedUploadAndWrapsSdkFailure` | Part Failure Aborts The Started Upload And Wraps Sdk Failure |  |
| 72 | `testInitiationFailureDoesNotAttemptAbortWithoutAnUploadId` | Initiation Failure Does Not Attempt Abort Without An Upload Id |  |

### `tests/Unit/Cloud/TwitterUserInfoTest.php` (2)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 6 | `testKnownFieldsAreMappedAndExcludedFromExtraFields` | Known Fields Are Mapped And Excluded From Extra Fields |  |
| 14 | `testFieldsAddedAfterConstructionReturnNamesOrValues` | Fields Added After Construction Return Names Or Values |  |

### `tests/Unit/Core/AlertDefaultsTest.php` (2)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 42 | `testDefaultBrokerLoadsItsConfigurationAndUsesTheRealLocalRabbitService` | Default Broker Loads Its Configuration And Uses The Real Local Rabbit Service |  |
| 47 | `testSubscriptionStorageRequiresAnExplicitProvider` | Subscription Storage Requires An Explicit Provider |  |

### `tests/Unit/Core/AlertTest.php` (6)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 52 | `testQueueProviderSuffixAndFallback` | Queue Provider Suffix And Fallback |  |
| 61 | `testSubscriptionsStoreExpirationAndUniqueId` | Subscriptions Store Expiration And Unique Id | data provider |
| 76 | `testListenerDecodesMessagesAndRefreshesExpiryOnTimeout` | Listener Decodes Messages And Refreshes Expiry On Timeout | data provider |
| 92 | `testRetiredPublisherFailsBeforeInvokingCallbacksOrChangingSubscriptions` | Retired Publisher Fails Before Invoking Callbacks Or Changing Subscriptions |  |
| 101 | `testRetiredPthreadScaffoldingNoLongerDeclaresAThreadClass` | Retired Pthread Scaffolding No Longer Declares A Thread Class |  |
| 105 | `testUnimplementedLogAndSubscriptionListenerMethodsAreNoOps` | Unimplemented Log And Subscription Listener Methods Are No Ops |  |

### `tests/Unit/Core/BadInterfaceTest.php` (2)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 6 | `testBothInterfaceNamesAutoloadAndRemainCompatible` | Both Interface Names Autoload And Remain Compatible |  |
| 16 | `testMessageFormattingRetainsCurrentMethodAndReasonBehavior` | Message Formatting Retains Current Method And Reason Behavior | **known defect KD-47** |

### `tests/Unit/Core/CliTest.php` (12)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 30 | `testUninitializedAndEmptyArguments` | Uninitialized And Empty Arguments |  |
| 41 | `testArgumentsSwitchesAndCommandOverride` | Arguments Switches And Command Override |  |
| 55 | `testOptionFormsAndAliases` | Option Forms And Aliases |  |
| 65 | `testEnumerationsMatchDocumentedExample` | Enumerations Match Documented Example |  |
| 73 | `testInputLineCallbacks` | Input Line Callbacks |  |
| 81 | `testWrappedLinesAndWidthHandlers` | Wrapped Lines And Width Handlers |  |
| 91 | `testPrintAndDumpHandlers` | Print And Dump Handlers |  |
| 99 | `testDefaultPrintAndDumpOutput` | Default Print And Dump Output |  |
| 108 | `testLineAndErrorHandlers` | Line And Error Handlers |  |
| 117 | `testNormalQuietAndErrorOutput` | Normal Quiet And Error Output |  |
| 124 | `testMissingArgvPreservesCurrentExceptionNamespaceFailure` | Missing Argv Preserves Current Exception Namespace Failure | **known defect KD-27** |
| 132 | `testGetoptWrapperUsesTheReturnedOperandIndex` | Getopt Wrapper Uses The Returned Operand Index |  |

### `tests/Unit/Core/Collection/ConstraintTest.php` (9)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 29 | `testClassAndInterfaceConstraintsAcceptMatchingMembers` | Class And Interface Constraints Accept Matching Members |  |
| 39 | `testConstraintsRejectWrongMember` | Constraints Reject Wrong Member | data provider |
| 46 | `testMalformedConstraintsAreRejected` | Malformed Constraints Are Rejected | data provider |
| 57 | `testInactiveControllersHaveNoChecks` | Inactive Controllers Have No Checks |  |
| 67 | `testValidationPassesAndSuccessReceivesFilteredValue` | Validation Passes And Success Receives Filtered Value | data provider |
| 76 | `testNullFilterLeavesValueUnchanged` | Null Filter Leaves Value Unchanged |  |
| 83 | `testValidationRejectsFailureResults` | Validation Rejects Failure Results | data provider |
| 90 | `testValidationExceptionIsPropagatedUnchanged` | Validation Exception Is Propagated Unchanged |  |
| 97 | `testValidationAndFilterMayIgnoreMembers` | Validation And Filter May Ignore Members |  |

### `tests/Unit/Core/CollectionTest.php` (15)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 11 | `testEmptyAndPopulatedIteration` | Empty And Populated Iteration |  |
| 27 | `testUnindexedMembersAndIgnoredMembers` | Unindexed Members And Ignored Members |  |
| 34 | `testReplacingMembersCopiesWrappersAndHonorsMatchingIndexLength` | Replacing Members Copies Wrappers And Honors Matching Index Length |  |
| 47 | `testRangeLimitNormalizationAndCallbacks` | Range Limit Normalization And Callbacks |  |
| 60 | `testEmptyRangeRaisesOutOfRange` | Empty Range Raises Out Of Range |  |
| 67 | `testCurrentCallbacksRunTwicePerAddedMember` | Current Callbacks Run Twice Per Added Member | **known defect KD-25** |
| 76 | `testFilterCanIgnoreOnEitherInvocation` | Filter Can Ignore On Either Invocation |  |
| 84 | `testValidatorExceptionsPropagate` | Validator Exceptions Propagate |  |
| 91 | `testCopiesAreIndependentAndPreserveIndexZero` | Copies Are Independent And Preserve Index Zero |  |
| 103 | `testCopyDestinationFormsAndFallbacks` | Copy Destination Forms And Fallbacks |  |
| 117 | `testFreshCollectionAcceptsMemberReplacement` | Fresh Collection Accepts Member Replacement |  |
| 126 | `testIterationKeysMixRegisteredNamesAndNumericPositions` | Iteration Keys Mix Registered Names And Numeric Positions |  |
| 135 | `testRegisteredNumericZeroDoesNotBecomeTheCurrentPosition` | Registered Numeric Zero Does Not Become The Current Position |  |
| 142 | `testRangeLimitIsACountAndClipsAtTheEnd` | Range Limit Is A Count And Clips At The End |  |
| 157 | `testRangeRejectsStartAtTheMemberCount` | Range Rejects Start At The Member Count |  |

### `tests/Unit/Core/ConfigTest.php` (16)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 62 | `testDefaultsBeforeInitialization` | Defaults Before Initialization |  |
| 69 | `testUninitializedAccessThrows` | Uninitialized Access Throws | data provider |
| 81 | `testInvalidBaseDirectory` | Invalid Base Directory | data provider |
| 98 | `testLoadsValuesAndCachesThem` | Loads Values And Caches Them |  |
| 110 | `testPathsAreNormalizedAndBaseNamesAreRemoved` | Paths Are Normalized And Base Names Are Removed |  |
| 122 | `testNonStringKeyThrows` | Non String Key Throws |  |
| 129 | `testMissingKeysAreCachedAndDefaultsPreserveTypes` | Missing Keys Are Cached And Defaults Preserve Types |  |
| 140 | `testInvalidPhpConfigIsRejectedAndCanUseDefault` | Invalid Php Config Is Rejected And Can Use Default |  |
| 149 | `testEnumeratesValuesWithReferences` | Enumerates Values With References |  |
| 163 | `testReferenceValuesAndNamespaceCaching` | Reference Values And Namespace Caching |  |
| 175 | `testRejectsInvalidReferenceDocuments` | Rejects Invalid Reference Documents | data provider |
| 193 | `testMissingReferenceDocumentAndCachedMisses` | Missing Reference Document And Cached Misses |  |
| 206 | `testWholeReferencesAndNestedValues` | Whole References And Nested Values |  |
| 224 | `testMissingWholeReferenceReportsTheOriginalKey` | Missing Whole Reference Reports The Original Key |  |
| 233 | `testInlineReferencesIncludingDuplicatesBooleansAndMalformedTokens` | Inline References Including Duplicates Booleans And Malformed Tokens |  |
| 249 | `testNonScalarInlineReferenceThrows` | Non Scalar Inline Reference Throws |  |

### `tests/Unit/Core/Curl/ConfigTest.php` (5)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 9 | `testExplicitConfigurationAndNumericDefaults` | Explicit Configuration And Numeric Defaults |  |
| 23 | `testCurrentChromeSelectionIsOverwrittenByRawSelector` | Current Chrome Selection Is Overwritten By Raw Selector | **known defect KD-32** |
| 29 | `testInvalidSettingsAreRejected` | Invalid Settings Are Rejected | data provider |
| 39 | `testCachingUsesWritableTemporaryDirectoryByDefault` | Caching Uses Writable Temporary Directory By Default |  |
| 45 | `testUnwritableCacheDirectoryIsRejected` | Unwritable Cache Directory Is Rejected |  |

### `tests/Unit/Core/Curl/RequestTest.php` (11)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 7 | `testUrlParsingPreservesComponentsAndNoBodyDefaults` | Url Parsing Preserves Components And No Body Defaults |  |
| 17 | `testInvalidUrls` | Invalid Urls | data provider |
| 27 | `testUrlencodedArrayObjectAndStringBodies` | Urlencoded Array Object And String Bodies |  |
| 36 | `testNonStringScalarInUrlencodedModeHasNoBody` | Non String Scalar In Urlencoded Mode Has No Body |  |
| 40 | `testJsonBodyAndContentType` | Json Body And Content Type |  |
| 46 | `testJsonEncodingFailureIsRejected` | Json Encoding Failure Is Rejected |  |
| 52 | `testJsonPreservesFalsyBodies` | Json Preserves Falsy Bodies | data provider |
| 63 | `testRawModePreservesTheSuppliedBody` | Raw Mode Preserves The Supplied Body | data provider |
| 74 | `testRawModeRequiresANonemptyStringContentType` | Raw Mode Requires A Nonempty String Content Type | data provider |
| 83 | `testNullRemainsTheNoBodyDefaultForJsonAndRawModes` | Null Remains The No Body Default For Json And Raw Modes |  |
| 91 | `testEmptyUrlencodedQueryStringIsRejected` | Empty Urlencoded Query String Is Rejected |  |

### `tests/Unit/Core/Curl/ResponseTest.php` (17)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 23 | `testFacadeAcceptsUrlsAndConfigurationObjects` | Facade Accepts Urls And Configuration Objects |  |
| 32 | `testResponseDataStatusesAndTransferOptions` | Response Data Statuses And Transfer Options |  |
| 46 | `testArrayConfigurationAndScalarUrl` | Array Configuration And Scalar Url |  |
| 55 | `testFilesAndTemporaryCleanup` | Files And Temporary Cleanup |  |
| 65 | `testCallerProvidedFilePersists` | Caller Provided File Persists |  |
| 75 | `testSaveFlagsAndArraySavePaths` | Save Flags And Array Save Paths |  |
| 91 | `testCookieOptionsAndAutomaticCookieCleanup` | Cookie Options And Automatic Cookie Cleanup |  |
| 103 | `testInvalidCookieFile` | Invalid Cookie File | data provider |
| 109 | `testDomAndXPathResults` | Dom And X Path Results |  |
| 118 | `testInvalidXPathReportsQuery` | Invalid X Path Reports Query |  |
| 126 | `testTransferFailureRetainsErrorDetails` | Transfer Failure Retains Error Details |  |
| 136 | `testErrorAfterFileTransferStillFails` | Error After File Transfer Still Fails |  |
| 146 | `testRobotsRules` | Robots Rules | data provider |
| 159 | `testRobotsCheckCanDenyOrAllowConstructor` | Robots Check Can Deny Or Allow Constructor |  |
| 168 | `testFalsyTextBodyInitializesDomOnDemand` | Falsy Text Body Initializes Dom On Demand |  |
| 176 | `testCurrentMissingInfoExceptionClassFailure` | Current Missing Info Exception Class Failure | **known defect KD-31** |
| 183 | `testUnavailableSavePathRaisesSystemErrorAfterWarning` | Unavailable Save Path Raises System Error After Warning |  |

### `tests/Unit/Core/DebugTest.php` (11)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 17 | `testDisplayCollectionHelperAcceptsOnlyDisplayData` | Display Collection Helper Accepts Only Display Data |  |
| 54 | `testDefaultKillHandlerExitsWithConfiguredStatus` | Default Kill Handler Exits With Configured Status |  |
| 62 | `testKillSanitizesInvalidNestedExitStatusAndEmptyMessage` | Kill Sanitizes Invalid Nested Exit Status And Empty Message |  |
| 68 | `testReturningCustomKillHandlerReachesTheFinalExit` | Returning Custom Kill Handler Reaches The Final Exit |  |
| 75 | `testWebDisplayFormattingInAnIsolatedPlatformBranch` | Web Display Formatting In An Isolated Platform Branch |  |
| 87 | `testSuppressionAndInactiveConfigurationPreventDisplay` | Suppression And Inactive Configuration Prevent Display |  |
| 101 | `testDisplayDumpAndMessageEventsCarryCallerAndCreationTime` | Display Dump And Message Events Carry Caller And Creation Time |  |
| 119 | `testDisplayAndDumpTriggerOnlySupportedUserErrors` | Display And Dump Trigger Only Supported User Errors |  |
| 135 | `testDefaultCliHandlerDisplaysStructuredData` | Default Cli Handler Displays Structured Data |  |
| 144 | `testDataDescriptionsCharacterizeCurrentFormatting` | Data Descriptions Characterize Current Formatting | **known defect KD-29** |
| 159 | `testCustomKillHandlerReceivesCallerAndConvertedMessage` | Custom Kill Handler Receives Caller And Converted Message |  |

### `tests/Unit/Core/Encode/CsvTest.php` (5)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 9 | `testFields2csvJoinsScalars` | Fields2csv Joins Scalars |  |
| 14 | `testFields2csvKeepsNumbers` | Fields2csv Keeps Numbers |  |
| 21 | `testFields2csvBlanksNonScalars` | Fields2csv Blanks Non Scalars |  |
| 26 | `testFields2csvQuotesCommaAndQuote` | Fields2csv Quotes Comma And Quote |  |
| 32 | `testSerializeAndUnserializeAreNoOps` | Serialize And Unserialize Are No Ops |  |

### `tests/Unit/Core/EventFacadeTest.php` (1)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 7 | `testFacadeSharesFactoryAndDeliversHandlerResults` | Facade Shares Factory And Delivers Handler Results |  |

### `tests/Unit/Core/EventFactoryTest.php` (6)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 11 | `testCreationHandlersListenersAndStoredData` | Creation Handlers Listeners And Stored Data |  |
| 27 | `testOnDemandCreationAndPerTriggerCallbacks` | On Demand Creation And Per Trigger Callbacks |  |
| 39 | `testDuplicateCanBeIgnored` | Duplicate Can Be Ignored |  |
| 45 | `testDuplicateRaisesSpecificException` | Duplicate Raises Specific Exception |  |
| 51 | `testUnknownEventTypeRaisesSpecificException` | Unknown Event Type Raises Specific Exception |  |
| 56 | `testNonTriggerableTypeDoesNotInvokeHandler` | Non Triggerable Type Does Not Invoke Handler |  |

### `tests/Unit/Core/EventResolverTest.php` (1)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 7 | `testSharedNamespaceDeliversHandlerResultsToListeners` | Shared Namespace Delivers Handler Results To Listeners |  |

### `tests/Unit/Core/EventTest.php` (8)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 11 | `testNamedEmptyEventAndMultipleListeners` | Named Empty Event And Multiple Listeners |  |
| 22 | `testHandlerReplacementRunsBeforeListeners` | Handler Replacement Runs Before Listeners |  |
| 31 | `testFailureAbortsListeners` | Failure Aborts Listeners |  |
| 39 | `testCallAndEventAndStoredDataAreAggregatedForHandler` | Call And Event And Stored Data Are Aggregated For Handler |  |
| 55 | `testListenerInputPriority` | Listener Input Priority | data provider |
| 73 | `testStoredNullCanBeReplacedWithNoOverwriteFlag` | Stored Null Can Be Replaced With No Overwrite Flag |  |
| 84 | `testInvalidName` | Invalid Name | data provider |
| 94 | `testInvalidDataKey` | Invalid Data Key | data provider |

### `tests/Unit/Core/ExceptionTest.php` (4)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 12 | `testLegacyExceptionBaseKeepsExplicitCodesAndDerivesMissingCodes` | Legacy Exception Base Keeps Explicit Codes And Derives Missing Codes |  |
| 22 | `testExplicitAndDerivedCodesAndMetadata` | Explicit And Derived Codes And Metadata |  |
| 33 | `testUnavailableFeaturesExplainTheirContext` | Unavailable Features Explain Their Context | data provider |
| 44 | `testUnavailableFunctionContext` | Unavailable Function Context |  |

### `tests/Unit/Core/Html/EncodeTest.php` (5)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 10 | `testTypeLabels` | Type Labels | data provider |
| 20 | `testScalarEscapingAndBooleanMarkers` | Scalar Escaping And Boolean Markers |  |
| 30 | `testArraysObjectsKeysAndCustomTags` | Arrays Objects Keys And Custom Tags |  |
| 43 | `testResourcesUseDumpAndUnknownType` | Resources Use Dump And Unknown Type |  |
| 51 | `testUnserializationIsExplicitlyNotReady` | Unserialization Is Explicitly Not Ready |  |

### `tests/Unit/Core/Html/FormTest.php` (4)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 7 | `testTextAndTextareaInputsEscapeNamesAndValues` | Text And Textarea Inputs Escape Names And Values |  |
| 18 | `testNestedFormsApplyNestedTypeMaps` | Nested Forms Apply Nested Type Maps |  |
| 28 | `testSerializeCurrentlyDiscardsItsConstructedForm` | Serialize Currently Discards Its Constructed Form | **known defect KD-24** |
| 32 | `testDecodeExplicitlyReportsFeatureNotReady` | Decode Explicitly Reports Feature Not Ready |  |

### `tests/Unit/Core/LegacyErrorMessagesTest.php` (19)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 17 | `testErrorsRetainOperationContext` | Errors Retain Operation Context | data provider; **known defect KD-11, KD-48** |
| 73 | `testDuplicateKeyRetainsNonScalarValueDescription` | Duplicate Key Retains Non Scalar Value Description |  |
| 78 | `testConfigReferenceErrorRetainsAllLookupKeys` | Config Reference Error Retains All Lookup Keys |  |
| 85 | `testScalarDefaultErrorAndThreadFailureRetainDetails` | Scalar Default Error And Thread Failure Retain Details |  |
| 96 | `testIgnoredCollectionMemberRemainsAnEmptyString` | Ignored Collection Member Remains An Empty String |  |
| 103 | `testUploadAndBrokerErrorsRetainTheOriginalExceptionContext` | Upload And Broker Errors Retain The Original Exception Context |  |
| 113 | `testValidationFailureDescribesTheSuppliedValueAndRule` | Validation Failure Describes The Supplied Value And Rule | data provider |
| 122 | `testRouteMissingErrorExposesBothCoordinates` | Route Missing Error Exposes Both Coordinates |  |
| 131 | `testCrudErrorAcceptsUnspecifiedOperation` | Crud Error Accepts Unspecified Operation |  |
| 136 | `testDuplicateKeyErrorRetainsIndexAndNonScalarValue` | Duplicate Key Error Retains Index And Non Scalar Value |  |
| 143 | `testDuplicateKeyDescribesANonemptyScalarValue` | Duplicate Key Describes A Nonempty Scalar Value |  |
| 150 | `testEmptyNonScalarDuplicateIndexUsesItsEmptyDescription` | Empty Non Scalar Duplicate Index Uses Its Empty Description | **known defect KD-49** |
| 157 | `testBadApiResponseRetainsDocumentedClassAndMethod` | Bad Api Response Retains Documented Class And Method |  |
| 163 | `testSessionErrorsRetainReasonAndExpirationMetadata` | Session Errors Retain Reason And Expiration Metadata |  |
| 171 | `testCurlSystemErrorRetainsDataAndDetails` | Curl System Error Retains Data And Details |  |
| 178 | `testLegacyNotFoundStatusCurrentlyReportsServerErrorCode` | Legacy Not Found Status Currently Reports Server Error Code | **known defect KD-10** |
| 184 | `testXpathErrorRetainsActualLibxmlDetailsWithoutDuplicatingMessage` | Xpath Error Retains Actual Libxml Details Without Duplicating Message |  |
| 200 | `testScalarTypeErrorUsesItsSuppliedTypeWithoutAnUndefinedLocal` | Scalar Type Error Uses Its Supplied Type Without An Undefined Local |  |
| 207 | `testLegacySshParameterErrorRetainsItsDeclaredNameAndMessage` | Legacy Ssh Parameter Error Retains Its Declared Name And Message |  |

### `tests/Unit/Core/LegacyListenerTest.php` (4)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 21 | `testListenerFactoryUsesTheExistingLoaderContractAndSharedFactory` | Listener Factory Uses The Existing Loader Contract And Shared Factory |  |
| 32 | `testHookableCurrentlyDropsEventDataAndReportsItsUndefinedLocal` | Hookable Currently Drops Event Data And Reports Its Undefined Local | **known defect KD-17** |
| 49 | `testListenerConstructorImmediatelyInvokesItsCallback` | Listener Constructor Immediately Invokes Its Callback |  |
| 55 | `testStubCurrentlyReadsASeparateUndeclaredListenerProperty` | Stub Currently Reads A Separate Undeclared Listener Property | **known defect KD-18** |

### `tests/Unit/Core/LegacyMappingTest.php` (7)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 10 | `testDeclaredCoreDataFieldsAreMapped` | Declared Core Data Fields Are Mapped |  |
| 14 | `testCoreDataMarkerOptsIntoCreatingProperties` | Core Data Marker Opts Into Creating Properties |  |
| 19 | `testCoreDataDoesNotCreateUndeclaredPropertiesByDefault` | Core Data Does Not Create Undeclared Properties By Default |  |
| 24 | `testListKeysAndIndexZeroDefaultFollowCurrentMappingRules` | List Keys And Index Zero Default Follow Current Mapping Rules |  |
| 31 | `testNonObjectInputCurrentlyProducesAnEmptyMap` | Non Object Input Currently Produces An Empty Map | **known defect KD-26** |
| 36 | `testObjectMappingCurrentlyOmitsRequiredValueArgument` | Object Mapping Currently Omits Required Value Argument | **known defect KD-26** |
| 41 | `testControllerExportsAreLimitedToFlatDataObjects` | Controller Exports Are Limited To Flat Data Objects |  |

### `tests/Unit/Core/MapperTest.php` (16)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 28 | `testReadsPropertiesByNameAndReflection` | Reads Properties By Name And Reflection |  |
| 37 | `testReadErrors` | Read Errors | data provider |
| 52 | `testIgnoredReadsReturnConfiguredSentinel` | Ignored Reads Return Configured Sentinel |  |
| 62 | `testWritesDeclaredProtectedAndReflectionProperties` | Writes Declared Protected And Reflection Properties |  |
| 77 | `testWriteErrors` | Write Errors | data provider |
| 85 | `testIgnoredWritesAndDynamicProperties` | Ignored Writes And Dynamic Properties |  |
| 101 | `testExportsPublicPropertiesAndOmitsNullFromStdClass` | Exports Public Properties And Omits Null From Std Class |  |
| 113 | `testListsAndObjectsPopulateProperties` | Lists And Objects Populate Properties |  |
| 125 | `testArgumentMapping` | Argument Mapping | data provider |
| 144 | `testParamsSupportNamedPositionalAndEmptyLists` | Params Support Named Positional And Empty Lists |  |
| 156 | `testVisitReadsAndUpdatesSelectedPublicProperties` | Visit Reads And Updates Selected Public Properties |  |
| 174 | `testRetiredPrivateReadOptionsRaiseAClearMappingError` | Retired Private Read Options Raise A Clear Mapping Error | data provider |
| 184 | `testPrivateReadFlagIsHarmlessForPublicOnlyObjects` | Private Read Flag Is Harmless For Public Only Objects |  |
| 194 | `testProtectedVisitPreservesReflectionFailure` | Protected Visit Preserves Reflection Failure |  |
| 200 | `testPrivateVisitIsIgnoredEvenWithObsoleteFlags` | Private Visit Is Ignored Even With Obsolete Flags |  |
| 207 | `testMissingVisitThrowsCannotGetProperty` | Missing Visit Throws Cannot Get Property |  |

### `tests/Unit/Core/Md/ConvertTest.php` (4)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 7 | `testMarkdownStringInstanceAndFileProduceEquivalentHtml` | Markdown String Instance And File Produce Equivalent Html |  |
| 22 | `testMarkdownExtraTablesAndFencedCode` | Markdown Extra Tables And Fenced Code |  |
| 29 | `testNonStringSourceIsRejected` | Non String Source Is Rejected |  |
| 34 | `testMissingFileRaisesInvalidSource` | Missing File Raises Invalid Source |  |

### `tests/Unit/Core/RandStrTest.php` (8)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 9 | `testStrDefaultLengthAndCharset` | Str Default Length And Charset |  |
| 16 | `testStrCustomCharpoolAndLength` | Str Custom Charpool And Length |  |
| 30 | `testStrGenerateAndInvoke` | Str Generate And Invoke |  |
| 41 | `testStrGetStringWithExplicitLength` | Str Get String With Explicit Length |  |
| 49 | `testStrOptionsAsList` | Str Options As List |  |
| 56 | `testStrWithoutLowerAlpha` | Str Without Lower Alpha |  |
| 62 | `testStrNonStringCharpoolThrowsBadConfig` | Str Non String Charpool Throws Bad Config |  |
| 68 | `testStrTooSmallCharpoolThrows` | Str Too Small Charpool Throws |  |

### `tests/Unit/Core/RandTest.php` (8)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 9 | `testGetWithinBounds` | Get Within Bounds |  |
| 22 | `testGetMinEqualsMax` | Get Min Equals Max |  |
| 29 | `testGetRejectsMaxLessThanMin` | Get Rejects Max Less Than Min |  |
| 35 | `testGetRejectsNegativeMin` | Get Rejects Negative Min |  |
| 41 | `testGetRejectsNonIntParams` | Get Rejects Non Int Params |  |
| 53 | `testGetRejectsBadMaxParameters` | Get Rejects Bad Max Parameters |  |
| 65 | `testBytesLength` | Bytes Length |  |
| 74 | `testInvokeAndGenerateMatchBounds` | Invoke And Generate Match Bounds |  |

### `tests/Unit/Core/Route/ControllerTest.php` (13)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 63 | `testResolutionInjectsInputsLifecycleAndStatus` | Resolution Injects Inputs Lifecycle And Status |  |
| 76 | `testSameWeightIsSkippedAndLaterWeightsResolve` | Same Weight Is Skipped And Later Weights Resolve |  |
| 82 | `testTraversalCanResolveAParentAndItsChild` | Traversal Can Resolve A Parent And Its Child |  |
| 95 | `testEmptyPathSegmentsResolveLikeTheCleanResource` | Empty Path Segments Resolve Like The Clean Resource | data provider |
| 115 | `testResolverCanConsumeUnmatchedSuffix` | Resolver Can Consume Unmatched Suffix |  |
| 123 | `testResourceTransformsAndNullTransformFallback` | Resource Transforms And Null Transform Fallback |  |
| 131 | `testCyclesAndDemands` | Cycles And Demands | data provider |
| 137 | `testIgnoredOrAbstractClassesDoNotResolve` | Ignored Or Abstract Classes Do Not Resolve | data provider |
| 142 | `testUnresolvedAndProviderClassesDoNotStopIteration` | Unresolved And Provider Classes Do Not Stop Iteration |  |
| 148 | `testDebugSuppressInterface` | Debug Suppress Interface |  |
| 153 | `testStringFactoryAndBaseInterface` | String Factory And Base Interface |  |
| 158 | `testReservedNodesAndCustomSetsAreCachedSeparately` | Reserved Nodes And Custom Sets Are Cached Separately |  |
| 167 | `testInvalidControllerParameters` | Invalid Controller Parameters | data provider |

### `tests/Unit/Core/Session/SessionTest.php` (18)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 33 | `testStartRefreshIdAndSnapshot` | Start Refresh Id And Snapshot |  |
| 49 | `testStoredValuesCanBeReadAndRemoved` | Stored Values Can Be Read And Removed |  |
| 58 | `testNonceCanBeConsumedOnlyOnce` | Nonce Can Be Consumed Only Once |  |
| 66 | `testNonceWithoutDataOrExpiryAndFalsyData` | Nonce Without Data Or Expiry And Falsy Data |  |
| 75 | `testMissingNonceIsRejected` | Missing Nonce Is Rejected |  |
| 80 | `testExpiredNonceIsRejectedWithoutSleeping` | Expired Nonce Is Rejected Without Sleeping |  |
| 91 | `testNonceIsAcceptedAtExactlyItsTtl` | Nonce Is Accepted At Exactly Its Ttl |  |
| 111 | `testNonceIsExpiredOneSecondAfterItsTtl` | Nonce Is Expired One Second After Its Ttl |  |
| 119 | `testCorruptNonceTtlIsRejected` | Corrupt Nonce Ttl Is Rejected |  |
| 127 | `testInvalidNonceParameters` | Invalid Nonce Parameters | data provider |
| 137 | `testWeakRandomnessIsRejected` | Weak Randomness Is Rejected |  |
| 143 | `testInvalidSessionKey` | Invalid Session Key |  |
| 149 | `testInvalidRefreshKey` | Invalid Refresh Key | data provider |
| 154 | `testNoCookieStartRequiresExplicitId` | No Cookie Start Requires Explicit Id |  |
| 159 | `testDestroyNotifiesListenersAndCanRestart` | Destroy Notifies Listeners And Can Restart |  |
| 169 | `testCookieSessionDestroyReportsCookieCleanup` | Cookie Session Destroy Reports Cookie Cleanup |  |
| 179 | `testMissingSessionGlobalIsRejectedEvenWhenActive` | Missing Session Global Is Rejected Even When Active |  |
| 187 | `testFailedStorageRetriesAndReturnsNoId` | Failed Storage Retries And Returns No Id |  |

### `tests/Unit/Core/ShutdownTest.php` (9)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 23 | `testNamedPrioritiesRunInDocumentedOrderAndHandlesAreUnique` | Named Priorities Run In Documented Order And Handles Are Unique |  |
| 35 | `testZeroAndOutOfRangeWeightsCurrentlyJoinTheDefaultQueue` | Zero And Out Of Range Weights Currently Join The Default Queue | **known defect KD-15** |
| 42 | `testDistinctWeightsRunBeforeDefaultAndAfterFirst` | Distinct Weights Run Before Default And After First |  |
| 51 | `testEachCallbackRunsOnlyOnceAcrossRepeatedInvocation` | Each Callback Runs Only Once Across Repeated Invocation |  |
| 65 | `testExplicitInvocationIsNotRepeatedAtPhpShutdown` | Explicit Invocation Is Not Repeated At Php Shutdown |  |
| 74 | `testCallbackRegisteredDuringInvocationRunsAtPhpShutdown` | Callback Registered During Invocation Runs At Php Shutdown |  |
| 84 | `testEqualWeightsRunNewestFirstWithoutLosingCallbacks` | Equal Weights Run Newest First Without Losing Callbacks |  |
| 91 | `testRegisteredCallbackRunsAtActualPhpShutdown` | Registered Callback Runs At Actual Php Shutdown |  |
| 96 | `testWeightCeilingStopsUnboundedInsertion` | Weight Ceiling Stops Unbounded Insertion |  |

### `tests/Unit/Core/SshTest.php` (32)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 33 | `testUnavailableExtensionIsReported` | Unavailable Extension Is Reported |  |
| 39 | `testRequiredConfigurationIsChecked` | Required Configuration Is Checked | data provider |
| 45 | `testAuthenticationConfigurationMustBePresent` | Authentication Configuration Must Be Present |  |
| 49 | `testPublicKeyConfigurationNeedsPrivateKey` | Public Key Configuration Needs Private Key |  |
| 53 | `testDeferredSubclassReportsUninitializedAuthentication` | Deferred Subclass Reports Uninitialized Authentication |  |
| 58 | `testPasswordConnectionAndExecutionUseTheirMatchingTransport` | Password Connection And Execution Use Their Matching Transport |  |
| 66 | `testRepeatedConnectIsRejected` | Repeated Connect Is Rejected |  |
| 70 | `testConnectionFailureIsReported` | Connection Failure Is Reported |  |
| 75 | `testPasswordFailureUsesTheExistingFrameworkException` | Password Failure Uses The Existing Framework Exception |  |
| 81 | `testPublicKeyPathRunsRealOpenSslAndRemovesItsTemporaryFiles` | Public Key Path Runs Real Open Ssl And Removes Its Temporary Files | data provider |
| 91 | `testProvidedFingerprintIsChecked` | Provided Fingerprint Is Checked |  |
| 96 | `testFingerprintMismatchIsReported` | Fingerprint Mismatch Is Reported |  |
| 101 | `testPublicKeyAuthenticationFailureIsReported` | Public Key Authentication Failure Is Reported |  |
| 107 | `testLocalCryptoFailureBoundariesAreReported` | Local Crypto Failure Boundaries Are Reported | data provider |
| 118 | `testOperationsRequireAConnection` | Operations Require A Connection | data provider |
| 124 | `testFailedRemoteExecutionIsReported` | Failed Remote Execution Is Reported |  |
| 129 | `testDisconnectIgnoresMissingConnectionOnlyWhenRequested` | Disconnect Ignores Missing Connection Only When Requested |  |
| 136 | `testProcessChecksPreserveZeroExitCodeSemanticsAndLegacyHooks` | Process Checks Preserve Zero Exit Code Semantics And Legacy Hooks |  |
| 142 | `testMissingRemoteProcessThrowsUnlessIgnored` | Missing Remote Process Throws Unless Ignored |  |
| 147 | `testInvalidPidUsesTheLegacyCompatibleParameterException` | Invalid Pid Uses The Legacy Compatible Parameter Exception |  |
| 152 | `testBackgroundExecutionReturnsPidAndChecksRequestedRuntime` | Background Execution Returns Pid And Checks Requested Runtime | data provider |
| 159 | `testBackgroundExecutionNeedsAPid` | Background Execution Needs A Pid |  |
| 165 | `testKillArgumentsAreValidatedBeforeSendingSignals` | Kill Arguments Are Validated Before Sending Signals | data provider |
| 171 | `testProcessCanExitAfterEitherSignal` | Process Can Exit After Either Signal |  |
| 179 | `testSignalAndStillRunningFailuresAreReported` | Signal And Still Running Failures Are Reported | data provider |
| 185 | `testSftpOpenFailuresAreReportedBeforeTheMissingHelper` | Sftp Open Failures Are Reported Before The Missing Helper |  |
| 190 | `testSftpLocalOpenFailureIsReported` | Sftp Local Open Failure Is Reported |  |
| 195 | `testLegacyParameterAliasAutoloadsInAFreshRuntime` | Legacy Parameter Alias Autoloads In A Fresh Runtime |  |
| 200 | `testSuccessfulReadsRestartTheIdleTimeoutAcrossALongerTransfer` | Successful Reads Restart The Idle Timeout Across A Longer Transfer |  |
| 211 | `testAnIdleTimeoutStopsTheNextReadAndClosesBothStreams` | An Idle Timeout Stops The Next Read And Closes Both Streams |  |
| 224 | `testNativeReadsHandleShortEmptyAndZeroBodies` | Native Reads Handle Short Empty And Zero Bodies | data provider |
| 238 | `testCallbackFailureStillClosesTheLocalAndRemoteStreams` | Callback Failure Still Closes The Local And Remote Streams |  |

### `tests/Unit/Core/Template/ControllerTest.php` (13)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 90 | `testDesignRootUsesConfigurationOrItsDocumentedFallback` | Design Root Uses Configuration Or Its Documented Fallback |  |
| 107 | `testMarkdownReadFailureIsCaughtByTemplateResolver` | Markdown Read Failure Is Caught By Template Resolver |  |
| 112 | `testAbsolutePhpFileAndDisplayClass` | Absolute Php File And Display Class |  |
| 120 | `testFileReadFailuresReportLibraryExceptions` | File Read Failures Report Library Exceptions | data provider |
| 134 | `testPhpAndPlainHtmlFilesRenderAndCheckWithoutOutput` | Php And Plain Html Files Render And Check Without Output |  |
| 150 | `testMarkdownAndGeneratedMarkdownFiles` | Markdown And Generated Markdown Files |  |
| 166 | `testObjectAndClassDesignsReceiveDataAndCheckOnlyFlag` | Object And Class Designs Receive Data And Check Only Flag |  |
| 180 | `testInvalidDesignsAreRejected` | Invalid Designs Are Rejected | data provider |
| 186 | `testDesignBasesPrefixesAndSeparators` | Design Bases Prefixes And Separators |  |
| 194 | `testFormattingFlagsReplacementsAndOutput` | Formatting Flags Replacements And Output |  |
| 203 | `testLoggerWritesDesignKindAndNormalizedName` | Logger Writes Design Kind And Normalized Name |  |
| 214 | `testConstructorAndPreparedInterfacesControlDisplayTiming` | Constructor And Prepared Interfaces Control Display Timing |  |
| 233 | `testDefaultDisplayCallbackDelegatesToTemplateRendering` | Default Display Callback Delegates To Template Rendering |  |

### `tests/Unit/Core/Template/ValidateControllerTest.php` (2)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 6 | `testControllerUnwrapsMembersBeforeInspectingInputFields` | Controller Unwraps Members Before Inspecting Input Fields |  |
| 16 | `testConstructorRejectsInvalidInputAndCollection` | Constructor Rejects Invalid Input And Collection | data provider |

### `tests/Unit/Core/Template/ViewTest.php` (1)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 19 | `testResourceSearchTriggersEventsForMatchingAncestorFiles` | Resource Search Triggers Events For Matching Ancestor Files |  |

### `tests/Unit/Core/Text/EncodeTest.php` (8)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 9 | `testSerializeString` | Serialize String |  |
| 14 | `testSerializeBoolAndNull` | Serialize Bool And Null |  |
| 22 | `testSerializeScalarNumber` | Serialize Scalar Number |  |
| 27 | `testSerializeNestedArrayIndents` | Serialize Nested Array Indents |  |
| 35 | `testSerializeHonoursOptions` | Serialize Honours Options |  |
| 47 | `testSerializeObject` | Serialize Object |  |
| 53 | `testSerializeNonPrintableString` | Serialize Non Printable String |  |
| 60 | `testUnserializeThrowsFeatureNotReady` | Unserialize Throws Feature Not Ready |  |

### `tests/Unit/Core/Util/Image/DimTest.php` (4)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 8 | `testAliasesRatioAndString` | Aliases Ratio And String |  |
| 18 | `testScalingMaintainsRatioAndReturnsOriginalWhenUnnecessary` | Scaling Maintains Ratio And Returns Original When Unnecessary |  |
| 28 | `testInstanceScalingModes` | Instance Scaling Modes |  |
| 37 | `testInvalidSizes` | Invalid Sizes | data provider |

### `tests/Unit/Core/Util/Image/ImageTest.php` (6)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 12 | `testBlobBase64SaveAndLoadUseRealImages` | Blob Base64 Save And Load Use Real Images |  |
| 25 | `testDimensionExtractionClonesTheImageWithoutChangingItsPage` | Dimension Extraction Clones The Image Without Changing Its Page |  |
| 34 | `testSingleAxisResizePreservesTheOriginalRatio` | Single Axis Resize Preserves The Original Ratio | data provider |
| 49 | `testMinimumBoundsAndEqualHeightDoNotIgnoreRequiredWidth` | Minimum Bounds And Equal Height Do Not Ignore Required Width |  |
| 61 | `testSingleAxisConstructorsDelegateToProportionalResizing` | Single Axis Constructors Delegate To Proportional Resizing |  |
| 72 | `testBothConstructorBoundsCurrentlyLeaveTheImageUnchanged` | Both Constructor Bounds Currently Leave The Image Unchanged | **known defect KD-23** |

### `tests/Unit/Core/Util/ListcheckTest.php` (4)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 12 | `testLastSuppliedValueDefinesTheList` | Last Supplied Value Defines The List |  |
| 24 | `testMembershipUsesLooseComparison` | Membership Uses Loose Comparison |  |
| 31 | `testFailureInvokesRegisteredCallbackWithFactoryAndValue` | Failure Invokes Registered Callback With Factory And Value |  |
| 41 | `testFailureCallbackExceptionPropagates` | Failure Callback Exception Propagates |  |

### `tests/Unit/Core/Util/Utf8Test.php` (2)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 7 | `testLatinOneAndAlreadyValidUtf8` | Latin One And Already Valid Utf8 |  |
| 15 | `testEmptyValueAndArrayResultAreNotExposedAsString` | Empty Value And Array Result Are Not Exposed As String |  |

### `tests/Unit/Core/Util/UtilityTest.php` (11)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 27 | `testBooleanOptionsAreStrict` | Boolean Options Are Strict | data provider |
| 36 | `testMissingOptionsAndScalarModes` | Missing Options And Scalar Modes |  |
| 49 | `testCopyPreservesTruthyScalarsAndRecursesArrays` | Copy Preserves Truthy Scalars And Recurses Arrays |  |
| 60 | `testDefaultObjectCopyIsShallowAndIndependent` | Default Object Copy Is Shallow And Independent |  |
| 71 | `testObjectConversionsRecurseAndStdClassTakesPrecedence` | Object Conversions Recurse And Std Class Takes Precedence |  |
| 82 | `testTimecodes` | Timecodes | data provider |
| 88 | `testShortTimecodeAndNumericCoercion` | Short Timecode And Numeric Coercion |  |
| 94 | `testRelativeTimeUnits` | Relative Time Units | data provider |
| 102 | `testRelativeTimeParsingDefaultsAndInvalidValues` | Relative Time Parsing Defaults And Invalid Values |  |
| 113 | `testReservedSegmentsAndCustomDelimiter` | Reserved Segments And Custom Delimiter |  |
| 119 | `testCustomRelativeTimeTableMayHaveNoMatchingUnit` | Custom Relative Time Table May Have No Matching Unit |  |

### `tests/Unit/Core/Util/ValidationFactoryTest.php` (2)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 16 | `testDefaultConstraintCurrentlyRejectsThePairBuiltByItsHelper` | Default Constraint Currently Rejects The Pair Built By Its Helper | **known defect KD-20** |
| 23 | `testSubclassConstraintAllowsPairsAndPreservesTheirInputs` | Subclass Constraint Allows Pairs And Preserves Their Inputs |  |

### `tests/Unit/Core/Util/ValidationTest.php` (14)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 22 | `testOnlyStrictTruePassesAndStoredStringsAreAvailable` | Only Strict True Passes And Stored Strings Are Available |  |
| 35 | `testNullDefaultOptionsCurrentlyCannotBeOverridden` | Null Default Options Currently Cannot Be Overridden | **known defect KD-21** |
| 42 | `testExplicitRegexConstructorKeepsItsExistingArrayPayload` | Explicit Regex Constructor Keeps Its Existing Array Payload |  |
| 47 | `testFactoryInterfaceFailureLoadsThroughBothNames` | Factory Interface Failure Loads Through Both Names |  |
| 54 | `testHistoricalFactoryExceptionAutoloadsInAFreshProcess` | Historical Factory Exception Autoloads In A Fresh Process |  |
| 60 | `testRegexMatchingAndMultiplePatterns` | Regex Matching And Multiple Patterns |  |
| 69 | `testRegexConfigurationFailures` | Regex Configuration Failures | data provider |
| 79 | `testInvalidRegexSyntaxProducesItsDescriptiveException` | Invalid Regex Syntax Produces Its Descriptive Exception |  |
| 94 | `testPcreExecutionErrorCurrentlyReturnsFalse` | Pcre Execution Error Currently Returns False | **known defect KD-22** |
| 102 | `testPcreExceptionExplainsNativeEngineErrors` | Pcre Exception Explains Native Engine Errors | data provider |
| 125 | `testInvalidPatternExceptionIncludesPatternAndNativeCodeName` | Invalid Pattern Exception Includes Pattern And Native Code Name |  |
| 133 | `testHostnameDefinerSuppliesAValidPatternWithoutChangingItsRawConstant` | Hostname Definer Supplies A Valid Pattern Without Changing Its Raw Constant | data provider |
| 144 | `testSupportedFilterIdsConstructSuccessfully` | Supported Filter Ids Construct Successfully |  |
| 150 | `testInvalidFilterUsesItsConcreteException` | Invalid Filter Uses Its Concrete Exception |  |

### `tests/Unit/Core/Util/ValueTest.php` (4)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 9 | `testStringability` | Stringability | data provider |
| 17 | `testResourceCanBeConvertedToStringWithoutClosingIt` | Resource Can Be Converted To String Without Closing It |  |
| 24 | `testScalarRequirementReturnsTheUnchangedScalar` | Scalar Requirement Returns The Unchanged Scalar | data provider |
| 30 | `testInvalidScalarRequirementsAndFallbacks` | Invalid Scalar Requirements And Fallbacks |  |

### `tests/Unit/Core/UuidTest.php` (12)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 11 | `testV3MatchesRfc4122Vector` | V3 Matches Rfc4122 Vector |  |
| 20 | `testV5MatchesRfc4122Vector` | V5 Matches Rfc4122 Vector |  |
| 29 | `testV3AcceptsBracedAndUnhyphenatedNamespace` | V3 Accepts Braced And Unhyphenated Namespace |  |
| 36 | `testV4FormatVersionAndVariant` | V4 Format Version And Variant |  |
| 50 | `testGetDefaultsToV4` | Get Defaults To V4 |  |
| 56 | `testGetArrayFormV3AndV5` | Get Array Form V3 And V5 |  |
| 68 | `testGetWithNamespaceAndVersion4Throws` | Get With Namespace And Version4 Throws |  |
| 74 | `testGetWithNamespaceAndBadVersionThrows` | Get With Namespace And Bad Version Throws |  |
| 80 | `testInvalidNamespaceThrows` | Invalid Namespace Throws |  |
| 99 | `testInvokeAlwaysReturnsV4` | Invoke Always Returns V4 |  |
| 107 | `testToStringReturnsUuidForConstructorState` | To String Returns Uuid For Constructor State |  |
| 113 | `testGenerateUsesConstructorState` | Generate Uses Constructor State |  |

### `tests/Unit/Core/Xml/DomTest.php` (15)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 9 | `testSubclassWithoutADocumentHasNoXPathResults` | Subclass Without A Document Has No X Path Results |  |
| 14 | `testInvalidXmlFileRaisesTheLibraryException` | Invalid Xml File Raises The Library Exception |  |
| 22 | `testHtmlConversionFailureRestoresLibxmlState` | Html Conversion Failure Restores Libxml State |  |
| 35 | `testGenericInputFailureReportsThePublicInputContract` | Generic Input Failure Reports The Public Input Contract |  |
| 43 | `testXmlLoadDocumentAndIterator` | Xml Load Document And Iterator |  |
| 61 | `testExistingNodeProducesAnIndependentDocument` | Existing Node Produces An Independent Document |  |
| 70 | `testEmptyDomAndXmlStringConstructor` | Empty Dom And Xml String Constructor |  |
| 77 | `testXmlFileConstructor` | Xml File Constructor |  |
| 85 | `testHtmlLoadingAndAttributeStripping` | Html Loading And Attribute Stripping |  |
| 95 | `testMalformedXmlRaisesLibraryException` | Malformed Xml Raises Library Exception |  |
| 100 | `testXPathResultsSupportIterationAndChildAccess` | X Path Results Support Iteration And Child Access |  |
| 120 | `testEncoderRejectsUnrelatedXmlFormat` | Encoder Rejects Unrelated Xml Format |  |
| 124 | `testObjectConversionGroupsRepeatedNodesPreservesEmptyFirstValue` | Object Conversion Groups Repeated Nodes Preserves Empty First Value |  |
| 132 | `testObjectConversionUsesNameAndIndexAttributes` | Object Conversion Uses Name And Index Attributes |  |
| 138 | `testObjectConversionCanPreserveMixedParentText` | Object Conversion Can Preserve Mixed Parent Text |  |

### `tests/Unit/Core/Xml/EncodeTest.php` (15)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 15 | `testScalarDocuments` | Scalar Documents | data provider |
| 23 | `testObjectAndArrayTypes` | Object And Array Types |  |
| 33 | `testNestedNodesEscapingAndTypeDetection` | Nested Nodes Escaping And Type Detection |  |
| 43 | `testInvalidAndNumericKeysUseDefaultNodeAndPreserveKey` | Invalid And Numeric Keys Use Default Node And Preserve Key |  |
| 53 | `testMetaOptionsCustomRootChecksumsAndIndentation` | Meta Options Custom Root Checksums And Indentation |  |
| 66 | `testNamedNestedStructureAndRealFloatValues` | Named Nested Structure And Real Float Values |  |
| 72 | `testStructureMetadataFallbackForNonScalarMetaOption` | Structure Metadata Fallback For Non Scalar Meta Option |  |
| 78 | `testNestedArrayAndObjectTypeDetection` | Nested Array And Object Type Detection |  |
| 84 | `testDumpEnabledEmptyAndNumericStringObjectKeys` | Dump Enabled Empty And Numeric String Object Keys |  |
| 91 | `testResourceFallbackWithAndWithoutDumpPreservesWarnings` | Resource Fallback With And Without Dump Preserves Warnings |  |
| 109 | `testLegacyIndexAndScalarTypeDecoding` | Legacy Index And Scalar Type Decoding |  |
| 115 | `testInstanceAndCompatibilityAlias` | Instance And Compatibility Alias |  |
| 123 | `testRoundTripsValuesWithExplicitMetadata` | Round Trips Values With Explicit Metadata | data provider |
| 139 | `testAssocDecodingConvertsObjectsWithoutChangingScalarTypes` | Assoc Decoding Converts Objects Without Changing Scalar Types |  |
| 145 | `testLegacyXmlWithoutNewMetadataCanStillBeRead` | Legacy Xml Without New Metadata Can Still Be Read |  |

### `tests/Unit/Data/ErrorDetailsTest.php` (5)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 7 | `testValidationFailureCollectsOnlyStringFieldsAndCurrentlyIgnoresMessage` | Validation Failure Collects Only String Fields And Currently Ignores Message | **known defect KD-38** |
| 20 | `testDataNotFoundRetainsLookupContext` | Data Not Found Retains Lookup Context |  |
| 28 | `testRandomFailurePreservesRequestedLength` | Random Failure Preserves Requested Length |  |
| 36 | `testCliErrorExposesStatusUsageFlagAndData` | Cli Error Exposes Status Usage Flag And Data |  |
| 47 | `testCloudErrorsExposeParameterAndChildClass` | Cloud Errors Expose Parameter And Child Class |  |

### `tests/Unit/Data/MapperTest.php` (24)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 30 | `testBaseFieldListIsAbsentAndUnknownDumpTypesUseHumanFallback` | Base Field List Is Absent And Unknown Dump Types Use Human Fallback |  |
| 43 | `testWebviewCurrentlyDiscardsTheHtmlEncodedClone` | Webview Currently Discards The Html Encoded Clone | **known defect KD-36** |
| 49 | `testRuleConsumerReceivesConstructedAndExplicitRules` | Rule Consumer Receives Constructed And Explicit Rules |  |
| 58 | `testFieldListWithInstanceCurrentlyFailsBeforeCreatingReflection` | Field List With Instance Currently Fails Before Creating Reflection | **known defect KD-37** |
| 64 | `testConstructorsMapNamedAndPositionalDataAndIgnoreUnknownFields` | Constructors Map Named And Positional Data And Ignore Unknown Fields |  |
| 74 | `testFieldInspectionAndCallbacks` | Field Inspection And Callbacks |  |
| 94 | `testInvalidFieldName` | Invalid Field Name |  |
| 99 | `testMissingField` | Missing Field |  |
| 104 | `testMagicFieldAccessStrippingAndFallbacks` | Magic Field Access Stripping And Fallbacks |  |
| 120 | `testEachFieldAndScalarConversions` | Each Field And Scalar Conversions |  |
| 137 | `testRecursiveHtmlEncodingClonesTheData` | Recursive Html Encoding Clones The Data |  |
| 148 | `testTypeAndLifecycleInterfaces` | Type And Lifecycle Interfaces |  |
| 157 | `testStringRepresentations` | String Representations |  |
| 174 | `testInvalidStringifierThrows` | Invalid Stringifier Throws | data provider |
| 180 | `testKeyPrefixMappingPreservesUnmatchedKeysAndExistingMap` | Key Prefix Mapping Preserves Unmatched Keys And Existing Map |  |
| 188 | `testInvalidPrefixThrows` | Invalid Prefix Throws |  |
| 194 | `testEmptyMappingKeyThrows` | Empty Mapping Key Throws |  |
| 201 | `testNumericConversionPreservesNonNumericStrings` | Numeric Conversion Preserves Non Numeric Strings | data provider |
| 209 | `testDumpFormatsAndStringFailureRecovery` | Dump Formats And String Failure Recovery |  |
| 227 | `testHtmlEncodingPlainObjectsAndUnsupportedResources` | Html Encoding Plain Objects And Unsupported Resources |  |
| 239 | `testUtf8FixOnlyTouchesSelectedStringFields` | Utf8 Fix Only Touches Selected String Fields |  |
| 247 | `testMapAppliesMissingDefaultsAndOptionalEmptyDefaults` | Map Applies Missing Defaults And Optional Empty Defaults |  |
| 260 | `testRuleObjectConstructorAndRemapping` | Rule Object Constructor And Remapping |  |
| 269 | `testInvalidMappingRules` | Invalid Mapping Rules | data provider |

### `tests/Unit/Data/Orm/LegacyOrmTest.php` (3)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 26 | `testStaticFacadeCachesALegalSubclassAndReturnsItsResults` | Static Facade Caches A Legal Subclass And Returns Its Results |  |
| 37 | `testNonemptyConstructorArrayStillCannotActAsADriver` | Nonempty Constructor Array Still Cannot Act As A Driver | data provider; **known defect KD-34** |
| 45 | `testDefaultFacadeUsesRealNativeSessionsWhileRetainingItsVoidGetResult` | Default Facade Uses Real Native Sessions While Retaining Its Void Get Result | **known defect KD-33** |

### `tests/Unit/Data/Orm/SessionDriverTest.php` (8)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 45 | `testHandlerObjectIsUsedDirectlyAndReturnsItsResults` | Handler Object Is Used Directly And Returns Its Results |  |
| 54 | `testHandlerClassReceivesItsConstructorParameters` | Handler Class Receives Its Constructor Parameters |  |
| 64 | `testInvalidExplicitHandlersFallBackToConfiguration` | Invalid Explicit Handlers Fall Back To Configuration | data provider |
| 71 | `testNativeOrmKeyOptionsAndConfiguration` | Native Orm Key Options And Configuration |  |
| 78 | `testInvalidKeysThrowTheDeclaredParameterError` | Invalid Keys Throw The Declared Parameter Error | data provider |
| 84 | `testDisabledSessionStatusUsesItsFrameworkFailure` | Disabled Session Status Uses Its Framework Failure |  |
| 94 | `testRealNativeSessionPersistsAcrossModernAndLegacyDriverNames` | Real Native Session Persists Across Modern And Legacy Driver Names |  |
| 104 | `testDestroyCurrentlyAttemptsRegenerationAfterEndingTheSession` | Destroy Currently Attempts Regeneration After Ending The Session | **known defect KD-35** |

### `tests/Unit/Data/OrmMapTest.php` (3)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 8 | `testObjectIsPreservedAndTakesPrecedence` | Object Is Preserved And Takes Precedence |  |
| 15 | `testDataAndTypeConstructAMappedObject` | Data And Type Construct A Mapped Object |  |
| 23 | `testIncompleteSpecificationsAreRejected` | Incomplete Specifications Are Rejected | data provider |

### `tests/Unit/Data/PhoneTest.php` (5)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 8 | `testNationalAndLocalNanpFormatting` | National And Local Nanp Formatting |  |
| 21 | `testEmptyInputAndProtectedValueExtensionPoint` | Empty Input And Protected Value Extension Point |  |
| 31 | `testCurrentCountryOneAndTextExtensionExtractionFailures` | Current Country One And Text Extension Extraction Failures | **known defect KD-50** |
| 45 | `testCurrentInternationalClassificationLeavesComparisonAssignmentsUnset` | Current International Classification Leaves Comparison Assignments Unset | data provider; **known defect KD-51** |
| 60 | `testInternationalDialingPrefixAndExistingFormattedMetadata` | International Dialing Prefix And Existing Formatted Metadata |  |

### `tests/Unit/Data/ScalarTest.php` (6)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 9 | `testScalarValuesAndType` | Scalar Values And Type | data provider |
| 17 | `testNestedScalarAndUnserialization` | Nested Scalar And Unserialization |  |
| 25 | `testArrayAndRuleInputForms` | Array And Rule Input Forms |  |
| 32 | `testEmptyInputsUseFallbackAndUnsupportedInputsRemainNull` | Empty Inputs Use Fallback And Unsupported Inputs Remain Null |  |
| 40 | `testPlainDisplay` | Plain Display |  |
| 45 | `testHtmlAndCommentDisplay` | Html And Comment Display |  |

### `tests/Unit/Data/TypedTest.php` (4)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 10 | `testRelativeAndAbsoluteTypesAndMissingNames` | Relative And Absolute Types And Missing Names |  |
| 21 | `testTypedLoadingUsesTheDiscriminatorOrExplicitOverride` | Typed Loading Uses The Discriminator Or Explicit Override |  |
| 29 | `testMediaDefaultsAndExplicitMetadata` | Media Defaults And Explicit Metadata |  |
| 38 | `testHistoryPreservesProvidedTimestampsAndGeneratesMissingOnes` | History Preserves Provided Timestamps And Generates Missing Ones |  |

### `tests/Unit/Db/ConfigProvidersTest.php` (2)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 23 | `testMongoConfigurationAndOptionalDefaults` | Mongo Configuration And Optional Defaults |  |
| 32 | `testPdoConfigurationValues` | Pdo Configuration Values |  |

### `tests/Unit/Db/LegacyControllerTest.php` (2)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 13 | `testDriverConstructionConnectsWithItsSuppliedParameters` | Driver Construction Connects With Its Supplied Parameters |  |
| 20 | `testStaticReadCurrentlyWarnsAboutItsLiteralSelfAndReturnsNull` | Static Read Currently Warns About Its Literal Self And Returns Null | **known defect KD-05** |

### `tests/Unit/Db/Mongo/CaFileTest.php` (4)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 22 | `testFileAdapterCallsItsDeclaredPathHook` | File Adapter Calls Its Declared Path Hook |  |
| 33 | `testLiteralContentUsesRequestedPathAndReusesWrittenFile` | Literal Content Uses Requested Path And Reuses Written File |  |
| 47 | `testTemporaryCertificateIsRemovedAtNativePhpShutdown` | Temporary Certificate Is Removed At Native Php Shutdown |  |
| 57 | `testFailedFileWriteCurrentlyReturnsNull` | Failed File Write Currently Returns Null | **known defect KD-04** |

### `tests/Unit/Db/Mongo/CachedClientFailureTest.php` (1)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 35 | `testFailedCachedPingEvictsTheActualClientAndRetriesAfterATransportOutage` | Failed Cached Ping Evicts The Actual Client And Retries After A Transport Outage |  |

### `tests/Unit/Db/Mongo/CrudTest.php` (20)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 24 | `testNonDuplicateServerInsertFailureIsRethrownUnchanged` | Non Duplicate Server Insert Failure Is Rethrown Unchanged |  |
| 58 | `testLegacyAndModernCollectionsUseTheSameDatabase` | Legacy And Modern Collections Use The Same Database |  |
| 73 | `testReadsSupportShapesSortingSkippingAndCountOnly` | Reads Support Shapes Sorting Skipping And Count Only |  |
| 85 | `testClassAndCloneRecordsAndCallbacks` | Class And Clone Records And Callbacks |  |
| 98 | `testPrefixReadsAndNamespacedCollections` | Prefix Reads And Namespaced Collections |  |
| 105 | `testModernClientOptionAliasesAndExplicitDatabaseNames` | Modern Client Option Aliases And Explicit Database Names |  |
| 116 | `testExpiredCachedPingUsesTheRealDatabaseAndKeepsTheClient` | Expired Cached Ping Uses The Real Database And Keeps The Client |  |
| 126 | `testCreateUpdateAndDeleteRoundTrip` | Create Update And Delete Round Trip |  |
| 136 | `testCreateSubcollectionsUpsertsAndDuplicateKeys` | Create Subcollections Upserts And Duplicate Keys |  |
| 150 | `testDocumentedCollectionNamespaceAliasForCreate` | Documented Collection Namespace Alias For Create |  |
| 155 | `testReplacementOperatorAndNamespacedWrites` | Replacement Operator And Namespaced Writes |  |
| 168 | `testUpdatedRecordCountUsesTheRealAdapterWriteResult` | Updated Record Count Uses The Real Adapter Write Result |  |
| 172 | `testUpdateCountModesForReplacementAndOperators` | Update Count Modes For Replacement And Operators |  |
| 180 | `testInvalidDocumentWritePreservesTheDriverException` | Invalid Document Write Preserves The Driver Exception |  |
| 185 | `testLoadDefaultsAliasesAndStartWindow` | Load Defaults Aliases And Start Window |  |
| 200 | `testLoadOptionsAreValidated` | Load Options Are Validated | data provider |
| 210 | `testInvalidReadParametersFail` | Invalid Read Parameters Fail | data provider |
| 218 | `testMissingRecordsThrow` | Missing Records Throw |  |
| 222 | `testMissingDeletesAndInvalidWriteParameters` | Missing Deletes And Invalid Write Parameters |  |
| 229 | `testWriteParameterValidation` | Write Parameter Validation | data provider |

### `tests/Unit/Db/Mongo/DriverTest.php` (18)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 51 | `testUriAndOptionsDerivationDoesNotNeedADatabase` | Uri And Options Derivation Does Not Need A Database |  |
| 66 | `testCollectionNamesAndDatabaseFallbacks` | Collection Names And Database Fallbacks |  |
| 75 | `testInvalidCollectionNamesFail` | Invalid Collection Names Fail | data provider |
| 80 | `testCollectionNameMustBeAvailable` | Collection Name Must Be Available |  |
| 84 | `testDatabaseMustBeAvailableWithoutInterfaceOrFallback` | Database Must Be Available Without Interface Or Fallback |  |
| 88 | `testDatabaseNameMustHaveFallback` | Database Name Must Have Fallback |  |
| 93 | `testInvalidDatabaseProviderFailsWithAUsableException` | Invalid Database Provider Fails With A Usable Exception | data provider |
| 99 | `testInvalidCommandsFailBeforeConnectionAccess` | Invalid Commands Fail Before Connection Access | data provider |
| 105 | `testAffectedRecordFailuresPreserveCurrentPolicy` | Affected Record Failures Preserve Current Policy | data provider |
| 116 | `testAffectedRecordCountIsAnInteger` | Affected Record Count Is An Integer |  |
| 121 | `testInvalidCollectionNameFailsBeforeDatabaseAccess` | Invalid Collection Name Fails Before Database Access |  |
| 125 | `testExplicitLazyClientDefersNetworkFailureUntilTheFirstCommand` | Explicit Lazy Client Defers Network Failure Until The First Command |  |
| 133 | `testEagerConnectionRetriesRealNetworkFailuresAndDoesNotCacheThem` | Eager Connection Retries Real Network Failures And Does Not Cache Them |  |
| 143 | `testLazyClientsAreReusedWithoutRecordingASuccessfulPing` | Lazy Clients Are Reused Without Recording A Successful Ping |  |
| 150 | `testCollectionInterfaceRejectsManualNameChanges` | Collection Interface Rejects Manual Name Changes |  |
| 154 | `testShortnameInterfaceRejectsManualNameChanges` | Shortname Interface Rejects Manual Name Changes |  |
| 158 | `testInvalidDatabaseCollectionFactoryIsRejected` | Invalid Database Collection Factory Is Rejected |  |
| 167 | `testPingOffsetIsSanitizedAndStoredWithClientCache` | Ping Offset Is Sanitized And Stored With Client Cache |  |

### `tests/Unit/Db/Pdo/ConnectionTest.php` (6)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 25 | `testEquivalentParametersReuseTheConnectionAndForceNewIsIsolated` | Equivalent Parameters Reuse The Connection And Force New Is Isolated |  |
| 41 | `testInterfaceSuppliesConnectionParameters` | Interface Supplies Connection Parameters |  |
| 47 | `testInvalidDriverDsnIsWrappedAsConnectError` | Invalid Driver Dsn Is Wrapped As Connect Error |  |
| 53 | `testMissingConnectionParametersAreReported` | Missing Connection Parameters Are Reported |  |
| 59 | `testLoadStatementMapsRuleArrays` | Load Statement Maps Rule Arrays |  |
| 67 | `testIncompleteMultipleArgumentRulesAreRejected` | Incomplete Multiple Argument Rules Are Rejected |  |

### `tests/Unit/Db/Pdo/FileConfigTest.php` (1)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 21 | `testConfiguredSqlRootAndDriverOptionOverloadUseRealSqlite` | Configured Sql Root And Driver Option Overload Use Real Sqlite |  |

### `tests/Unit/Db/Pdo/PdoTest.php` (7)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 8 | `testBareRecordFlagsMapRowsToGenericData` | Bare Record Flags Map Rows To Generic Data |  |
| 32 | `testPreparedStatementsRemainUnexecutedUnlessRequested` | Prepared Statements Remain Unexecuted Unless Requested |  |
| 40 | `testAliasesBindingAndExecuteFlags` | Aliases Binding And Execute Flags |  |
| 54 | `testInvalidMapParameters` | Invalid Map Parameters | data provider |
| 65 | `testSqlFilesWithExplicitRootOrNoRoot` | Sql Files With Explicit Root Or No Root |  |
| 78 | `testBadSqlPathsReportPathAndReason` | Bad Sql Paths Report Path And Reason | data provider |
| 102 | `testRecordsAndCallbacksUseNativeSqliteResults` | Records And Callbacks Use Native Sqlite Results |  |

### `tests/Unit/Db/Pdo/StatementTest.php` (9)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 41 | `testPreparedSqlBindsCriteriaAndMapsColumnsAndOrdering` | Prepared Sql Binds Criteria And Maps Columns And Ordering |  |
| 51 | `testLimitsDefaultSkipAndStringCriteria` | Limits Default Skip And String Criteria |  |
| 60 | `testCriteriaJoinHashAndStructuredRelationsProduceExecutableJoins` | Criteria Join Hash And Structured Relations Produce Executable Joins |  |
| 76 | `testInvalidStructuralRules` | Invalid Structural Rules | data provider |
| 95 | `testEveryExplicitFetchOptionCurrentlyThrows` | Every Explicit Fetch Option Currently Throws | data provider; **known defect KD-03** |
| 104 | `testFetchCallbackConsumesActualRowsThroughABufferedNativeStatement` | Fetch Callback Consumes Actual Rows Through A Buffered Native Statement |  |
| 111 | `testExecutedRowsWithoutACallbackStillReferenceUndefinedDataClass` | Executed Rows Without A Callback Still Reference Undefined Data Class | **known defect KD-01** |
| 117 | `testDeclaredJoinClauseCannotContainAStatementSeparator` | Declared Join Clause Cannot Contain A Statement Separator |  |
| 124 | `testExecuteCurrentlyReportsNotFoundForSqliteSelectRowCount` | Execute Currently Reports Not Found For Sqlite Select Row Count | **known defect KD-02** |

### `tests/Unit/Db/RabbitMqTest.php` (10)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 55 | `testInitialConnectionAndChannelAreReturnedAndReused` | Initial Connection And Channel Are Returned And Reused |  |
| 69 | `testChannelCanEstablishItsOwnConnection` | Channel Can Establish Its Own Connection |  |
| 79 | `testQueueDeclarationAndPublishUseDocumentedParameters` | Queue Declaration And Publish Use Documented Parameters |  |
| 94 | `testExplicitRoutingMessageAndCommandPaths` | Explicit Routing Message And Command Paths |  |
| 109 | `testProviderOverridesDefaultsAndTrimsStringParameters` | Provider Overrides Defaults And Trims String Parameters |  |
| 116 | `testMissingConnectionParametersAreRejected` | Missing Connection Parameters Are Rejected | data provider |
| 123 | `testMissingHostIsRejectedBeforeNetworkConnection` | Missing Host Is Rejected Before Network Connection |  |
| 128 | `testEmptyMessageIsRejected` | Empty Message Is Rejected |  |
| 137 | `testMissingQueueIsRejected` | Missing Queue Is Rejected |  |
| 143 | `testConfigNamespaceSuppliesPresentKeysAndKeepsDefaults` | Config Namespace Supplies Present Keys And Keeps Defaults |  |

### `tests/Unit/Input/MapTest.php` (4)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 9 | `testArrayBecomesProperties` | Array Becomes Properties |  |
| 16 | `testObjectBecomesProperties` | Object Becomes Properties |  |
| 26 | `testScalarGoesToData` | Scalar Goes To Data |  |
| 32 | `testEmptyInputHasNoProperties` | Empty Input Has No Properties |  |

### `tests/Unit/Lib/AppErrorTest.php` (3)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 9 | `testSummariesPreserveDataAndExposeCurrentFormatting` | Summaries Preserve Data And Expose Current Formatting | data provider |
| 25 | `testLargePayloadSummariesAreTruncatedAndResourcesAreDescribed` | Large Payload Summaries Are Truncated And Resources Are Described |  |
| 34 | `testMissingParameterNamesAcceptStringsListsAndStringableMembers` | Missing Parameter Names Accept Strings Lists And Stringable Members | data provider |

### `tests/Unit/Lib/LibTest.php` (6)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 7 | `testDependencyMessagesIncludeOptionalMissingClassDetails` | Dependency Messages Include Optional Missing Class Details |  |
| 17 | `testRequiredValuesAndLists` | Required Values And Lists |  |
| 25 | `testInvalidRequiredValues` | Invalid Required Values | data provider |
| 37 | `testPresentClassDependenciesPass` | Present Class Dependencies Pass |  |
| 43 | `testMissingClassDependenciesRetainTheNormalizedClassSet` | Missing Class Dependencies Retain The Normalized Class Set | data provider |
| 54 | `testBadParameterMetadata` | Bad Parameter Metadata |  |

### `tests/Unit/Orm/LegacyFieldErrorTest.php` (1)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 8 | `testLegacyFieldFileCanBeIncludedUnderItsActualDeclaredName` | Legacy Field File Can Be Included Under Its Actual Declared Name |  |

### `tests/Unit/Orm/LifecycleTest.php` (6)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 29 | `testUnsupportedOperationsDoNothing` | Unsupported Operations Do Nothing |  |
| 36 | `testCreateReadAndUpdateTrackTheirLifecycle` | Create Read And Update Track Their Lifecycle |  |
| 48 | `testDeleteAndTheCurrentReadDoneGuard` | Delete And The Current Read Done Guard |  |
| 58 | `testStorageHookFailuresExplainTheOperation` | Storage Hook Failures Explain The Operation | data provider |
| 74 | `testExistenceChecksAreCachedOnlyAfterAConcreteAnswer` | Existence Checks Are Cached Only After A Concrete Answer |  |
| 83 | `testKeyAndFieldMappingRetainNamedAndPositionalValues` | Key And Field Mapping Retain Named And Positional Values |  |

### `tests/Unit/Orm/ReportTest.php` (3)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 12 | `testMetadataProviderSuppliesTheSourceAndIsRetained` | Metadata Provider Supplies The Source And Is Retained |  |
| 20 | `testReportAcceptsDatabaseClassNamesWithoutInstantiatingThem` | Report Accepts Database Class Names Without Instantiating Them |  |
| 25 | `testReportRejectsNonDatabaseSources` | Report Rejects Non Database Sources |  |

### `tests/Unit/Tmpl/DataTest.php` (4)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 8 | `testArrayAndObjectMappingIncludeDeclaredAndDynamicValues` | Array And Object Mapping Include Declared And Dynamic Values |  |
| 18 | `testArrayAndMagicPropertyAccessShareTheSameValues` | Array And Magic Property Access Share The Same Values |  |
| 35 | `testScalarConstructorPreservesValues` | Scalar Constructor Preserves Values | data provider |
| 43 | `testEmptyConstructorHasNoDynamicValues` | Empty Constructor Has No Dynamic Values |  |

### `tests/Unit/Tmpl/DesignOutputTest.php` (1)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 11 | `testResolvableOutputForwardsTheOptionalTemplateData` | Resolvable Output Forwards The Optional Template Data |  |

### `tests/Unit/Vendor/EncodingTest.php` (4)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 8 | `testConversionPreservesValidUtf8AndRepairsLegacyBytes` | Conversion Preserves Valid Utf8 And Repairs Legacy Bytes | data provider |
| 18 | `testNestedArraysAndWindowsEncodingAliases` | Nested Arrays And Windows Encoding Aliases |  |
| 27 | `testBomRemovalAndEncodingLabels` | Bom Removal And Encoding Labels |  |
| 39 | `testIconvOptionsUseTheActualExtension` | Iconv Options Use The Actual Extension |  |

### `tests/Unit/Vendor/JsMinifierTest.php` (2)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 8 | `testWhitespaceCommentsStringsAndRegexArePreservedAsNeeded` | Whitespace Comments Strings And Regex Are Preserved As Needed | data provider |
| 37 | `testMalformedProgramsThrowAndRestoreOutputBuffer` | Malformed Programs Throw And Restore Output Buffer | data provider |

### `tests/Unit/Vendor/MarkdownTest.php` (4)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 9 | `testQuoteHandlerRejectsANonmatchingExtensionMarker` | Quote Handler Rejects A Nonmatching Extension Marker |  |
| 18 | `testBothBundledLoadersWorkInAFreshPhpRuntime` | Both Bundled Loaders Work In A Fresh Php Runtime |  |
| 25 | `testSupportedBlockAndInlineSyntax` | Supported Block And Inline Syntax | data provider |
| 83 | `testConfigurationAndRepeatedParserInstances` | Configuration And Repeated Parser Instances |  |

### `tests/Unit/Vendor/YasumiDateQueryTest.php` (5)

| Line | Method | Reads as | Notes |
| --- | --- | --- | --- |
| 22 | `testMissingAndUnparseableDatesUseTheCurrentTimestamp` | Missing And Unparseable Dates Use The Current Timestamp | data provider |
| 31 | `testValidDateAndExplicitYearCurrentlyLeaveTheirBackingFieldsUnset` | Valid Date And Explicit Year Currently Leave Their Backing Fields Unset | **known defect KD-19** |
| 38 | `testProviderCacheCurrentlyDoesNotAttachItsHitToTheNextInstance` | Provider Cache Currently Does Not Attach Its Hit To The Next Instance | **known defect KD-19** |
| 52 | `testRealProviderRejectsTheWrappersRawTimestamp` | Real Provider Rejects The Wrappers Raw Timestamp | data provider; **known defect KD-19** |
| 58 | `testScheduleComparisonUsesActualNativeDateTimeObjects` | Schedule Comparison Uses Actual Native Date Time Objects |  |
