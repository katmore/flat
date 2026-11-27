# Test inventory

Every test in the `unit` suite, grouped by file. **Generated; do not edit by hand.**
Regenerate with `bin/test inventory [KNOWN-DEFECTS.md] > TEST-INVENTORY.md` (or
`docker/bin/test inventory ...` to run it in Docker), which lists the suite with
PHPUnit's `--list-tests-xml` and converts it with
`tests/tools/inventory.php`.

- 890 test methods in 110 files, running 1663 test cases.
- 144 methods use a data provider (one case per data set).
- 0 methods still assert defective behavior (`@group known-defect`); KD IDs are from `docs/modernization/php-7.4/active/KNOWN-DEFECTS.md`.
- Each entry is a test method with its line in the file; the method name is the test's claim.
- Groups `services`, `mongo` and `rabbitmq` mark tests that need those containers.

## Files

### `tests/Sandbox/AuthenticationTest.php` (3)

- `testInvalidCredentialsAreAnAuthorizeNetResponseError` (line 17): groups: sandbox, anet, services
- `testTheSandboxAccountAuthenticatesThroughTheCurrentApi` (line 30): groups: sandbox, anet, services
- `testTheLegacyClientTargetsTheSandboxOnly` (line 38): groups: sandbox, anet, services

### `tests/Sandbox/CustomerProfileTest.php` (3)

- `testInvalidCredentialsComeBackThroughTheLegacyWrapperAsAResponseError` (line 36): groups: sandbox, anet, services
- `testACustomerProfileCanBeCreatedReadAndUpdated` (line 47): groups: sandbox, anet, services
- `testAnUnknownCustomerProfileIsAResponseError` (line 64): groups: sandbox, anet, services

### `tests/Sandbox/PaymentMethodTest.php` (2)

- `testAPaymentMethodCanBeAddedChangedAndRemoved` (line 63): groups: sandbox, anet, services
- `testAnUnknownPaymentMethodIsAResponseError` (line 84): groups: sandbox, anet, services

### `tests/Sandbox/SubscriptionTest.php` (2)

- `testASubscriptionCanBeCreatedChangedAndCancelled` (line 81): groups: sandbox, anet, services
- `testAnUnknownSubscriptionIsAResponseError` (line 100): groups: sandbox, anet, services

### `tests/Sandbox/TransactionTest.php` (3)

- `testAnAuthorizationAndCaptureIsApproved` (line 62): groups: sandbox, anet, services
- `testAnAuthorizationOnlyIsApproved` (line 71): groups: sandbox, anet, services
- `testADeclinedTransactionIsAResponseError` (line 79): groups: sandbox, anet, services

### `tests/Unit/Api/ControllerTest.php` (16)

- `testMethodDispatchHeadTraceAndNoInterface` (line 35)
- `testUnsupportedMethodAnswersServerErrorThroughTheResponseHandler` (line 49)
- `testAnyInterfaceFallsBackToSpecificMethodOrUsesItsOwnResponse` (line 69)
- `testResponseHandlerCanObserveOrReplaceResponses` (line 81)
- `testConstructorMapsInputsAndInvokesSuccessAndAlwaysCallbacks` (line 90): 4 data-provider cases
- `testStringMethodConstructorAndDirectCallback` (line 99)
- `testResponseExceptionsFromHandlerReachConstructorErrorAndAlwaysCallbacks` (line 106)
- `testReturnedErrorResponseSuppliesItsOwnMetadata` (line 116)
- `testNonErrorFailureStatusUsesNullOptionalErrorMetadata` (line 123)
- `testOrdinaryHandledExceptionReturnsWithoutAnAlwaysResponse` (line 130)
- `testUnhandledConstructorExceptionPropagates` (line 136)
- `testInvalidResponseIsRejected` (line 141)
- `testRequestMethodGetterReturnsTheDispatchedMethod` (line 147)
- `testInvalidFallbackMethodIsRejected` (line 155)
- `testUnknownMethodIsRejected` (line 159)
- `testMissingMethodRaisesMissingMethod` (line 164)

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

### `tests/Unit/Cloud/AuthorizeCustomerTest.php` (17)

- `testNativeLegacyClientIsPreparedAndCachedWhenSubclassProvidesTheHook` (line 60)
- `testGetMapsProfileAndMissingProfileReturnsNull` (line 71)
- `testProfileAliasDoesNotReplaceExplicitId` (line 82)
- `testProfileFieldsReachTheSdkCustomer` (line 90)
- `testCreateForwardsValidationMode` (line 101): 7 data-provider cases
- `testWithoutAPaymentTheValidationModeIsAlwaysNone` (line 113): 7 data-provider cases
- `testExplicitPaymentObjectIsConvertedForTheSdk` (line 127)
- `testInvalidRequestsStopBeforeClientCalls` (line 139): 20 data-provider cases
- `testCaptureOnlyIsGoneBecauseAuthorizeNetWithdrewIt` (line 150)
- `testUpdateAndDeleteForwardIds` (line 175): 2 data-provider cases
- `testClientRejectionBecomesResponseError` (line 187): 4 data-provider cases
- `testRetiredConvenienceOperationsFailClearlyWithoutCallingTheGateway` (line 201): 5 data-provider cases
- `testPaymentMethodCreationAndUpdateForwardValidatedIds` (line 223)
- `testMissingPaymentIdDoesNotRaiseAnIndexZeroNotice` (line 237)
- `testPaymentMethodRejectionUsesExistingResponseError` (line 248): 2 data-provider cases
- `testTransactionSpecificationsNormalizeAndReachTheClient` (line 261): 10 data-provider cases
- `testTransactionClientRejectionIsPropagatedBeforeDecoding` (line 289)

### `tests/Unit/Cloud/AuthorizeDataTest.php` (13)

- `testIntervalNormalizesScalarLengths` (line 16): 6 data-provider cases
- `testScheduleDefaultsAndScalarOccurrenceNormalization` (line 23): 6 data-provider cases
- `testNewScheduleUsesCurrentTimezoneAndInvalidTimezoneClearsDate` (line 32)
- `testStartDateIsNormalizedToIsoFormat` (line 42)
- `testValidTimezoneConversionUsesNativeDateTimeZone` (line 50)
- `testSubscriptionCacheRetainsMappedMetadata` (line 55)
- `testSubscriptionErrorsRetainTheirIdentifiers` (line 60)
- `testSubscriptionPaymentAdaptersCopyFieldsIntoRealSdkDtos` (line 70)
- `testResponseSubclassCanSupplyStatusInteger` (line 94): 6 data-provider cases
- `testFailureDetailsComeFromSdkUnlessAlreadyProvided` (line 104)
- `testUnexpectedResponseRetainsOptionalDetails` (line 116)
- `testFactoryMapsActualSdkCodesToResponseKindsAndStatus` (line 124): 4 data-provider cases
- `testUnknownSdkResponseCodeStillReturnsNull` (line 136)

### `tests/Unit/Cloud/AuthorizeDeprecationTest.php` (1)

- `testEveryAuthorizeNetWrapperDeclarationIsMarkedDeprecated` (line 6)

### `tests/Unit/Cloud/AuthorizeNoResponseTest.php` (5)

- `testAnAuthorizeNetResponseIsReturnedUnchanged` (line 38)
- `testAnAuthorizeNetErrorIsStillAnAnswerNotNoResponse` (line 44)
- `testNoUsableReplyRaisesNoResponseThatSaysWhatHappened` (line 53): 5 data-provider cases
- `testBothFlatClientsRaiseNoResponse` (line 70)
- `testAWrapperCallRaisesInsteadOfLookingSuccessful` (line 81)

### `tests/Unit/Cloud/AuthorizePaymentMethodTest.php` (8)

- `testACardPaymentMethodBecomesTheXmlAuthorizeNetDefines` (line 94): 4 data-provider cases
- `testACustomerTypeIsSent` (line 109)
- `testBankAccountFieldsWithoutAWrapperAreRecognised` (line 116)
- `testABankAccountPaymentMethodBecomesBankAccountXml` (line 123)
- `testUpdatingAPaymentMethodNamesTheMethodInsideThePaymentProfile` (line 133)
- `testAPaymentGivenToCreateAProfileIsPartOfTheProfile` (line 142)
- `testAPaymentIsRequired` (line 151)
- `testAnInvalidPaymentStopsBeforeAnyRequest` (line 161): 8 data-provider cases

### `tests/Unit/Cloud/AuthorizeSubscriptionSdkTest.php` (3)

- `testGetInfoReadsTheListResponseTheSchemaDescribes` (line 65)
- `testASubscriptionMissingFromTheListIsNotFound` (line 75)
- `testEachCallSendsOnlyItsOwnData` (line 85)

### `tests/Unit/Cloud/AuthorizeSubscriptionTest.php` (19)

- `testNativeArbConstructionUsesTheApplicationPreparationHookAndCachesItsClient` (line 71)
- `testExistingSubscriptionOperationsRequireTheirIdentifier` (line 79): 5 data-provider cases
- `testUpdateAmountAndCancelPassValidatedIdentifiersToTheirClient` (line 85)
- `testRetiredGetTransactionFailsClearlyWithoutCallingTheGateway` (line 94)
- `testUpdateAmountRetainsItsExistingIntegerRestriction` (line 103): 2 data-provider cases
- `testStatusAndUpdatabilityUseTheExistingStatusMethod` (line 109): 3 data-provider cases
- `testGatewayRejectionsUseTheDeclaredResponseError` (line 117): 5 data-provider cases
- `testPaymentFormsProduceRealSdkSubscriptionObjects` (line 128): 6 data-provider cases
- `testInvalidPaymentShapesFailBeforeClientTransmission` (line 145): 4 data-provider cases
- `testCreateMapsItsFieldsAndReturnsTheSubscriptionId` (line 158)
- `testCreateRejectsMissingOrMalformedDocumentedFields` (line 170): 5 data-provider cases
- `testCreateAcceptsAnExistingScheduleObject` (line 181)
- `testInfoSurfacesAListRejectionAfterSuccessfulStatusLookup` (line 187)
- `testInfoStopsAfterFullPageExceedsDeclaredTotal` (line 193)
- `testInfoCircuitBreakerBoundsRepeatedFullPages` (line 199)
- `testInfoRequestsEachPageInTurn` (line 209)
- `testInfoFindsASubscriptionInActualXmlDetails` (line 219)
- `testInfoMissingTotalUsesUnexpectedResponseWithoutAnIndexZeroWarning` (line 226)
- `testInactiveInfoWithoutAMatchUsesNotFound` (line 231)

### `tests/Unit/Cloud/AuthorizeTest.php` (7)

- `testAuthenticationUsesSubclassCredentials` (line 45)
- `testSuccessfulResponseIsReturnedAndEndpointPassedToController` (line 53)
- `testRejectedResponseRetainsSdkMessages` (line 60)
- `testNullResponseBecomesResponseError` (line 74)
- `testControllerExceptionIsSummarizedAndKeptAsPrevious` (line 80)
- `testResponseWithoutMessagesAndScalarErrors` (line 91)
- `testParameterErrorsExposeDetails` (line 108)

### `tests/Unit/Cloud/AuthorizeTransactionRequestTest.php` (3)

- `testEachKindBecomesTheXmlAuthorizeNetDefines` (line 79): 2 data-provider cases
- `testADeclinedAnswerIsAFailureWithItsReason` (line 98)
- `testAnAmountIsRequiredByTheKindOfTransactionItself` (line 108)

### `tests/Unit/Cloud/AuthorizeTransactionTest.php` (6)

- `testTransactionKindsAreLoadableTypedData` (line 17): 2 data-provider cases
- `testLegacyClassNameHookStillUsesItsOriginalCustomerPrefix` (line 27)
- `testSdkAdaptersAcceptOnlyTheirMatchingType` (line 32): 2 data-provider cases
- `testAdapterMapsEveryDeclaredSdkField` (line 45)
- `testOnlyTrueValidationPasses` (line 53)
- `testLegacyPaymentTypeNamesAutoloadWithoutRenamingTheirImplementations` (line 65)

### `tests/Unit/Cloud/AwsConfigTest.php` (1)

- `testS3ClientConfigurationUsesRealSdkWithoutMakingRequests` (line 26)

### `tests/Unit/Cloud/AwsTest.php` (8)

- `testExplicitClientIsCheckedAndCached` (line 41)
- `testProviderClientIsResolvedOnce` (line 47)
- `testMissingClientReportsUnavailable` (line 53)
- `testBodyUploadCanonicalizesKeysAndWaitsForTheUploadedObject` (line 58): 5 data-provider cases
- `testSmallFileUploadUsesFileAndOptionalContentType` (line 65)
- `testFileUploadRejectsInvalidPathsBeforeClientCalls` (line 76): 3 data-provider cases
- `testS3ParameterMappingUrlsAndBodySetterUsingAnExplicitClientBoundary` (line 88)
- `testConstructorUploadAndFileSetterUseTheSameBoundary` (line 101)

### `tests/Unit/Cloud/IpInfoDbTest.php` (7)

- `testCacheHitUsesStoredRecordWithoutHttp` (line 53): groups: services, http
- `testCachedRecordExpiresExactlyAtItsTimeToLive` (line 65): groups: services, http
- `testMissingOrExpiredCacheFetchesMapsAndStoresResponse` (line 85): 3 data-provider cases; groups: services, http
- `testHttpFailureCarriesStatusContext` (line 107): groups: services, http
- `testTransportFailureCarriesCurlContext` (line 119): groups: services, http
- `testTheApiBaseUrlDefaultsToTheRealApi` (line 132): groups: services, http
- `testRetiredStaticLoadFailsClearlyWithoutTouchingTheCache` (line 139): groups: services, http

### `tests/Unit/Cloud/S3MultipartTest.php` (4)

- `testThresholdRoutesToRealSdkMultipartWorkflow` (line 56)
- `testMultipartUploadKeepsTheContentTypeAndThresholdStaysBelowTheSinglePutLimit` (line 66)
- `testPartFailureAbortsTheStartedUploadAndWrapsSdkFailure` (line 75)
- `testInitiationFailureDoesNotAttemptAbortWithoutAnUploadId` (line 88)

### `tests/Unit/Cloud/TwitterUserInfoTest.php` (2)

- `testKnownFieldsAreMappedAndExcludedFromExtraFields` (line 6)
- `testFieldsAddedAfterConstructionReturnNamesOrValues` (line 14)

### `tests/Unit/CodingRulesTest.php` (3)

- `testClassAliasIsNeverUsed` (line 51)
- `testEveryClassIsDeclaredWhereItsPathSays` (line 62)
- `testClassesAreLoadedByTheAutoloaderNeverByInclude` (line 74)

### `tests/Unit/Core/AlertDefaultsTest.php` (2)

- `testDefaultBrokerLoadsItsConfigurationAndUsesTheRealLocalRabbitService` (line 41): groups: services, rabbitmq
- `testSubscriptionStorageRequiresAnExplicitProvider` (line 46)

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
- `testReferenceFilesAreReadOnlyOncePerNamespace` (line 174)
- `testEnumeratesValuesWithReferences` (line 183)
- `testReferenceValuesAndNamespaceCaching` (line 197)
- `testRejectsInvalidReferenceDocuments` (line 209): 4 data-provider cases
- `testMissingReferenceDocumentAndCachedMisses` (line 227)
- `testWholeReferencesAndNestedValues` (line 240)
- `testMissingWholeReferenceReportsTheOriginalKey` (line 258)
- `testInlineReferencesIncludingDuplicatesBooleansAndMalformedTokens` (line 267)
- `testNonScalarInlineReferenceThrows` (line 283)

### `tests/Unit/Core/Curl/ConfigTest.php` (5)

- `testExplicitConfigurationAndDefaults` (line 9)
- `testChromeSelectionProvidesTheChromeUserAgent` (line 23)
- `testInvalidSettingsAreRejected` (line 30): 7 data-provider cases
- `testCachingUsesWritableTemporaryDirectoryByDefault` (line 40)
- `testUnwritableCacheDirectoryIsRejected` (line 46)

### `tests/Unit/Core/Curl/RequestTest.php` (17)

- `testUrlParsingPreservesComponentsAndNoBodyDefaults` (line 9)
- `testInvalidUrls` (line 19): 5 data-provider cases
- `testMissingSchemeRaisesBadParamWithoutPhpNotices` (line 30): 4 data-provider cases
- `testFormKeysAndValuesSurviveEncoding` (line 37)
- `testUrlencodedArrayObjectAndStringBodies` (line 48)
- `testNonStringScalarInUrlencodedModeHasNoBody` (line 57)
- `testNestedFormValuesRaiseBadParamWithoutPhpWarnings` (line 62): 6 data-provider cases
- `testResourceFormValuesRaiseBadParamWithoutPhpWarnings` (line 79)
- `testFlatFormValuesKeepTheirEncoding` (line 88)
- `testJsonBodyAndContentType` (line 98)
- `testJsonBodiesStillAcceptNestedData` (line 104)
- `testJsonEncodingFailureIsRejected` (line 110)
- `testJsonPreservesFalsyBodies` (line 116): 5 data-provider cases
- `testRawModePreservesTheSuppliedBody` (line 127): 6 data-provider cases
- `testRawModeRequiresANonemptyStringContentType` (line 138): 5 data-provider cases
- `testNullRemainsTheNoBodyDefaultForJsonAndRawModes` (line 147)
- `testEmptyUrlencodedQueryStringIsRejected` (line 155)

### `tests/Unit/Core/Curl/ResponseTest.php` (30)

- `testDefaultRequestsSendTheFlatUserAgent` (line 38): groups: services, http
- `testFacadeAcceptsUrlsAndConfigurationObjects` (line 48): groups: services, http
- `testResponseDataAndTheUserAgentOfTheConfiguration` (line 59): groups: services, http
- `testTheServersStatusDecidesWhetherTheResponseIsOk` (line 71): 6 data-provider cases; groups: services, http
- `testStatusZeroIsNotOk` (line 78): groups: services, http
- `testScalarUrlConfiguration` (line 85): groups: services, http
- `testRequestDataIsSentAsTheBody` (line 90): groups: services, http
- `testJsonRequestDataIsSentWithItsContentTypeAndMethod` (line 102): groups: services, http
- `testFiletimeIsTheServersLastModifiedTimeOrMinusOne` (line 113): groups: services, http
- `testRedirectsAreFollowed` (line 120): groups: services, http
- `testFilesAndTemporaryCleanup` (line 128): groups: services, http
- `testCallerProvidedFilePersists` (line 139): groups: services, http
- `testSaveFlagsAndArraySavePaths` (line 150): groups: services, http
- `testCookiesTheServerSetsAreSavedAndThoseInTheFileAreSent` (line 168): groups: services, http
- `testAutomaticCookieFileIsRemovedWithTheResponse` (line 181): groups: services, http
- `testInvalidCookieFile` (line 192): 2 data-provider cases; groups: services, http
- `testDomAndXPathResults` (line 198): groups: services, http
- `testInvalidXPathReportsQuery` (line 208): groups: services, http
- `testDomAndXPathPreserveCallerLibxmlStateAndBufferedErrors` (line 218): 2 data-provider cases; groups: services, http
- `testXPathArgumentWarningRestoresCallerLibxmlState` (line 241): 2 data-provider cases; groups: services, http
- `testHtmlParserWarningReachesCallerAndLeavesReportingEnabled` (line 258): groups: services, http
- `testAServerThatIsNotListeningIsAConnectError` (line 274): groups: services, http
- `testTransferFailureRetainsErrorDetails` (line 289): 3 data-provider cases; groups: services, http
- `testErrorAfterFileTransferStillFails` (line 299): groups: services, http
- `testRobotsRules` (line 309): 6 data-provider cases; groups: services, http
- `testRobotsCheckCanDenyOrAllowConstructor` (line 323): groups: services, http
- `testRobotsRulesForAnotherAgentIgnoreTheUserAgentLabel` (line 332): groups: services, http
- `testNoCookieFileIsCreatedUnlessCookiesAreAccepted` (line 339): groups: services, http
- `testFalsyTextBodyInitializesDomOnDemand` (line 347): groups: services, http
- `testUnavailableSavePathRaisesSystemErrorAfterWarning` (line 354): groups: services, http

### `tests/Unit/Core/Curl/ResponseTransferInfoTest.php` (1)

- `testMissingTransferInfoRaisesItsDeclaredException` (line 21): groups: services, http

### `tests/Unit/Core/DebugTest.php` (13)

- `testDisplayCollectionHelperAcceptsOnlyDisplayData` (line 16)
- `testDefaultKillHandlerExitsWithConfiguredStatus` (line 53)
- `testDisplayIsNotSuppressedByDefault` (line 61)
- `testKillHonorsAPlainExitStatus` (line 66)
- `testKillSanitizesInvalidNestedExitStatusAndEmptyMessage` (line 74)
- `testReturningCustomKillHandlerReachesTheFinalExit` (line 80)
- `testWebDisplayFormattingInAnIsolatedPlatformBranch` (line 87)
- `testSuppressionAndInactiveConfigurationPreventDisplay` (line 99)
- `testDisplayDumpAndMessageEventsCarryCallerAndCreationTime` (line 113)
- `testDisplayAndDumpTriggerOnlySupportedUserErrors` (line 131)
- `testDefaultCliHandlerDisplaysStructuredData` (line 147)
- `testDataDescriptionsUseTheMatchingFormat` (line 155)
- `testCustomKillHandlerReceivesCallerAndConvertedMessage` (line 170)

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

- `testErrorsRetainOperationContext` (line 11): 45 data-provider cases
- `testDuplicateKeyRetainsNonScalarValueDescription` (line 66)
- `testConfigReferenceErrorRetainsAllLookupKeys` (line 71)
- `testScalarDefaultErrorAndThreadFailureRetainDetails` (line 78)
- `testIgnoredCollectionMemberRemainsAnEmptyString` (line 89)
- `testUploadAndBrokerErrorsRetainTheOriginalExceptionContext` (line 96)
- `testValidationFailureDescribesTheSuppliedValueAndRule` (line 106): 4 data-provider cases
- `testRouteMissingErrorExposesBothCoordinates` (line 115)
- `testCrudErrorAcceptsUnspecifiedOperation` (line 124)
- `testDuplicateKeyErrorRetainsIndexAndNonScalarValue` (line 129)
- `testDuplicateKeyDescribesANonemptyScalarValue` (line 136)
- `testDuplicateKeyDescribesIndexesWithoutWarnings` (line 142)
- `testBadApiResponseRetainsDocumentedClassAndMethod` (line 156)
- `testSessionErrorsRetainReasonAndExpirationMetadata` (line 162)
- `testCurlSystemErrorRetainsDataAndDetails` (line 170)
- `testNotFoundStatusReportsCode404` (line 176)
- `testXpathErrorRetainsActualLibxmlDetailsWithoutDuplicatingMessage` (line 182)
- `testXpathErrorMessageNamesTheQueryOnceWhateverTheDetails` (line 198)
- `testScalarTypeErrorUsesItsSuppliedTypeWithoutAnUndefinedLocal` (line 203)
- `testSshParameterErrorMessage` (line 210)

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

### `tests/Unit/Core/Md/ConvertTest.php` (9)

- `testMarkdownStringInstanceAndFileProduceEquivalentHtml` (line 7)
- `testMarkdownExtraTablesAndFencedCode` (line 22)
- `testRawHtmlAndUnsafeAddressesAreNeutralisedByDefault` (line 30): 7 data-provider cases
- `testOrdinaryMarkdownAndMarkdownExtraAreUnchangedBySafeMode` (line 50)
- `testCharacterEntitiesAreShownLiterallyInSafeMode` (line 60)
- `testTrustedTextCanLetRawHtmlThrough` (line 66)
- `testAnUnknownOptionIsRejected` (line 81)
- `testNonStringSourceIsRejected` (line 87)
- `testMissingFileRaisesInvalidSource` (line 92)

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

- `testNamedPrioritiesRunInDocumentedOrderAndHandlesAreUnique` (line 22)
- `testZeroIsAWeightAndEmptyValuesJoinTheDefaultQueue` (line 33)
- `testOutOfRangeWeightsAreRejected` (line 44): 3 data-provider cases
- `testDistinctWeightsRunBeforeDefaultAndAfterFirst` (line 51)
- `testEachCallbackRunsOnlyOnceAcrossRepeatedInvocation` (line 60)
- `testExplicitInvocationIsNotRepeatedAtPhpShutdown` (line 74)
- `testCallbackRegisteredDuringInvocationRunsAtPhpShutdown` (line 83)
- `testEqualWeightsRunNewestFirstWithoutLosingCallbacks` (line 93)
- `testRegisteredCallbackRunsAtActualPhpShutdown` (line 100)
- `testWeightCeilingStopsUnboundedInsertion` (line 105)
- `testWeightCeilingAllowsExactlyTheRemainingSlots` (line 110)

### `tests/Unit/Core/SshTest.php` (34)

- `testUnavailableExtensionIsReported` (line 32)
- `testRequiredConfigurationIsChecked` (line 38): 3 data-provider cases
- `testAuthenticationConfigurationMustBePresent` (line 44)
- `testPublicKeyConfigurationNeedsPrivateKey` (line 48)
- `testDeferredSubclassReportsUninitializedAuthentication` (line 52)
- `testPasswordConnectionAndExecutionUseTheirMatchingTransport` (line 57)
- `testRepeatedConnectIsRejected` (line 65)
- `testConnectionFailureIsReported` (line 69)
- `testPasswordFailureUsesTheExistingFrameworkException` (line 74)
- `testPublicKeyPathRunsRealOpenSslAndRemovesItsTemporaryFiles` (line 80): 2 data-provider cases
- `testProvidedFingerprintIsChecked` (line 92)
- `testFingerprintMismatchIsReported` (line 97)
- `testPublicKeyAuthenticationFailureIsReported` (line 105)
- `testLocalCryptoFailureBoundariesAreReported` (line 111): 4 data-provider cases
- `testOperationsRequireAConnection` (line 126): 6 data-provider cases
- `testFailedRemoteExecutionIsReported` (line 132)
- `testDisconnectIgnoresMissingConnectionOnlyWhenRequested` (line 137)
- `testProcessChecksPreserveZeroExitCodeSemanticsAndLegacyHooks` (line 144)
- `testMissingRemoteProcessThrowsUnlessIgnored` (line 150)
- `testInvalidPidUsesTheLegacyCompatibleParameterException` (line 155)
- `testBackgroundExecutionReturnsPidAndChecksRequestedRuntime` (line 160): 3 data-provider cases
- `testBackgroundExecutionNeedsAPid` (line 167)
- `testKillArgumentsAreValidatedBeforeSendingSignals` (line 173): 11 data-provider cases
- `testProcessCanExitAfterEitherSignal` (line 182)
- `testKillWaitsForTheFirstSignalBeforeSendingTheSecond` (line 189)
- `testAProcessThatNeverExitsReportsTheTimeWaited` (line 201)
- `testSignalAndStillRunningFailuresAreReported` (line 211): 3 data-provider cases
- `testSftpOpenFailuresAreReportedBeforeTheMissingHelper` (line 217)
- `testSftpLocalOpenFailureIsReported` (line 222)
- `testLegacyParameterAliasAutoloadsInAFreshRuntime` (line 227)
- `testSuccessfulReadsRestartTheIdleTimeoutAcrossALongerTransfer` (line 232)
- `testAnIdleTimeoutStopsTheNextReadAndClosesBothStreams` (line 243)
- `testNativeReadsHandleShortEmptyAndZeroBodies` (line 256): 5 data-provider cases
- `testCallbackFailureStillClosesTheLocalAndRemoteStreams` (line 270)

### `tests/Unit/Core/Template/ControllerTest.php` (17)

- `testFailingTemplatesCloseTheirOutputBuffers` (line 90)
- `testDesignRootUsesConfigurationOrItsDocumentedFallback` (line 102)
- `testMarkdownReadFailureIsCaughtByTemplateResolver` (line 127)
- `testAbsolutePhpFileAndDisplayClass` (line 132)
- `testFileReadFailuresReportLibraryExceptions` (line 140): 4 data-provider cases
- `testPhpAndPlainHtmlFilesRenderAndCheckWithoutOutput` (line 154)
- `testUnusablePhpDesignsFallBackToHtml` (line 170)
- `testCheckingADesignNeverPrintsIt` (line 182)
- `testTemplatesWrittenByTheDeveloperKeepTheirRawHtml` (line 190)
- `testMarkdownAndGeneratedMarkdownFiles` (line 198)
- `testObjectAndClassDesignsReceiveDataAndCheckOnlyFlag` (line 214)
- `testInvalidDesignsAreRejected` (line 228): 4 data-provider cases
- `testDesignBasesPrefixesAndSeparators` (line 234)
- `testFormattingFlagsReplacementsAndOutput` (line 242)
- `testLoggerWritesDesignKindAndNormalizedName` (line 251)
- `testConstructorAndPreparedInterfacesControlDisplayTiming` (line 262)
- `testDefaultDisplayCallbackDelegatesToTemplateRendering` (line 281)

### `tests/Unit/Core/Template/ValidateControllerTest.php` (3)

- `testRetiredControllerFailsClearlyOnceItsArgumentsAreValid` (line 6)
- `testRetiredInputMethodFailsClearly` (line 12)
- `testConstructorRejectsInvalidInputAndCollection` (line 19): 2 data-provider cases

### `tests/Unit/Core/Template/ViewTest.php` (1)

- `testResourceSearchTriggersEventsForMatchingAncestorFiles` (line 18)

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

- `testOnlyStrictTruePassesAndStoredStringsAreAvailable` (line 26)
- `testValidationOptions` (line 38)
- `testExplicitRegexConstructorKeepsItsExistingArrayPayload` (line 51)
- `testFactoryInterfaceFailureHasItsMessage` (line 56)
- `testFactoryExceptionAutoloadsInAFreshProcess` (line 62)
- `testRegexMatchingAndMultiplePatterns` (line 68)
- `testRegexConfigurationFailures` (line 77): 4 data-provider cases
- `testInvalidRegexSyntaxProducesItsDescriptiveException` (line 87)
- `testPcreExecutionErrorRaisesPregError` (line 101)
- `testPcreExceptionExplainsNativeEngineErrors` (line 111): 5 data-provider cases
- `testInvalidPatternExceptionIncludesPatternAndNativeCodeName` (line 140)
- `testHostnameDefinerSuppliesAValidPatternWithoutChangingItsRawConstant` (line 148): 8 data-provider cases
- `testSupportedFilterIdsConstructSuccessfully` (line 159)
- `testInvalidFilterUsesItsConcreteException` (line 165)

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
- `testOnlyWholeBytesOfHexDigitsAreTypedAsHexBinary` (line 119): 7 data-provider cases
- `testArrayTypeDetectionInspectsTheFirstTenKeys` (line 127)
- `testDumpEnabledEmptyAndNumericStringObjectKeys` (line 136)
- `testResourceFallbackWithAndWithoutDumpRaisesNoWarnings` (line 143)
- `testLegacyIndexAndScalarTypeDecoding` (line 157)
- `testInstanceAndCompatibilityAlias` (line 163)
- `testRoundTripsValuesWithExplicitMetadata` (line 171): 18 data-provider cases
- `testAssocDecodingConvertsObjectsWithoutChangingScalarTypes` (line 187)
- `testLegacyXmlWithoutNewMetadataCanStillBeRead` (line 193)

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

- `testStaticFacadeCachesALegalSubclassAndReturnsItsResults` (line 34)
- `testConstructorArrayConfiguresTheDriver` (line 41)
- `testADriverNeedsBothGetAndSet` (line 55)
- `testDefaultFacadeUsesRealNativeSessionsAndReturnsStoredData` (line 62)

### `tests/Unit/Data/Orm/SessionDriverTest.php` (8)

- `testHandlerObjectIsUsedDirectlyAndReturnsItsResults` (line 43)
- `testHandlerClassReceivesItsConstructorParameters` (line 52)
- `testInvalidExplicitHandlersFallBackToConfiguration` (line 62): 5 data-provider cases
- `testNativeOrmKeyOptionsAndConfiguration` (line 69)
- `testInvalidKeysThrowTheDeclaredParameterError` (line 76): 4 data-provider cases
- `testDisabledSessionStatusUsesItsFrameworkFailure` (line 82)
- `testRealNativeSessionPersistsAcrossSeparateProcesses` (line 92)
- `testDestroyEndsTheSessionAndRemovesItsData` (line 101)

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

- `testMongoConfigurationAndOptionalDefaults` (line 22)
- `testPdoConfigurationValues` (line 31)

### `tests/Unit/Db/LegacyControllerTest.php` (2)

- `testDriverConstructionConnectsWithItsSuppliedParameters` (line 22)
- `testStaticReadUsesTheCalledClass` (line 28)

### `tests/Unit/Db/Mongo/CaFileTest.php` (5)

- `testFileAdapterCallsItsDeclaredPathHook` (line 16)
- `testLiteralContentUsesRequestedPathAndReusesWrittenFile` (line 27)
- `testTemporaryCertificateIsRemovedAtNativePhpShutdown` (line 41)
- `testFailedFileWriteRaisesAnError` (line 49)
- `testEverySslClassLoadsByNameWithoutIncludingFiles` (line 65)

### `tests/Unit/Db/Mongo/CachedClientFailureTest.php` (1)

- `testFailedCachedPingEvictsTheActualClientAndRetriesAfterATransportOutage` (line 35): groups: services, mongo

### `tests/Unit/Db/Mongo/CrudTest.php` (24)

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
- `testAnUpdateThatChangesNothingStillCountsTheMatchedRecord` (line 195): groups: services, mongo
- `testAnUpdateThatMatchesNothingIsNotModified` (line 199): groups: services, mongo
- `testInvalidDocumentWritePreservesTheDriverException` (line 204): groups: services, mongo
- `testLoadDefaultsAliasesAndStartWindow` (line 210): groups: services, mongo
- `testLoadOptionsAreValidated` (line 225): 9 data-provider cases; groups: services, mongo
- `testInvalidReadParametersFail` (line 235): 6 data-provider cases; groups: services, mongo
- `testMissingRecordsThrow` (line 243): groups: services, mongo
- `testMissingDeletesAndInvalidWriteParameters` (line 247): groups: services, mongo
- `testWriteParameterValidation` (line 254): 5 data-provider cases; groups: services, mongo

### `tests/Unit/Db/Mongo/DriverTest.php` (23)

- `testUriAndOptionsDerivationDoesNotNeedADatabase` (line 57)
- `testCollectionNamesAndDatabaseFallbacks` (line 72)
- `testInvalidCollectionNamesFail` (line 81): 2 data-provider cases
- `testCollectionNameMustBeAvailable` (line 86)
- `testDatabaseMustBeAvailableWithoutInterfaceOrFallback` (line 90)
- `testDatabaseNameMustHaveFallback` (line 94)
- `testInvalidDatabaseProviderFailsWithAUsableException` (line 99): 2 data-provider cases
- `testInvalidCommandsFailBeforeConnectionAccess` (line 105): 3 data-provider cases
- `testAffectedRecordFailuresPreserveCurrentPolicy` (line 111): 4 data-provider cases
- `testAffectedRecordCountIsAnInteger` (line 122)
- `testAffectedRecordsComeFromThePreviousUpdateResultNotFromTheServer` (line 127)
- `testAnUpdateResultWithoutMatchesKeepsTheNotModifiedPolicy` (line 134): 2 data-provider cases
- `testInvalidCollectionNameFailsBeforeDatabaseAccess` (line 142)
- `testExplicitLazyClientDefersNetworkFailureUntilTheFirstCommand` (line 146)
- `testEagerConnectionRetriesRealNetworkFailuresAndDoesNotCacheThem` (line 154)
- `testLazyClientsAreReusedWithoutRecordingASuccessfulPing` (line 164)
- `testCollectionInterfaceRejectsManualNameChanges` (line 171)
- `testShortnameInterfaceRejectsManualNameChanges` (line 175)
- `testInvalidDatabaseCollectionFactoryIsRejected` (line 179)
- `testClientLoadsWithoutOptionsAndWithoutWarnings` (line 188): groups: services, mongo
- `testPingOffsetIsSanitizedAndStoredWithClientCache` (line 201): groups: services, mongo
- `testPingTimestampWithoutAnOffsetIsNow` (line 215): groups: services, mongo
- `testRefreshedPingKeepsTheConfiguredOffset` (line 229): groups: services, mongo

### `tests/Unit/Db/Mongo/ReadPreferenceTest.php` (9)

- `testFlatsCopyOfTheTraitIsTheOneThatLoads` (line 38)
- `testTheDefaultIsPrimaryWithoutTags` (line 44)
- `testEveryLegacyModeIsSetAndReadBack` (line 65): 5 data-provider cases
- `testTagSetsAreKept` (line 76)
- `testTheArrayFormIsAcceptedForInternalCopies` (line 84)
- `testAnUnknownModeWarnsAndChangesNothing` (line 94)
- `testPrimaryRefusesTags` (line 102)
- `testSlaveOkayTogglesSecondaryPreferredAndReportsTheEarlierState` (line 109)
- `testSlaveOkayKeepsTheTagSets` (line 120)

### `tests/Unit/Db/Pdo/ConnectionTest.php` (7)

- `testEquivalentParametersReuseTheConnectionAndForceNewIsIsolated` (line 25)
- `testConnectReturnsAnIsolatedConnectionOnlyWhenForced` (line 41)
- `testInterfaceSuppliesConnectionParameters` (line 56)
- `testInvalidDriverDsnIsWrappedAsConnectError` (line 62)
- `testMissingConnectionParametersAreReported` (line 68)
- `testLoadStatementMapsRuleArrays` (line 74)
- `testIncompleteMultipleArgumentRulesAreRejected` (line 82)

### `tests/Unit/Db/Pdo/FileConfigTest.php` (1)

- `testConfiguredSqlRootAndDriverOptionOverloadUseRealSqlite` (line 20)

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

### `tests/Unit/Db/RabbitMqTest.php` (15)

- `testInitialConnectionAndChannelAreReturnedAndReused` (line 56): groups: services, rabbitmq
- `testChannelCanEstablishItsOwnConnection` (line 72): groups: services, rabbitmq
- `testQueueDeclarationAndPublishUseDocumentedParameters` (line 82): groups: services, rabbitmq
- `testPhpAmqpLib2CallersStillGetTheLegacyConnectionClass` (line 100): groups: services, rabbitmq
- `testTheDefaultQueueIsDurableOnRabbitMq4` (line 123): groups: services, rabbitmq
- `testAnExplicitExclusiveQueueKeepsItsTransientDefault` (line 135): groups: services, rabbitmq
- `testAnExplicitDurableChoiceIsNeverOverridden` (line 147): groups: services, rabbitmq
- `testExplicitRoutingMessageAndCommandPaths` (line 158): groups: services, rabbitmq
- `testProviderOverridesDefaultsAndTrimsStringParameters` (line 173): groups: services, rabbitmq
- `testMissingConnectionParametersAreRejected` (line 180): 2 data-provider cases
- `testMissingHostIsRejectedBeforeNetworkConnection` (line 187)
- `testEmptyMessageIsRejected` (line 192)
- `testConnectionCacheKeyDependsOnTheConnectionParameters` (line 197)
- `testMissingQueueIsRejected` (line 211): groups: services, rabbitmq
- `testConfigNamespaceSuppliesPresentKeysAndKeepsDefaults` (line 217)

### `tests/Unit/FsmTest.php` (1)

- `testTheFiniteStateMachineDefinitionLoadsByItsPathName` (line 8)

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

- `testFieldErrorLoadsByItsOwnName` (line 7)

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

### `tests/Unit/PhpVersionTest.php` (1)

- `testSuiteRunsOnPhp74` (line 10)

### `tests/Unit/Tmpl/DataTest.php` (4)

- `testArrayAndObjectMappingIncludeDeclaredAndDynamicValues` (line 8)
- `testArrayAndMagicPropertyAccessShareTheSameValues` (line 18)
- `testScalarConstructorPreservesValues` (line 35): 5 data-provider cases
- `testEmptyConstructorHasNoDynamicValues` (line 43)

### `tests/Unit/Tmpl/DesignOutputTest.php` (1)

- `testResolvableOutputForwardsTheOptionalTemplateData` (line 11)

### `tests/Unit/Tools/ApiDumpTest.php` (5)

- `testReferenceReturnChangesAreDetectedInBothDirections` (line 42)
- `testOlderBaselinesRequireFreshReferenceMetadata` (line 48)
- `testParameterTypeControlStillFindsAChange` (line 56)
- `testTheAuthorizeNetSdkDeprecationNoticeDoesNotMakeAClassUnloadable` (line 68)
- `testAnyOtherNoticeStillMakesAClassUnloadable` (line 76)

### `tests/Unit/Tools/ServicesToolTest.php` (2)

- `testItNamesTheServicesTheSelectedTestsNeed` (line 85): 18 data-provider cases
- `testAMissingListIsAUsageError` (line 90)

### `tests/Unit/Vendor/EncodingTest.php` (7)

- `testConversionPreservesValidUtf8AndRepairsLegacyBytes` (line 8): 13 data-provider cases
- `testNestedArraysAndWindowsEncodingAliases` (line 18)
- `testBomRemovalAndEncodingLabels` (line 27)
- `testIconvOptionsUseTheActualExtension` (line 39)
- `testLatin1ConversionsAcceptTheIconvOptionToo` (line 46)
- `testFixUtf8LeavesValuesThatAreNotTextAlone` (line 54)
- `testVendoredSourceHasNoCurlyBraceStringOffsets` (line 62)

### `tests/Unit/Vendor/JsMinifierTest.php` (5)

- `testWhitespaceCommentsStringsAndRegexArePreservedAsNeeded` (line 8): 28 data-provider cases
- `testPeekReturnsFalseAtTheEndOfTheInput` (line 47)
- `testCommentsPreserveTokenBoundariesAndStringContents` (line 60): 15 data-provider cases
- `testModernSyntaxKeepsItsMeaning` (line 90): 30 data-provider cases
- `testMalformedProgramsThrowAndRestoreOutputBuffer` (line 160): 4 data-provider cases

### `tests/Unit/Vendor/MarkdownTest.php` (8)

- `testQuoteHandlerRejectsANonmatchingExtensionMarker` (line 8)
- `testBothBundledLoadersWorkInAFreshPhpRuntime` (line 17)
- `testSupportedBlockAndInlineSyntax` (line 24): 47 data-provider cases
- `testRenderingThatChangedWithParsedown18` (line 34): 7 data-provider cases
- `testAnImageAddressCannotBreakOutOfItsAttribute` (line 50)
- `testConfigurationAndRepeatedParserInstances` (line 109)
- `testHtmlMarkupPreservesCallerLibxmlStateAndBufferedErrors` (line 123): 2 data-provider cases
- `testNestedHtml5MarkupReportsParserWarningsAndStillRenders` (line 136)

### `tests/Unit/Vendor/ParsedownApiTest.php` (12)

- `testTheOptionSettersReturnTheParserAndChangeTheOutput` (line 14)
- `testLineRendersInlineMarkdownOnly` (line 32)
- `testALinkInsideALinkIsNotNested` (line 40)
- `testSafeModeNeutralisesActiveContent` (line 47): 6 data-provider cases
- `testSafeModeKeepsOrdinaryLinksAndImages` (line 67)
- `testSafeModeKeepsParsedownExtraIdAndClassAttributesAndDropsRawHtml` (line 76)
- `testStrictModeRequiresASpaceAfterTheHeadingMarker` (line 84)
- `testPlainParsedownHandlesHtmlBlocks` (line 91)
- `testBlockParserEdgeCases` (line 100)
- `testSubclassHooksStillWork` (line 125)
- `testDefinitionListsWithBlankLines` (line 179)
- `testBlockMarkupContinuesAcrossBlankLines` (line 189)

### `tests/Unit/Vendor/YasumiDateQueryTest.php` (5)

- `testMissingAndUnparseableDatesUseTheCurrentTimestamp` (line 22): 3 data-provider cases
- `testValidDateYearAndLocaleAreKept` (line 30)
- `testCachedProvidersAreSharedAndDatesUpdate` (line 39)
- `testHolidayAndWorkingDayQueriesUseTheRealProvider` (line 48)
- `testScheduleComparisonUsesActualNativeDateTimeObjects` (line 55)
