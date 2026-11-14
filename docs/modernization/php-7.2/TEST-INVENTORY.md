# Test inventory

Every test in the `unit` suite, grouped by file. **Generated; do not edit by hand.**
Regenerate with `bin/test inventory [KNOWN-DEFECTS.md] > TEST-INVENTORY.md` (or
`docker/bin/test inventory ...` to run it in Docker), which lists the suite with
PHPUnit's `--list-tests-xml` and converts it with
`tests/tools/inventory.php`.

- 777 test methods in 92 files, running 1421 test cases.
- 123 methods use a data provider (one case per data set).
- 1 methods still assert defective behavior (`@group known-defect`); KD IDs are from `docs/modernization/php-7.2/active/KNOWN-DEFECTS.md`.
- Each entry is a test method with its line in the file; the method name is the test's claim.
- Groups `services`, `mongo` and `rabbitmq` mark tests that need those containers.

## Files

### `tests/Unit/Api/ControllerTest.php` (15)

- `testMethodDispatchHeadTraceAndNoInterface` (line 35)
- `testAnyInterfaceFallsBackToSpecificMethodOrUsesItsOwnResponse` (line 49)
- `testResponseHandlerCanObserveOrReplaceResponses` (line 61)
- `testConstructorMapsInputsAndInvokesSuccessAndAlwaysCallbacks` (line 70): 4 data-provider cases
- `testStringMethodConstructorAndDirectCallback` (line 79)
- `testResponseExceptionsFromHandlerReachConstructorErrorAndAlwaysCallbacks` (line 86)
- `testReturnedErrorResponseSuppliesItsOwnMetadata` (line 96)
- `testNonErrorFailureStatusUsesNullOptionalErrorMetadata` (line 103)
- `testOrdinaryHandledExceptionReturnsWithoutAnAlwaysResponse` (line 110)
- `testUnhandledConstructorExceptionPropagates` (line 116)
- `testInvalidResponseIsRejected` (line 121)
- `testRequestMethodGetterReturnsTheDispatchedMethod` (line 127)
- `testInvalidFallbackMethodIsRejected` (line 135)
- `testUnknownMethodIsRejected` (line 139)
- `testMissingMethodRaisesMissingMethod` (line 144)

### `tests/Unit/Api/ResponseTest.php` (15)

- `testSuccessAndExplicitStatusMapping` (line 35)
- `testErrorMessagesDataAndStatusCodes` (line 53): 6 data-provider cases
- `testNotFoundPreservesFalsyAndAlreadyMessagedPayloads` (line 69)
- `testBadRequestAddsMessageToArrayOrObjectWithoutOverwritingIt` (line 78)
- `testResponseExceptionsPreserveTheResponseAndStatus` (line 86)
- `testUnknownErrorWithoutClassCallerGetsGenericMessage` (line 97)
- `testUnknownErrorWithClassCallerNamesTheCallingClass` (line 103)
- `testGeneratedMessagesNameTheClassThatCreatedTheResponse` (line 109)
- `testDefaultTraceOffsetNamesTheDirectCaller` (line 118)
- `testResponsesCreatedOutsideAnyClassNameNoCaller` (line 122)
- `testTraceOffsetLandingOnAFunctionNamesNoCaller` (line 128)
- `testMissingStatusReportsTheResponseAndInvalidValue` (line 139)
- `testHeadersExposeFieldAndValueAndReplaceDuplicateFields` (line 148)
- `testAnyMethodFallbackResourcesReturnTheirStatusAndMessage` (line 165)
- `testEmptyHeaderFieldThrowsItsDeclaredLegacyException` (line 175)

### `tests/Unit/Api/StatusTest.php` (4)

- `testConcreteStatusesProvideExpectedCodeAndMessage` (line 17): 10 data-provider cases
- `testCodeClassesAndNumericInputBoundaries` (line 33)
- `testFormattingUsesTheCodeClassMetadata` (line 46)
- `testUnknownCodeNumberFormatsAsAnEmptyString` (line 61)

### `tests/Unit/Asset/AssetTest.php` (29)

- `testSocialProfileAssetsResolveUserBasesAndIgnoreResources` (line 72)
- `testUrlsResourcesHashesAndInvocation` (line 86)
- `testExplicitHashesAndInlineScriptSource` (line 106)
- `testFilesystemPathsExistenceAndDataUris` (line 121)
- `testAbsoluteSystemPathsAreCanonicalized` (line 132)
- `testAHtmlCallbackAloneIsRegisteredAsOutput` (line 140)
- `testFilesystemPathFailures` (line 146): 5 data-provider cases
- `testLinkedTagsFileContentsAndDeprecatedAliases` (line 162)
- `testInvalidFileContentTypeIsRejected` (line 175)
- `testDirectoryFlagsRequireDirectoriesAndAcceptRealDirectories` (line 182)
- `testOverriddenSystemPathsAndReadFailuresRemainGuarded` (line 191): 3 data-provider cases
- `testExplicitCssContentAndExtensionFallbackTags` (line 200)
- `testHtmlSpecialAcceptsImplicitPositionAndSingleItemsWithoutExecutingCycles` (line 205)
- `testUnsupportedContentRequestsFailClearly` (line 217)
- `testExplicitOutputAssetsResourcesAndCallbacksAreRegistered` (line 227)
- `testRegistrationsDeduplicateAndRespectResourceAndPositionFilters` (line 237)
- `testInvalidRegistrationPositionFails` (line 253): 2 data-provider cases
- `testDependenciesRunBeforeParentsAndAreOnlyEmittedOnce` (line 259)
- `testHtmlCallbacksAndArraysAreRegistered` (line 272)
- `testMultipleHtmlItemsPrintInOrderWithoutASelfDependency` (line 283)
- `testRegisteringOutputForAnExistingAssetKeepsTheFirstWithoutASelfDependency` (line 290)
- `testOutputAssetsGetAnOutputHashUnlessTheCallerChoosesOne` (line 298)
- `testDependencyGroupsWithoutUsableNestedDependenciesRegisterOnlyThemselves` (line 308)
- `testDependsTakesAnyNumberOfDependencies` (line 315)
- `testFileAssetHasNoInlineSourceToPrint` (line 324)
- `testInlineSourcePrintsWithoutAFilePath` (line 329)
- `testBuiltinDependenciesAreRegisteredOncePerAssetClass` (line 334)
- `testResolvedHandlerCanRewriteResources` (line 340)
- `testStringConversionReportsUrlFailureAsNotice` (line 347)

### `tests/Unit/Asset/CascadeTest.php` (4)

- `testFullAndAncestorCascadeClassesResolve` (line 31)
- `testControllerResolutionSubstitutesOnlyInstantiableClasses` (line 46)
- `testArrayDependencyGroupsPreserveTheirOrder` (line 57)
- `testNestedDependencyInputsAndParameterForms` (line 63)

### `tests/Unit/Asset/LegacyOptionsTest.php` (3)

- `testFlagsSupportListAndAssociativeForms` (line 12)
- `testFlagsAreOnlyReadFromAnArrayOfFlags` (line 22)
- `testOptionsReturnPresentValuesOrFallback` (line 30)

### `tests/Unit/Cloud/AuthorizeCustomerTest.php` (14)

- `testNativeLegacyClientIsPreparedAndCachedWhenSubclassProvidesTheHook` (line 62)
- `testGetMapsProfileAndMissingProfileReturnsNull` (line 73)
- `testProfileAliasDoesNotReplaceExplicitId` (line 84)
- `testCreateForwardsValidationMode` (line 93): 7 data-provider cases
- `testExplicitPaymentObjectIsForwarded` (line 111)
- `testInvalidRequestsStopBeforeClientCalls` (line 119): 18 data-provider cases
- `testUpdateAndDeleteForwardIds` (line 148): 2 data-provider cases
- `testClientRejectionBecomesResponseError` (line 160): 4 data-provider cases
- `testRetiredConvenienceOperationsFailClearlyWithoutCallingTheGateway` (line 174): 6 data-provider cases
- `testPaymentMethodCreationAndUpdateForwardValidatedIds` (line 197)
- `testMissingPaymentIdDoesNotRaiseAnIndexZeroNotice` (line 207)
- `testPaymentMethodRejectionUsesExistingResponseError` (line 217): 2 data-provider cases
- `testTransactionSpecificationsNormalizeAndReachTheClient` (line 228): 15 data-provider cases
- `testTransactionClientRejectionIsPropagatedBeforeDecoding` (line 254)

### `tests/Unit/Cloud/AuthorizeDataTest.php` (13)

- `testIntervalNormalizesScalarLengths` (line 17): 6 data-provider cases
- `testScheduleDefaultsAndScalarOccurrenceNormalization` (line 24): 6 data-provider cases
- `testNewScheduleUsesCurrentTimezoneAndInvalidTimezoneClearsDate` (line 33)
- `testStartDateIsNormalizedToIsoFormat` (line 43)
- `testValidTimezoneConversionUsesNativeDateTimeZone` (line 51)
- `testSubscriptionCacheRetainsMappedMetadata` (line 56)
- `testSubscriptionErrorsRetainTheirIdentifiersAndLegacySpellings` (line 61)
- `testSubscriptionPaymentAdaptersCopyFieldsIntoRealSdkDtos` (line 71)
- `testResponseSubclassCanSupplyStatusInteger` (line 95): 6 data-provider cases
- `testFailureDetailsComeFromSdkUnlessAlreadyProvided` (line 105)
- `testUnexpectedResponseRetainsOptionalDetails` (line 117)
- `testFactoryMapsActualSdkCodesToResponseKindsAndStatus` (line 125): 4 data-provider cases
- `testUnknownSdkResponseCodeStillReturnsNull` (line 137)

### `tests/Unit/Cloud/AuthorizeSubscriptionTest.php` (19)

- `testNativeArbConstructionUsesTheApplicationPreparationHookAndCachesItsClient` (line 72)
- `testExistingSubscriptionOperationsRequireTheirIdentifier` (line 80): 5 data-provider cases
- `testUpdateAmountAndCancelPassValidatedIdentifiersToTheirClient` (line 86)
- `testRetiredGetTransactionFailsClearlyWithoutCallingTheGateway` (line 95)
- `testUpdateAmountRetainsItsExistingIntegerRestriction` (line 104): 2 data-provider cases
- `testStatusAndUpdatabilityUseTheExistingStatusMethod` (line 110): 3 data-provider cases
- `testGatewayRejectionsUseTheDeclaredResponseError` (line 118): 5 data-provider cases
- `testPaymentFormsProduceRealSdkSubscriptionObjects` (line 129): 6 data-provider cases
- `testInvalidPaymentShapesFailBeforeClientTransmission` (line 146): 4 data-provider cases
- `testCreateMapsItsFieldsAndReturnsTheSubscriptionId` (line 159)
- `testCreateRejectsMissingOrMalformedDocumentedFields` (line 171): 5 data-provider cases
- `testCreateAcceptsAnExistingScheduleObject` (line 182)
- `testInfoSurfacesAListRejectionAfterSuccessfulStatusLookup` (line 188)
- `testInfoStopsAfterFullPageExceedsDeclaredTotal` (line 194)
- `testInfoCircuitBreakerBoundsRepeatedFullPages` (line 200)
- `testInfoRequestsEachPageInTurn` (line 210)
- `testInfoFindsASubscriptionInActualXmlDetails` (line 220)
- `testInfoMissingTotalUsesUnexpectedResponseWithoutAnIndexZeroWarning` (line 227)
- `testInactiveInfoWithoutAMatchUsesNotFound` (line 232)

### `tests/Unit/Cloud/AuthorizeTest.php` (7)

- `testAuthenticationUsesSubclassCredentials` (line 45)
- `testSuccessfulResponseIsReturnedAndEndpointPassedToController` (line 53)
- `testRejectedResponseRetainsSdkMessages` (line 60)
- `testNullResponseBecomesResponseError` (line 74)
- `testControllerExceptionIsSummarizedAndKeptAsPrevious` (line 80)
- `testResponseWithoutMessagesAndScalarErrors` (line 91)
- `testParameterErrorsExposeDetails` (line 108)

### `tests/Unit/Cloud/AuthorizeTransactionTest.php` (6)

- `testTransactionKindsAreLoadableTypedData` (line 17): 3 data-provider cases
- `testLegacyClassNameHookStillUsesItsOriginalCustomerPrefix` (line 27)
- `testSdkAdaptersAcceptOnlyTheirMatchingType` (line 32): 3 data-provider cases
- `testAdapterMapsEveryDeclaredSdkField` (line 45)
- `testOnlyTrueValidationPasses` (line 53)
- `testLegacyPaymentTypeNamesAutoloadWithoutRenamingTheirImplementations` (line 65)

### `tests/Unit/Cloud/AwsConfigTest.php` (1)

- `testS3ClientConfigurationUsesRealSdkWithoutMakingRequests` (line 21)

### `tests/Unit/Cloud/AwsTest.php` (8)

- `testExplicitClientIsCheckedAndCached` (line 41)
- `testProviderClientIsResolvedOnce` (line 47)
- `testMissingClientReportsUnavailable` (line 53)
- `testBodyUploadCanonicalizesKeysAndWaitsForTheUploadedObject` (line 58): 5 data-provider cases
- `testSmallFileUploadUsesFileAndOptionalContentType` (line 65)
- `testFileUploadRejectsInvalidPathsBeforeClientCalls` (line 76): 3 data-provider cases
- `testS3ParameterMappingUrlsAndBodySetterUsingAnExplicitClientBoundary` (line 88)
- `testConstructorUploadAndFileSetterUseTheSameBoundary` (line 101)

### `tests/Unit/Cloud/IpInfoDbTest.php` (6)

- `testCacheHitUsesStoredRecordWithoutHttp` (line 40)
- `testCachedRecordExpiresExactlyAtItsTimeToLive` (line 52)
- `testMissingOrExpiredCacheFetchesMapsAndStoresResponse` (line 72): 3 data-provider cases
- `testHttpFailureCarriesStatusContext` (line 92)
- `testTransportFailureCarriesCurlContext` (line 104)
- `testRetiredStaticLoadFailsClearlyWithoutTouchingTheCache` (line 117)

### `tests/Unit/Cloud/S3MultipartTest.php` (4)

- `testThresholdRoutesToRealSdkMultipartWorkflow` (line 49)
- `testMultipartUploadKeepsTheContentTypeAndThresholdStaysBelowTheSinglePutLimit` (line 59)
- `testPartFailureAbortsTheStartedUploadAndWrapsSdkFailure` (line 68)
- `testInitiationFailureDoesNotAttemptAbortWithoutAnUploadId` (line 81)

### `tests/Unit/Cloud/TwitterUserInfoTest.php` (2)

- `testKnownFieldsAreMappedAndExcludedFromExtraFields` (line 6)
- `testFieldsAddedAfterConstructionReturnNamesOrValues` (line 14)

### `tests/Unit/Core/AlertDefaultsTest.php` (2)

- `testDefaultBrokerLoadsItsConfigurationAndUsesTheRealLocalRabbitService` (line 42): groups: services, rabbitmq
- `testSubscriptionStorageRequiresAnExplicitProvider` (line 47)

### `tests/Unit/Core/AlertTest.php` (6)

- `testQueueProviderSuffixAndFallback` (line 52)
- `testSubscriptionsStoreExpirationAndUniqueId` (line 61): 6 data-provider cases
- `testListenerDecodesMessagesAndRefreshesExpiryOnTimeout` (line 76): 2 data-provider cases
- `testRetiredPublisherFailsBeforeInvokingCallbacksOrChangingSubscriptions` (line 95)
- `testRetiredPthreadScaffoldingNoLongerDeclaresAThreadClass` (line 104)
- `testUnimplementedLogAndSubscriptionListenerMethodsAreNoOps` (line 108)

### `tests/Unit/Core/BadInterfaceTest.php` (2)

- `testBothInterfaceNamesAutoloadAndRemainCompatible` (line 6)
- `testMessageIncludesClassMethodAndReason` (line 15)

### `tests/Unit/Core/CliTest.php` (15)

- `testUninitializedAndEmptyArguments` (line 30)
- `testArgumentsSwitchesAndCommandOverride` (line 41)
- `testOptionFormsAndAliases` (line 55)
- `testShortAliasTakesTheArgumentAfterItsOwnOptionOnly` (line 65)
- `testResourceIndexZeroIsAllowedAndShortOptionValuesAreEnumerated` (line 73)
- `testUnusableResourceIndexFallsBackToTheFirstArgument` (line 82): 4 data-provider cases
- `testEnumerationsMatchDocumentedExample` (line 88)
- `testInputLineCallbacks` (line 96)
- `testWrappedLinesAndWidthHandlers` (line 104)
- `testPrintAndDumpHandlers` (line 114)
- `testDefaultPrintAndDumpOutput` (line 122)
- `testLineAndErrorHandlers` (line 131)
- `testNormalQuietAndErrorOutput` (line 140)
- `testMissingArgvRaisesARuntimeException` (line 146)
- `testGetoptWrapperUsesTheReturnedOperandIndex` (line 154)

### `tests/Unit/Core/Collection/ConstraintTest.php` (9)

- `testClassAndInterfaceConstraintsAcceptMatchingMembers` (line 29)
- `testConstraintsRejectWrongMember` (line 39): 2 data-provider cases
- `testMalformedConstraintsAreRejected` (line 46): 6 data-provider cases
- `testInactiveControllersHaveNoChecks` (line 57)
- `testValidationPassesAndSuccessReceivesFilteredValue` (line 67): 4 data-provider cases
- `testNullFilterLeavesValueUnchanged` (line 76)
- `testValidationRejectsFailureResults` (line 83): 3 data-provider cases
- `testValidationExceptionIsPropagatedUnchanged` (line 90)
- `testValidationAndFilterMayIgnoreMembers` (line 97)

### `tests/Unit/Core/CollectionTest.php` (15)

- `testEmptyAndPopulatedIteration` (line 11)
- `testUnindexedMembersAndIgnoredMembers` (line 27)
- `testReplacingMembersCopiesWrappersAndHonorsMatchingIndexLength` (line 34)
- `testRangeLimitNormalizationAndCallbacks` (line 47)
- `testEmptyRangeRaisesOutOfRange` (line 60)
- `testCallbacksRunOncePerAddedMember` (line 66)
- `testFilterCanIgnoreAMemberAndRunsOnce` (line 75)
- `testValidatorExceptionsPropagate` (line 82)
- `testCopiesAreIndependentAndPreserveIndexZero` (line 89)
- `testCopyDestinationFormsAndFallbacks` (line 101)
- `testFreshCollectionAcceptsMemberReplacement` (line 115)
- `testIterationKeysMixRegisteredNamesAndNumericPositions` (line 124)
- `testRegisteredNumericZeroDoesNotBecomeTheCurrentPosition` (line 133)
- `testRangeLimitIsACountAndClipsAtTheEnd` (line 140)
- `testRangeRejectsStartAtTheMemberCount` (line 155)

### `tests/Unit/Core/ConfigTest.php` (19)

- `testDefaultsBeforeInitialization` (line 62)
- `testUninitializedAccessThrows` (line 69): 3 data-provider cases
- `testInvalidBaseDirectory` (line 81): 3 data-provider cases
- `testLoadsValuesAndCachesThem` (line 98)
- `testPathsAreNormalizedAndBaseNamesAreRemoved` (line 110)
- `testEachConfigFileIsIncludedOnlyOnce` (line 124)
- `testNonStringKeyThrows` (line 135)
- `testMissingKeysAreCachedAndDefaultsPreserveTypes` (line 142)
- `testInvalidPhpConfigIsRejectedAndCanUseDefault` (line 153)
- `testDirectoriesAndUnreadableFilesAreNotLoadedAsConfig` (line 162)
- `testReferenceFilesAreReadOnlyOncePerNamespace` (line 173)
- `testEnumeratesValuesWithReferences` (line 182)
- `testReferenceValuesAndNamespaceCaching` (line 196)
- `testRejectsInvalidReferenceDocuments` (line 208): 4 data-provider cases
- `testMissingReferenceDocumentAndCachedMisses` (line 226)
- `testWholeReferencesAndNestedValues` (line 239)
- `testMissingWholeReferenceReportsTheOriginalKey` (line 257)
- `testInlineReferencesIncludingDuplicatesBooleansAndMalformedTokens` (line 266)
- `testNonScalarInlineReferenceThrows` (line 282)

### `tests/Unit/Core/Curl/ConfigTest.php` (5)

- `testExplicitConfigurationAndDefaults` (line 9)
- `testChromeSelectionProvidesTheChromeUserAgent` (line 23)
- `testInvalidSettingsAreRejected` (line 30): 7 data-provider cases
- `testCachingUsesWritableTemporaryDirectoryByDefault` (line 40)
- `testUnwritableCacheDirectoryIsRejected` (line 46)

### `tests/Unit/Core/Curl/RequestTest.php` (11)

- `testUrlParsingPreservesComponentsAndNoBodyDefaults` (line 7)
- `testInvalidUrls` (line 17): 5 data-provider cases
- `testUrlencodedArrayObjectAndStringBodies` (line 27)
- `testNonStringScalarInUrlencodedModeHasNoBody` (line 36)
- `testJsonBodyAndContentType` (line 40)
- `testJsonEncodingFailureIsRejected` (line 46)
- `testJsonPreservesFalsyBodies` (line 52): 5 data-provider cases
- `testRawModePreservesTheSuppliedBody` (line 63): 6 data-provider cases
- `testRawModeRequiresANonemptyStringContentType` (line 74): 5 data-provider cases
- `testNullRemainsTheNoBodyDefaultForJsonAndRawModes` (line 83)
- `testEmptyUrlencodedQueryStringIsRejected` (line 91)

### `tests/Unit/Core/Curl/ResponseTest.php` (20)

- `testDefaultRequestsSendTheFlatUserAgentAndKeepCurlsMethodChoice` (line 23)
- `testFacadeAcceptsUrlsAndConfigurationObjects` (line 32)
- `testResponseDataStatusesAndTransferOptions` (line 41)
- `testArrayConfigurationAndScalarUrl` (line 55)
- `testFilesAndTemporaryCleanup` (line 64)
- `testCallerProvidedFilePersists` (line 74)
- `testSaveFlagsAndArraySavePaths` (line 84)
- `testCookieOptionsAndAutomaticCookieCleanup` (line 100)
- `testInvalidCookieFile` (line 112): 2 data-provider cases
- `testDomAndXPathResults` (line 118)
- `testInvalidXPathReportsQuery` (line 127)
- `testTransferFailureRetainsErrorDetails` (line 135)
- `testErrorAfterFileTransferStillFails` (line 145)
- `testRobotsRules` (line 155): 6 data-provider cases
- `testRobotsCheckCanDenyOrAllowConstructor` (line 168)
- `testRobotsRulesForAnotherAgentIgnoreTheUserAgentLabel` (line 176)
- `testNoCookieFileIsCreatedUnlessCookiesAreAccepted` (line 183)
- `testFalsyTextBodyInitializesDomOnDemand` (line 192)
- `testMissingTransferInfoRaisesItsDeclaredException` (line 199)
- `testUnavailableSavePathRaisesSystemErrorAfterWarning` (line 206)

### `tests/Unit/Core/DebugTest.php` (13)

- `testDisplayCollectionHelperAcceptsOnlyDisplayData` (line 17)
- `testDefaultKillHandlerExitsWithConfiguredStatus` (line 54)
- `testDisplayIsNotSuppressedByDefault` (line 62)
- `testKillHonorsAPlainExitStatus` (line 67)
- `testKillSanitizesInvalidNestedExitStatusAndEmptyMessage` (line 75)
- `testReturningCustomKillHandlerReachesTheFinalExit` (line 81)
- `testWebDisplayFormattingInAnIsolatedPlatformBranch` (line 88)
- `testSuppressionAndInactiveConfigurationPreventDisplay` (line 100)
- `testDisplayDumpAndMessageEventsCarryCallerAndCreationTime` (line 114)
- `testDisplayAndDumpTriggerOnlySupportedUserErrors` (line 132)
- `testDefaultCliHandlerDisplaysStructuredData` (line 148)
- `testDataDescriptionsUseTheMatchingFormat` (line 156)
- `testCustomKillHandlerReceivesCallerAndConvertedMessage` (line 171)

### `tests/Unit/Core/Encode/CsvTest.php` (6)

- `testFields2csvJoinsScalars` (line 9)
- `testFields2csvKeepsNumbers` (line 14)
- `testFields2csvOfNoFieldsIsAnEmptyLine` (line 20)
- `testFields2csvBlanksNonScalars` (line 27)
- `testFields2csvQuotesCommaAndQuote` (line 32)
- `testSerializeAndUnserializeAreNoOps` (line 38)

### `tests/Unit/Core/EventFacadeTest.php` (1)

- `testFacadeSharesFactoryAndDeliversHandlerResults` (line 7)

### `tests/Unit/Core/EventFactoryTest.php` (6)

- `testCreationHandlersListenersAndStoredData` (line 11)
- `testOnDemandCreationAndPerTriggerCallbacks` (line 27)
- `testDuplicateCanBeIgnored` (line 39)
- `testDuplicateRaisesSpecificException` (line 45)
- `testUnknownEventTypeRaisesSpecificException` (line 51)
- `testNonTriggerableTypeDoesNotInvokeHandler` (line 56)

### `tests/Unit/Core/EventResolverTest.php` (1)

- `testSharedNamespaceDeliversHandlerResultsToListeners` (line 7)

### `tests/Unit/Core/EventTest.php` (8)

- `testNamedEmptyEventAndMultipleListeners` (line 11)
- `testHandlerReplacementRunsBeforeListeners` (line 22)
- `testFailureAbortsListeners` (line 31)
- `testCallAndEventAndStoredDataAreAggregatedForHandler` (line 39)
- `testListenerInputPriority` (line 55): 4 data-provider cases
- `testStoredNullCanBeReplacedWithNoOverwriteFlag` (line 73)
- `testInvalidName` (line 84): 2 data-provider cases
- `testInvalidDataKey` (line 94): 2 data-provider cases

### `tests/Unit/Core/ExceptionTest.php` (6)

- `testLegacyExceptionBaseKeepsExplicitCodesAndDerivesMissingCodes` (line 13)
- `testExplicitAndDerivedCodesAndMetadata` (line 23)
- `testUnavailableFeaturesExplainTheirContext` (line 36): 6 data-provider cases
- `testUnavailableFeatureNamesTheClassAndMethodThatRaisedIt` (line 41)
- `testUnavailableFeatureRaisedByAPlainFunctionNamesOnlyTheFunction` (line 47)
- `testUnavailableFunctionContext` (line 57)

### `tests/Unit/Core/Html/EncodeTest.php` (6)

- `testTypeLabels` (line 10): 11 data-provider cases
- `testScalarEscapingAndBooleanMarkers` (line 20)
- `testArraysObjectsKeysAndCustomTags` (line 30)
- `testNestedItemsAreIndentedPerLevelAndIntegerKeysAreNotShown` (line 43)
- `testResourcesUseDumpAndUnknownType` (line 52)
- `testUnserializationIsExplicitlyNotReady` (line 60)

### `tests/Unit/Core/Html/FormTest.php` (4)

- `testTextAndTextareaInputsEscapeNamesAndValues` (line 7)
- `testNestedFormsApplyNestedTypeMaps` (line 18)
- `testSerializeReturnsTheConstructedForm` (line 27)
- `testDecodeExplicitlyReportsFeatureNotReady` (line 33)

### `tests/Unit/Core/LegacyErrorMessagesTest.php` (20)

- `testErrorsRetainOperationContext` (line 14): 45 data-provider cases
- `testDuplicateKeyRetainsNonScalarValueDescription` (line 69)
- `testConfigReferenceErrorRetainsAllLookupKeys` (line 74)
- `testScalarDefaultErrorAndThreadFailureRetainDetails` (line 81)
- `testIgnoredCollectionMemberRemainsAnEmptyString` (line 92)
- `testUploadAndBrokerErrorsRetainTheOriginalExceptionContext` (line 99)
- `testValidationFailureDescribesTheSuppliedValueAndRule` (line 109): 4 data-provider cases
- `testRouteMissingErrorExposesBothCoordinates` (line 118)
- `testCrudErrorAcceptsUnspecifiedOperation` (line 127)
- `testDuplicateKeyErrorRetainsIndexAndNonScalarValue` (line 132)
- `testDuplicateKeyDescribesANonemptyScalarValue` (line 139)
- `testDuplicateKeyDescribesIndexesWithoutWarnings` (line 145)
- `testBadApiResponseRetainsDocumentedClassAndMethod` (line 159)
- `testSessionErrorsRetainReasonAndExpirationMetadata` (line 165)
- `testCurlSystemErrorRetainsDataAndDetails` (line 173)
- `testNotFoundStatusReportsCode404` (line 179)
- `testXpathErrorRetainsActualLibxmlDetailsWithoutDuplicatingMessage` (line 185)
- `testXpathErrorMessageNamesTheQueryOnceWhateverTheDetails` (line 201)
- `testScalarTypeErrorUsesItsSuppliedTypeWithoutAnUndefinedLocal` (line 206)
- `testLegacySshParameterErrorRetainsItsDeclaredNameAndMessage` (line 213)

### `tests/Unit/Core/LegacyListenerTest.php` (4)

- `testListenerFactoryUsesTheExistingLoaderContractAndSharedFactory` (line 21)
- `testHookablePassesItsEventDataToTheHandler` (line 31)
- `testListenerConstructorImmediatelyInvokesItsCallback` (line 40)
- `testStubReturnsTheListenersItWasGiven` (line 45)

### `tests/Unit/Core/LegacyMappingTest.php` (7)

- `testDeclaredCoreDataFieldsAreMapped` (line 11)
- `testCoreDataMarkerOptsIntoCreatingProperties` (line 15)
- `testCoreDataDoesNotCreateUndeclaredPropertiesByDefault` (line 20)
- `testListKeysAndIndexZeroDefaultFollowCurrentMappingRules` (line 25)
- `testNonObjectInputHasNothingToMap` (line 31)
- `testObjectFieldsMapOntoDeclaredProperties` (line 35)
- `testControllerExportsAreLimitedToFlatDataObjects` (line 42)

### `tests/Unit/Core/MapperTest.php` (17)

- `testReadsPropertiesByNameAndReflection` (line 28)
- `testReadErrors` (line 37): 3 data-provider cases
- `testIgnoredReadsReturnConfiguredSentinel` (line 52)
- `testWritesDeclaredProtectedAndReflectionProperties` (line 62)
- `testWriteErrors` (line 77): 4 data-provider cases
- `testIgnoredWritesAndDynamicProperties` (line 85)
- `testExportsPublicPropertiesAndOmitsNullFromStdClass` (line 101)
- `testListsAndObjectsPopulateProperties` (line 113)
- `testArgumentMapping` (line 125): 6 data-provider cases
- `testParamsSupportNamedPositionalAndEmptyLists` (line 144)
- `testParamsWithNumericKeysAreAPositionalList` (line 155)
- `testVisitReadsAndUpdatesSelectedPublicProperties` (line 163)
- `testRetiredPrivateReadOptionsRaiseAClearMappingError` (line 181): 3 data-provider cases
- `testPrivateReadFlagIsHarmlessForPublicOnlyObjects` (line 191)
- `testProtectedVisitPreservesReflectionFailure` (line 201)
- `testPrivateVisitIsIgnoredEvenWithObsoleteFlags` (line 207)
- `testMissingVisitThrowsCannotGetProperty` (line 214)

### `tests/Unit/Core/Md/ConvertTest.php` (4)

- `testMarkdownStringInstanceAndFileProduceEquivalentHtml` (line 7)
- `testMarkdownExtraTablesAndFencedCode` (line 22)
- `testNonStringSourceIsRejected` (line 29)
- `testMissingFileRaisesInvalidSource` (line 34)

### `tests/Unit/Core/RandStrTest.php` (10)

- `testStrDefaultLengthAndCharset` (line 9)
- `testDefaultPoolDrawsFromDigitsAndBothCases` (line 15)
- `testStrCustomCharpoolAndLength` (line 22)
- `testStrGenerateAndInvoke` (line 36)
- `testInvokingAnInstanceReusesItsPoolAndLength` (line 47)
- `testStrGetStringWithExplicitLength` (line 56)
- `testStrOptionsAsList` (line 64)
- `testStrWithoutLowerAlpha` (line 71)
- `testStrNonStringCharpoolThrowsBadConfig` (line 77)
- `testStrTooSmallCharpoolThrows` (line 83)

### `tests/Unit/Core/RandTest.php` (11)

- `testGetWithinBounds` (line 9)
- `testGetMinEqualsMax` (line 22)
- `testZeroMaximumMeansTheDefaultMaximum` (line 29)
- `testGetRejectsMaxLessThanMin` (line 38)
- `testGetRejectsNegativeMin` (line 44)
- `testGetRejectsNonIntParams` (line 50)
- `testGetRejectsBadMaxParameters` (line 62)
- `testBadParamMessageNamesTheParameterAndOptionalDetails` (line 73)
- `testBytesLength` (line 79)
- `testInvokeAndGenerateMatchBounds` (line 88)
- `testInvocationWithoutArgumentsUsesTheConstructedBounds` (line 100)

### `tests/Unit/Core/Route/ControllerTest.php` (14)

- `testResolutionInjectsInputsLifecycleAndStatus` (line 63)
- `testSameWeightIsSkippedAndLaterWeightsResolve` (line 76)
- `testTraversalCanResolveAParentAndItsChild` (line 82)
- `testRulesIterateOverTheRouteByDefault` (line 90)
- `testEmptyPathSegmentsResolveLikeTheCleanResource` (line 100): 5 data-provider cases
- `testResolverCanConsumeUnmatchedSuffix` (line 120)
- `testResourceTransformsAndNullTransformFallback` (line 128)
- `testCyclesAndDemands` (line 136): 4 data-provider cases
- `testIgnoredOrAbstractClassesDoNotResolve` (line 142): 4 data-provider cases
- `testUnresolvedAndProviderClassesDoNotStopIteration` (line 147)
- `testDebugSuppressInterface` (line 153)
- `testStringFactoryAndBaseInterface` (line 158)
- `testReservedNodesAndCustomSetsAreCachedSeparately` (line 163)
- `testInvalidControllerParameters` (line 172): 4 data-provider cases

### `tests/Unit/Core/Session/SessionTest.php` (18)

- `testStartRefreshIdAndSnapshot` (line 33)
- `testStoredValuesCanBeReadAndRemoved` (line 49)
- `testNonceCanBeConsumedOnlyOnce` (line 58)
- `testNonceWithoutDataOrExpiryAndFalsyData` (line 66)
- `testMissingNonceIsRejected` (line 75)
- `testExpiredNonceIsRejectedWithoutSleeping` (line 80)
- `testNonceIsAcceptedAtExactlyItsTtl` (line 91)
- `testNonceIsExpiredOneSecondAfterItsTtl` (line 111)
- `testCorruptNonceTtlIsRejected` (line 119)
- `testInvalidNonceParameters` (line 127): 3 data-provider cases
- `testWeakRandomnessIsRejected` (line 137)
- `testInvalidSessionKey` (line 143)
- `testInvalidRefreshKey` (line 149): 2 data-provider cases
- `testNoCookieStartRequiresExplicitId` (line 154)
- `testDestroyNotifiesListenersAndCanRestart` (line 159)
- `testCookieSessionDestroyReportsCookieCleanup` (line 169)
- `testMissingSessionGlobalIsRejectedEvenWhenActive` (line 179)
- `testFailedStorageRetriesAndReturnsNoId` (line 187)

### `tests/Unit/Core/ShutdownTest.php` (11)

- `testNamedPrioritiesRunInDocumentedOrderAndHandlesAreUnique` (line 23)
- `testZeroIsAWeightAndEmptyValuesJoinTheDefaultQueue` (line 34)
- `testOutOfRangeWeightsAreRejected` (line 45): 3 data-provider cases
- `testDistinctWeightsRunBeforeDefaultAndAfterFirst` (line 52)
- `testEachCallbackRunsOnlyOnceAcrossRepeatedInvocation` (line 61)
- `testExplicitInvocationIsNotRepeatedAtPhpShutdown` (line 75)
- `testCallbackRegisteredDuringInvocationRunsAtPhpShutdown` (line 84)
- `testEqualWeightsRunNewestFirstWithoutLosingCallbacks` (line 94)
- `testRegisteredCallbackRunsAtActualPhpShutdown` (line 101)
- `testWeightCeilingStopsUnboundedInsertion` (line 106)
- `testWeightCeilingAllowsExactlyTheRemainingSlots` (line 111)

### `tests/Unit/Core/SshTest.php` (34)

- `testUnavailableExtensionIsReported` (line 33)
- `testRequiredConfigurationIsChecked` (line 39): 3 data-provider cases
- `testAuthenticationConfigurationMustBePresent` (line 45)
- `testPublicKeyConfigurationNeedsPrivateKey` (line 49)
- `testDeferredSubclassReportsUninitializedAuthentication` (line 53)
- `testPasswordConnectionAndExecutionUseTheirMatchingTransport` (line 58)
- `testRepeatedConnectIsRejected` (line 66)
- `testConnectionFailureIsReported` (line 70)
- `testPasswordFailureUsesTheExistingFrameworkException` (line 75)
- `testPublicKeyPathRunsRealOpenSslAndRemovesItsTemporaryFiles` (line 81): 2 data-provider cases
- `testProvidedFingerprintIsChecked` (line 93)
- `testFingerprintMismatchIsReported` (line 98)
- `testPublicKeyAuthenticationFailureIsReported` (line 106)
- `testLocalCryptoFailureBoundariesAreReported` (line 112): 4 data-provider cases
- `testOperationsRequireAConnection` (line 127): 6 data-provider cases
- `testFailedRemoteExecutionIsReported` (line 133)
- `testDisconnectIgnoresMissingConnectionOnlyWhenRequested` (line 138)
- `testProcessChecksPreserveZeroExitCodeSemanticsAndLegacyHooks` (line 145)
- `testMissingRemoteProcessThrowsUnlessIgnored` (line 151)
- `testInvalidPidUsesTheLegacyCompatibleParameterException` (line 156)
- `testBackgroundExecutionReturnsPidAndChecksRequestedRuntime` (line 161): 3 data-provider cases
- `testBackgroundExecutionNeedsAPid` (line 168)
- `testKillArgumentsAreValidatedBeforeSendingSignals` (line 174): 11 data-provider cases
- `testProcessCanExitAfterEitherSignal` (line 183)
- `testKillWaitsForTheFirstSignalBeforeSendingTheSecond` (line 190)
- `testAProcessThatNeverExitsReportsTheTimeWaited` (line 202)
- `testSignalAndStillRunningFailuresAreReported` (line 212): 3 data-provider cases
- `testSftpOpenFailuresAreReportedBeforeTheMissingHelper` (line 218)
- `testSftpLocalOpenFailureIsReported` (line 223)
- `testLegacyParameterAliasAutoloadsInAFreshRuntime` (line 228)
- `testSuccessfulReadsRestartTheIdleTimeoutAcrossALongerTransfer` (line 233)
- `testAnIdleTimeoutStopsTheNextReadAndClosesBothStreams` (line 244)
- `testNativeReadsHandleShortEmptyAndZeroBodies` (line 257): 5 data-provider cases
- `testCallbackFailureStillClosesTheLocalAndRemoteStreams` (line 271)

### `tests/Unit/Core/Template/ControllerTest.php` (16)

- `testFailingTemplatesCloseTheirOutputBuffers` (line 90)
- `testDesignRootUsesConfigurationOrItsDocumentedFallback` (line 102)
- `testMarkdownReadFailureIsCaughtByTemplateResolver` (line 119)
- `testAbsolutePhpFileAndDisplayClass` (line 124)
- `testFileReadFailuresReportLibraryExceptions` (line 132): 4 data-provider cases
- `testPhpAndPlainHtmlFilesRenderAndCheckWithoutOutput` (line 146)
- `testUnusablePhpDesignsFallBackToHtml` (line 162)
- `testCheckingADesignNeverPrintsIt` (line 174)
- `testMarkdownAndGeneratedMarkdownFiles` (line 182)
- `testObjectAndClassDesignsReceiveDataAndCheckOnlyFlag` (line 198)
- `testInvalidDesignsAreRejected` (line 212): 4 data-provider cases
- `testDesignBasesPrefixesAndSeparators` (line 218)
- `testFormattingFlagsReplacementsAndOutput` (line 226)
- `testLoggerWritesDesignKindAndNormalizedName` (line 235)
- `testConstructorAndPreparedInterfacesControlDisplayTiming` (line 246)
- `testDefaultDisplayCallbackDelegatesToTemplateRendering` (line 265)

### `tests/Unit/Core/Template/ValidateControllerTest.php` (3)

- `testRetiredControllerFailsClearlyOnceItsArgumentsAreValid` (line 6)
- `testRetiredInputMethodFailsClearly` (line 12)
- `testConstructorRejectsInvalidInputAndCollection` (line 19): 2 data-provider cases

### `tests/Unit/Core/Template/ViewTest.php` (1)

- `testResourceSearchTriggersEventsForMatchingAncestorFiles` (line 19)

### `tests/Unit/Core/Text/EncodeTest.php` (8)

- `testSerializeString` (line 9)
- `testSerializeBoolAndNull` (line 14)
- `testSerializeScalarNumber` (line 22)
- `testSerializeNestedArrayIndents` (line 27)
- `testSerializeHonoursOptions` (line 35)
- `testSerializeObject` (line 47)
- `testSerializeNonPrintableString` (line 53)
- `testUnserializeThrowsFeatureNotReady` (line 60)

### `tests/Unit/Core/Util/Image/DimTest.php` (4)

- `testAliasesRatioAndString` (line 8)
- `testScalingMaintainsRatioAndReturnsOriginalWhenUnnecessary` (line 18)
- `testInstanceScalingModes` (line 28)
- `testInvalidSizes` (line 37): 2 data-provider cases

### `tests/Unit/Core/Util/Image/ImageTest.php` (6)

- `testBlobBase64SaveAndLoadUseRealImages` (line 12)
- `testDimensionExtractionClonesTheImageWithoutChangingItsPage` (line 25)
- `testSingleAxisResizePreservesTheOriginalRatio` (line 34): 6 data-provider cases
- `testMinimumBoundsAndEqualHeightDoNotIgnoreRequiredWidth` (line 49)
- `testSingleAxisConstructorsDelegateToProportionalResizing` (line 61)
- `testBothConstructorBoundsAreProportionalMinimums` (line 71)

### `tests/Unit/Core/Util/ListcheckTest.php` (4)

- `testLastSuppliedValueDefinesTheList` (line 12)
- `testMembershipUsesLooseComparison` (line 24)
- `testFailureInvokesRegisteredCallbackWithFactoryAndValue` (line 31)
- `testFailureCallbackExceptionPropagates` (line 41)

### `tests/Unit/Core/Util/Utf8Test.php` (2)

- `testLatinOneAndAlreadyValidUtf8` (line 7)
- `testEmptyValueAndArrayResultAreNotExposedAsString` (line 15)

### `tests/Unit/Core/Util/UtilityTest.php` (11)

- `testBooleanOptionsAreStrict` (line 27): 6 data-provider cases
- `testMissingOptionsAndScalarModes` (line 36)
- `testCopyPreservesTruthyScalarsAndRecursesArrays` (line 49)
- `testDefaultObjectCopyIsShallowAndIndependent` (line 60)
- `testObjectConversionsRecurseAndStdClassTakesPrecedence` (line 71)
- `testTimecodes` (line 82): 5 data-provider cases
- `testShortTimecodeAndNumericCoercion` (line 88)
- `testRelativeTimeUnits` (line 94): 14 data-provider cases
- `testRelativeTimeParsingDefaultsAndInvalidValues` (line 102)
- `testReservedSegmentsAndCustomDelimiter` (line 113)
- `testCustomRelativeTimeTableMayHaveNoMatchingUnit` (line 119)

### `tests/Unit/Core/Util/ValidationFactoryTest.php` (2)

- `testDefaultConstraintAcceptsThePairsItsHelperBuilds` (line 15)
- `testSubclassConstraintAllowsPairsAndPreservesTheirInputs` (line 25)

### `tests/Unit/Core/Util/ValidationTest.php` (14)

- `testOnlyStrictTruePassesAndStoredStringsAreAvailable` (line 27)
- `testValidationOptions` (line 39)
- `testExplicitRegexConstructorKeepsItsExistingArrayPayload` (line 52)
- `testFactoryInterfaceFailureLoadsThroughBothNames` (line 57)
- `testHistoricalFactoryExceptionAutoloadsInAFreshProcess` (line 64)
- `testRegexMatchingAndMultiplePatterns` (line 70)
- `testRegexConfigurationFailures` (line 79): 4 data-provider cases
- `testInvalidRegexSyntaxProducesItsDescriptiveException` (line 89)
- `testPcreExecutionErrorRaisesPregError` (line 103)
- `testPcreExceptionExplainsNativeEngineErrors` (line 113): 5 data-provider cases
- `testInvalidPatternExceptionIncludesPatternAndNativeCodeName` (line 136)
- `testHostnameDefinerSuppliesAValidPatternWithoutChangingItsRawConstant` (line 144): 8 data-provider cases
- `testSupportedFilterIdsConstructSuccessfully` (line 155)
- `testInvalidFilterUsesItsConcreteException` (line 161)

### `tests/Unit/Core/Util/ValueTest.php` (4)

- `testStringability` (line 9): 7 data-provider cases
- `testResourceCanBeConvertedToStringWithoutClosingIt` (line 17)
- `testScalarRequirementReturnsTheUnchangedScalar` (line 24): 8 data-provider cases
- `testInvalidScalarRequirementsAndFallbacks` (line 30)

### `tests/Unit/Core/UuidTest.php` (14)

- `testV3MatchesRfc4122Vector` (line 11)
- `testV5MatchesRfc4122Vector` (line 20)
- `testV3AcceptsBracedAndUnhyphenatedNamespace` (line 29)
- `testV4FormatVersionAndVariant` (line 36)
- `testGetDefaultsToV4` (line 50)
- `testGetArrayFormV3AndV5` (line 56)
- `testGetWithNamespaceAndVersion4Throws` (line 68)
- `testGetWithNamespaceAndBadVersionThrows` (line 74)
- `testGetWithoutNamespaceRejectsVersionsOtherThanThreeFourOrFive` (line 80)
- `testInvalidNamespaceThrows` (line 87)
- `testInvokeGeneratesTheRequestedVersion` (line 104)
- `testToStringReturnsUuidForConstructorState` (line 114)
- `testConfiguredStateDrivesGenerateAndStringConversion` (line 119)
- `testGenerateUsesConstructorState` (line 135)

### `tests/Unit/Core/Xml/DomTest.php` (15)

- `testSubclassWithoutADocumentHasNoXPathResults` (line 9)
- `testInvalidXmlFileRaisesTheLibraryException` (line 14)
- `testHtmlConversionFailureRestoresLibxmlState` (line 22)
- `testGenericInputFailureReportsThePublicInputContract` (line 35)
- `testXmlLoadDocumentAndIterator` (line 43)
- `testExistingNodeProducesAnIndependentDocument` (line 61)
- `testEmptyDomAndXmlStringConstructor` (line 70)
- `testXmlFileConstructor` (line 77)
- `testHtmlLoadingAndAttributeStripping` (line 85)
- `testMalformedXmlRaisesLibraryException` (line 95)
- `testXPathResultsSupportIterationAndChildAccess` (line 100)
- `testEncoderRejectsUnrelatedXmlFormat` (line 120)
- `testObjectConversionGroupsRepeatedNodesPreservesEmptyFirstValue` (line 124)
- `testObjectConversionUsesNameAndIndexAttributes` (line 132)
- `testObjectConversionCanPreserveMixedParentText` (line 138)

### `tests/Unit/Core/Xml/EncodeTest.php` (21)

- `testScalarDocuments` (line 15): 8 data-provider cases
- `testObjectAndArrayTypes` (line 23)
- `testNestedNodesEscapingAndTypeDetection` (line 34)
- `testInvalidAndNumericKeysUseDefaultNodeAndPreserveKey` (line 44)
- `testMetaOptionsCustomRootChecksumsAndIndentation` (line 54)
- `testNamedNestedStructureAndRealFloatValues` (line 67)
- `testStructureMetadataFallbackForNonScalarMetaOption` (line 73)
- `testNestedArrayAndObjectTypeDetection` (line 79)
- `testDateAndBinaryLookingStringsGetTheirSchemaTypes` (line 85)
- `testIsoDatesAndTimesAreTypedAsDateTime` (line 93): 6 data-provider cases
- `testOtherTextIsAStringEvenWhenStrtotimeParsesIt` (line 102): 9 data-provider cases
- `testSingleLettersInAListAreStrings` (line 112)
- `testLettersAThroughFAreCurrentlyTypedAsHexBinary` (line 119): **known defect KD-77**
- `testArrayTypeDetectionInspectsTheFirstTenKeys` (line 125)
- `testDumpEnabledEmptyAndNumericStringObjectKeys` (line 134)
- `testResourceFallbackWithAndWithoutDumpPreservesWarnings` (line 141)
- `testLegacyIndexAndScalarTypeDecoding` (line 159)
- `testInstanceAndCompatibilityAlias` (line 165)
- `testRoundTripsValuesWithExplicitMetadata` (line 173): 18 data-provider cases
- `testAssocDecodingConvertsObjectsWithoutChangingScalarTypes` (line 189)
- `testLegacyXmlWithoutNewMetadataCanStillBeRead` (line 195)

### `tests/Unit/Data/ErrorDetailsTest.php` (6)

- `testValidationFailureCollectsStringFieldsAndKeepsItsMessage` (line 6)
- `testDataNotFoundRetainsLookupContext` (line 21)
- `testRandomFailurePreservesRequestedLength` (line 29)
- `testMissingOrEmptyDataStaysAnEmptyArray` (line 37)
- `testCliErrorExposesStatusUsageFlagAndData` (line 42)
- `testCloudErrorsExposeParameterAndChildClass` (line 53)

### `tests/Unit/Data/MapperTest.php` (24)

- `testBaseFieldListIsAbsentAndUnknownDumpTypesUseHumanFallback` (line 31)
- `testWebviewDataIsHtmlEncodedOnConstruction` (line 43)
- `testRuleConsumerReceivesConstructedAndExplicitRules` (line 52)
- `testFieldListUsesTheInstanceAndCachesPerClass` (line 60)
- `testConstructorsMapNamedAndPositionalDataAndIgnoreUnknownFields` (line 68)
- `testFieldInspectionAndCallbacks` (line 78)
- `testInvalidFieldName` (line 98)
- `testMissingField` (line 103)
- `testMagicFieldAccessStrippingAndFallbacks` (line 108)
- `testEachFieldAndScalarConversions` (line 124)
- `testRecursiveHtmlEncodingClonesTheData` (line 141)
- `testTypeAndLifecycleInterfaces` (line 152)
- `testStringRepresentations` (line 161)
- `testInvalidStringifierThrows` (line 178): 3 data-provider cases
- `testKeyPrefixMappingPreservesUnmatchedKeysAndExistingMap` (line 184)
- `testInvalidPrefixThrows` (line 192)
- `testEmptyMappingKeyThrows` (line 198)
- `testNumericConversionPreservesNonNumericStrings` (line 205): 4 data-provider cases
- `testDumpFormatsAndStringFailureRecovery` (line 213)
- `testHtmlEncodingPlainObjectsAndUnsupportedResources` (line 231)
- `testUtf8FixOnlyTouchesSelectedStringFields` (line 243)
- `testMapAppliesMissingDefaultsAndOptionalEmptyDefaults` (line 251)
- `testRuleObjectConstructorAndRemapping` (line 264)
- `testInvalidMappingRules` (line 273): 3 data-provider cases

### `tests/Unit/Data/Orm/LegacyOrmTest.php` (4)

- `testStaticFacadeCachesALegalSubclassAndReturnsItsResults` (line 35)
- `testConstructorArrayConfiguresTheDriver` (line 42)
- `testADriverNeedsBothGetAndSet` (line 56)
- `testDefaultFacadeUsesRealNativeSessionsAndReturnsStoredData` (line 63)

### `tests/Unit/Data/Orm/SessionDriverTest.php` (8)

- `testHandlerObjectIsUsedDirectlyAndReturnsItsResults` (line 45)
- `testHandlerClassReceivesItsConstructorParameters` (line 54)
- `testInvalidExplicitHandlersFallBackToConfiguration` (line 64): 5 data-provider cases
- `testNativeOrmKeyOptionsAndConfiguration` (line 71)
- `testInvalidKeysThrowTheDeclaredParameterError` (line 78): 4 data-provider cases
- `testDisabledSessionStatusUsesItsFrameworkFailure` (line 84)
- `testRealNativeSessionPersistsAcrossModernAndLegacyDriverNames` (line 94)
- `testDestroyEndsTheSessionAndRemovesItsData` (line 103)

### `tests/Unit/Data/OrmMapTest.php` (3)

- `testObjectIsPreservedAndTakesPrecedence` (line 8)
- `testDataAndTypeConstructAMappedObject` (line 15)
- `testIncompleteSpecificationsAreRejected` (line 23): 4 data-provider cases

### `tests/Unit/Data/PhoneTest.php` (5)

- `testNationalAndLocalNanpFormatting` (line 8)
- `testEmptyInputAndProtectedValueExtensionPoint` (line 21)
- `testCountryOneAndTextExtensions` (line 30)
- `testInternationalNumbersGetTheirCountryCode` (line 47): 41 data-provider cases
- `testInternationalDialingPrefixAndExistingFormattedMetadata` (line 68)

### `tests/Unit/Data/ScalarTest.php` (6)

- `testScalarValuesAndType` (line 9): 4 data-provider cases
- `testNestedScalarAndUnserialization` (line 17)
- `testArrayAndRuleInputForms` (line 25)
- `testEmptyInputsUseFallbackAndUnsupportedInputsRemainNull` (line 32)
- `testPlainDisplay` (line 40)
- `testHtmlAndCommentDisplay` (line 45)

### `tests/Unit/Data/TypedTest.php` (6)

- `testRelativeAndAbsoluteTypesAndMissingNames` (line 10)
- `testTypedLoadingUsesTheDiscriminatorOrExplicitOverride` (line 21)
- `testUnusableExplicitTypesFallBackToTheDiscriminator` (line 30): 4 data-provider cases
- `testAnExplicitTypeWinsOverTheDiscriminator` (line 37)
- `testMediaDefaultsAndExplicitMetadata` (line 43)
- `testHistoryPreservesProvidedTimestampsAndGeneratesMissingOnes` (line 52)

### `tests/Unit/Db/ConfigProvidersTest.php` (2)

- `testMongoConfigurationAndOptionalDefaults` (line 23)
- `testPdoConfigurationValues` (line 32)

### `tests/Unit/Db/LegacyControllerTest.php` (2)

- `testDriverConstructionConnectsWithItsSuppliedParameters` (line 22)
- `testStaticReadUsesTheCalledClass` (line 28)

### `tests/Unit/Db/Mongo/CaFileTest.php` (4)

- `testFileAdapterCallsItsDeclaredPathHook` (line 22)
- `testLiteralContentUsesRequestedPathAndReusesWrittenFile` (line 33)
- `testTemporaryCertificateIsRemovedAtNativePhpShutdown` (line 47)
- `testFailedFileWriteRaisesAnError` (line 56)

### `tests/Unit/Db/Mongo/CachedClientFailureTest.php` (1)

- `testFailedCachedPingEvictsTheActualClientAndRetriesAfterATransportOutage` (line 35): groups: services, mongo

### `tests/Unit/Db/Mongo/CrudTest.php` (22)

- `testNonDuplicateServerInsertFailureIsRethrownUnchanged` (line 24): groups: services, mongo
- `testLegacyAndModernCollectionsUseTheSameDatabase` (line 58): groups: services, mongo
- `testReadsSupportShapesSortingSkippingAndCountOnly` (line 73): groups: services, mongo
- `testClassAndCloneRecordsAndCallbacks` (line 85): groups: services, mongo
- `testPrefixReadsAndNamespacedCollections` (line 98): groups: services, mongo
- `testModernClientOptionAliasesAndExplicitDatabaseNames` (line 105): groups: services, mongo
- `testClientOptionsChooseTheServer` (line 116): groups: services, mongo
- `testClientOptionsAreKeptWhenSelectingAClient` (line 123): groups: services, mongo
- `testExpiredCachedPingUsesTheRealDatabaseAndKeepsTheClient` (line 131): groups: services, mongo
- `testCreateUpdateAndDeleteRoundTrip` (line 141): groups: services, mongo
- `testCreateSubcollectionsUpsertsAndDuplicateKeys` (line 151): groups: services, mongo
- `testDocumentedCollectionNamespaceAliasForCreate` (line 165): groups: services, mongo
- `testReplacementOperatorAndNamespacedWrites` (line 170): groups: services, mongo
- `testUpdatedRecordCountUsesTheRealAdapterWriteResult` (line 183): groups: services, mongo
- `testUpdateCountModesForReplacementAndOperators` (line 187): groups: services, mongo
- `testInvalidDocumentWritePreservesTheDriverException` (line 195): groups: services, mongo
- `testLoadDefaultsAliasesAndStartWindow` (line 200): groups: services, mongo
- `testLoadOptionsAreValidated` (line 215): 9 data-provider cases; groups: services, mongo
- `testInvalidReadParametersFail` (line 225): 6 data-provider cases; groups: services, mongo
- `testMissingRecordsThrow` (line 233): groups: services, mongo
- `testMissingDeletesAndInvalidWriteParameters` (line 237): groups: services, mongo
- `testWriteParameterValidation` (line 244): 5 data-provider cases; groups: services, mongo

### `tests/Unit/Db/Mongo/DriverTest.php` (21)

- `testUriAndOptionsDerivationDoesNotNeedADatabase` (line 51)
- `testCollectionNamesAndDatabaseFallbacks` (line 66)
- `testInvalidCollectionNamesFail` (line 75): 2 data-provider cases
- `testCollectionNameMustBeAvailable` (line 80)
- `testDatabaseMustBeAvailableWithoutInterfaceOrFallback` (line 84)
- `testDatabaseNameMustHaveFallback` (line 88)
- `testInvalidDatabaseProviderFailsWithAUsableException` (line 93): 2 data-provider cases
- `testInvalidCommandsFailBeforeConnectionAccess` (line 99): 3 data-provider cases
- `testAffectedRecordFailuresPreserveCurrentPolicy` (line 105): 4 data-provider cases
- `testAffectedRecordCountIsAnInteger` (line 116)
- `testInvalidCollectionNameFailsBeforeDatabaseAccess` (line 121)
- `testExplicitLazyClientDefersNetworkFailureUntilTheFirstCommand` (line 125)
- `testEagerConnectionRetriesRealNetworkFailuresAndDoesNotCacheThem` (line 133)
- `testLazyClientsAreReusedWithoutRecordingASuccessfulPing` (line 143)
- `testCollectionInterfaceRejectsManualNameChanges` (line 150)
- `testShortnameInterfaceRejectsManualNameChanges` (line 154)
- `testInvalidDatabaseCollectionFactoryIsRejected` (line 158)
- `testClientLoadsWithoutOptionsAndWithoutWarnings` (line 167): groups: services, mongo
- `testPingOffsetIsSanitizedAndStoredWithClientCache` (line 180): groups: services, mongo
- `testPingTimestampWithoutAnOffsetIsNow` (line 194): groups: services, mongo
- `testRefreshedPingKeepsTheConfiguredOffset` (line 208): groups: services, mongo

### `tests/Unit/Db/Pdo/ConnectionTest.php` (7)

- `testEquivalentParametersReuseTheConnectionAndForceNewIsIsolated` (line 25)
- `testConnectReturnsAnIsolatedConnectionOnlyWhenForced` (line 41)
- `testInterfaceSuppliesConnectionParameters` (line 56)
- `testInvalidDriverDsnIsWrappedAsConnectError` (line 62)
- `testMissingConnectionParametersAreReported` (line 68)
- `testLoadStatementMapsRuleArrays` (line 74)
- `testIncompleteMultipleArgumentRulesAreRejected` (line 82)

### `tests/Unit/Db/Pdo/FileConfigTest.php` (1)

- `testConfiguredSqlRootAndDriverOptionOverloadUseRealSqlite` (line 21)

### `tests/Unit/Db/Pdo/PdoTest.php` (7)

- `testBareRecordFlagsMapRowsToGenericData` (line 8)
- `testPreparedStatementsRemainUnexecutedUnlessRequested` (line 32)
- `testAliasesBindingAndExecuteFlags` (line 40)
- `testInvalidMapParameters` (line 54): 5 data-provider cases
- `testSqlFilesWithExplicitRootOrNoRoot` (line 65)
- `testBadSqlPathsReportPathAndReason` (line 78): 5 data-provider cases
- `testRecordsAndCallbacksUseNativeSqliteResults` (line 102)

### `tests/Unit/Db/Pdo/StatementTest.php` (12)

- `testPreparedSqlBindsCriteriaAndMapsColumnsAndOrdering` (line 43)
- `testLimitsDefaultSkipAndStringCriteria` (line 53)
- `testCriteriaJoinHashAndStructuredRelationsProduceExecutableJoins` (line 62)
- `testInvalidStructuralRules` (line 78): 15 data-provider cases
- `testFetchOptionsAddRowsToTheCollection` (line 93)
- `testInvalidFetchOptionsAreRejected` (line 112): 4 data-provider cases
- `testInvalidAliasedColumnNamesAreRejected` (line 118): 7 data-provider cases
- `testAliasedColumnNamesMayContainDigitsAndUnderscoresAfterTheFirstLetter` (line 124)
- `testFetchCallbackConsumesActualRowsThroughABufferedNativeStatement` (line 133)
- `testExecutedRowsWithoutAFetchOptionAreAddedAsGenericData` (line 139)
- `testDeclaredJoinClauseCannotContainAStatementSeparator` (line 145)
- `testNotFoundMeansNoRowsWereFetched` (line 151)

### `tests/Unit/Db/RabbitMqTest.php` (11)

- `testInitialConnectionAndChannelAreReturnedAndReused` (line 55): groups: services, rabbitmq
- `testChannelCanEstablishItsOwnConnection` (line 69): groups: services, rabbitmq
- `testQueueDeclarationAndPublishUseDocumentedParameters` (line 79): groups: services, rabbitmq
- `testExplicitRoutingMessageAndCommandPaths` (line 94): groups: services, rabbitmq
- `testProviderOverridesDefaultsAndTrimsStringParameters` (line 109): groups: services, rabbitmq
- `testMissingConnectionParametersAreRejected` (line 116): 2 data-provider cases
- `testMissingHostIsRejectedBeforeNetworkConnection` (line 123)
- `testEmptyMessageIsRejected` (line 128)
- `testConnectionCacheKeyDependsOnTheConnectionParameters` (line 133)
- `testMissingQueueIsRejected` (line 147): groups: services, rabbitmq
- `testConfigNamespaceSuppliesPresentKeysAndKeepsDefaults` (line 153)

### `tests/Unit/Input/MapTest.php` (6)

- `testArrayBecomesProperties` (line 9)
- `testObjectBecomesProperties` (line 16)
- `testScalarGoesToData` (line 26)
- `testEmptyInputHasNoProperties` (line 31)
- `testFalsyScalarInputIsTreatedAsEmpty` (line 40): 6 data-provider cases
- `testWhitespaceAndOtherNonEmptyScalarsAreData` (line 45)

### `tests/Unit/Lib/AppErrorTest.php` (3)

- `testSummariesPreserveDataAndExposeCurrentFormatting` (line 9): 10 data-provider cases
- `testLargePayloadSummariesAreTruncatedAndResourcesAreDescribed` (line 25)
- `testMissingParameterNamesAcceptStringsListsAndStringableMembers` (line 34): 7 data-provider cases

### `tests/Unit/Lib/LibTest.php` (6)

- `testDependencyMessagesIncludeOptionalMissingClassDetails` (line 7)
- `testRequiredValuesAndLists` (line 17)
- `testInvalidRequiredValues` (line 25): 7 data-provider cases
- `testPresentClassDependenciesPass` (line 37)
- `testMissingClassDependenciesRetainTheNormalizedClassSet` (line 43): 2 data-provider cases
- `testBadParameterMetadata` (line 54)

### `tests/Unit/Orm/LegacyFieldErrorTest.php` (1)

- `testLegacyFieldFileCanBeIncludedUnderItsActualDeclaredName` (line 8)

### `tests/Unit/Orm/LifecycleTest.php` (6)

- `testUnsupportedOperationsDoNothing` (line 29)
- `testCreateReadAndUpdateTrackTheirLifecycle` (line 36)
- `testDeleteWorksAfterReadAndOnlyOnce` (line 48)
- `testStorageHookFailuresExplainTheOperation` (line 60): 8 data-provider cases
- `testExistenceChecksAreCachedOnlyAfterAConcreteAnswer` (line 75)
- `testKeyAndFieldMappingRetainNamedAndPositionalValues` (line 84)

### `tests/Unit/Orm/ReportTest.php` (3)

- `testMetadataProviderSuppliesTheSourceAndIsRetained` (line 12)
- `testReportAcceptsDatabaseClassNamesWithoutInstantiatingThem` (line 20)
- `testReportRejectsNonDatabaseSources` (line 25)

### `tests/Unit/Tmpl/DataTest.php` (4)

- `testArrayAndObjectMappingIncludeDeclaredAndDynamicValues` (line 8)
- `testArrayAndMagicPropertyAccessShareTheSameValues` (line 18)
- `testScalarConstructorPreservesValues` (line 35): 5 data-provider cases
- `testEmptyConstructorHasNoDynamicValues` (line 43)

### `tests/Unit/Tmpl/DesignOutputTest.php` (1)

- `testResolvableOutputForwardsTheOptionalTemplateData` (line 11)

### `tests/Unit/Vendor/EncodingTest.php` (4)

- `testConversionPreservesValidUtf8AndRepairsLegacyBytes` (line 8): 13 data-provider cases
- `testNestedArraysAndWindowsEncodingAliases` (line 18)
- `testBomRemovalAndEncodingLabels` (line 27)
- `testIconvOptionsUseTheActualExtension` (line 39)

### `tests/Unit/Vendor/JsMinifierTest.php` (2)

- `testWhitespaceCommentsStringsAndRegexArePreservedAsNeeded` (line 8): 20 data-provider cases
- `testMalformedProgramsThrowAndRestoreOutputBuffer` (line 37): 3 data-provider cases

### `tests/Unit/Vendor/MarkdownTest.php` (4)

- `testQuoteHandlerRejectsANonmatchingExtensionMarker` (line 9)
- `testBothBundledLoadersWorkInAFreshPhpRuntime` (line 18)
- `testSupportedBlockAndInlineSyntax` (line 25): 48 data-provider cases
- `testConfigurationAndRepeatedParserInstances` (line 83)

### `tests/Unit/Vendor/YasumiDateQueryTest.php` (5)

- `testMissingAndUnparseableDatesUseTheCurrentTimestamp` (line 22): 3 data-provider cases
- `testValidDateYearAndLocaleAreKept` (line 30)
- `testCachedProvidersAreSharedAndDatesUpdate` (line 39)
- `testHolidayAndWorkingDayQueriesUseTheRealProvider` (line 48)
- `testScheduleComparisonUsesActualNativeDateTimeObjects` (line 55)
