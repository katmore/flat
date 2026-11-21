# Adversarial review, PHP 7.3

Review date: 2026-11-20 (America/Los_Angeles). Report only; no source, tests or locks changed.
This review informs the unreleased v1.73.1. Findings below are recommendations for owner review.

## Scope and method

Reviewed flat c257625 to 8bee551 and flat-webapp 202644b to 9b8cc1a in fresh local
clones. Read every functional source hunk and changed framework test assertion, plus the
webapp functional tests, HTTP helpers, error handlers and Docker scripts. Followed suspicious
paths into unchanged source. Ran PHP as user 1000 through Docker; executed minified output
with Node to check semantics. Scratch probes and clones remain at /tmp/flat-review.UJ14Rq.

The first four findings are existing defects exposed by this review, not regressions introduced
by the PHP version step. They matter because the suite passes while missing these behaviors.

## Fix status

Findings 1 to 8 are addressed in MOD-218 to MOD-224 of [../CHANGELOG.md](../CHANGELOG.md).
Q45 remains open for the separate nested-form-value contract; reserved form key encoding is fixed.
Current validation passes 1,462 framework tests and 66 webapp tests. The findings below describe
the original reviewed commits. [Validation and mutation evidence](review-evidence/WEBAPP-FIXES.log).

### 1. P1: Empty JavaScript comments can turn working programs into broken code

In `src/flat/vendor/tedious/JShrink/minifier.php`, initialize removes every /**/ before lexical
processing. That removes a necessary token separator. Reproduce with minify followed by
JavaScript execution:

- var/**/result=1; becomes varresult=1; the variable result is never declared.
- function f(){return/**/1;} becomes function f(){return1;} and throws ReferenceError when called.
- var x=1; var result=x+/**/+x; becomes var x=1;var result=x++x; and throws SyntaxError.

`tests/Unit/Vendor/JsMinifierTest.php` only compares selected strings, including a harmless empty
comment between statements. It never executes the output. Recommendation: preserve token
boundaries and add execution-based checks for these cases. [Evidence](review-evidence/JAVASCRIPT.log).

### 2. P2: XML error capture leaks into unrelated application operations

`src/flat/core/curl/response.php` _init_dom and the bundled ParsedownExtra elementMarkup turn
libxml internal errors on without restoring the prior setting or checking the captured errors.
A caller starting with normal PHP XML warnings can silently lose them after either operation.

Reproduced by setting libxml_use_internal_errors(false), converting a Markdown HTML block
with markdown="1", and loading malformed XML. The setting is now true; loadXML returns false
and buffers an error instead of reporting the warning. The cURL test fixture restores the global
setting in tearDown, concealing the application's leak. Recommendation: scope capture to the
parse, account for errors, and test caller-visible state after the call. This is outside the four
approved exception sites. [Evidence](review-evidence/TOOL-PROBES.log).

### 3. P2: Form request keys are not encoded

`src/flat/core/curl/request.php` URL-encodes values but concatenates keys unchanged.
Input ['a&b'=>'x'] becomes a&b=x, which the receiver parses as two fields; ['a=b'=>'x']
becomes a=b=x, changing both the key and value. This silently changes submitted data.

`tests/Unit/Core/Curl/RequestTest.php` covers spaces in values but only simple ASCII keys.
Recommendation: verify decoded key/value round trips, including reserved characters.
Nested arrays also currently raise a native urlencode warning; decide their supported contract
rather than assuming every array value is a string. [Evidence](review-evidence/CURL-PROBES.log).

### 4. P2: Missing URL schemes raise a PHP notice before validation

In `src/flat/core/curl/request.php` parse_url, the scheme index is read without checking it exists.
Constructing a request with /relative, example.com, or //example.com/path raises Undefined index:
scheme under E_ALL. A strict consumer gets an ErrorException instead of the documented bad_param.
The invalid-URL data provider omits these common cases. Recommendation: reject absent schemes
through the existing exception path. [Evidence](review-evidence/CURL-PROBES.log).

### 5. P2: The webapp log gate misses its own handled fatal errors

flat-webapp docker/bin/test only rejects PHP message text matching its fixed regular expression.
AppWeb ErrorTrait and JsonErrorTrait log handled fatals and exceptions as JSON instead. A scratch
object using the actual ErrorTrait, after _enable_custom_errors and trigger_error(E_USER_ERROR),
logs errno 256 as JSON; the exact runner expression finds no match. Catchable/recoverable fatal
message prefixes are also absent from the expression.

A response assertion could catch a particular fatal; the claimed independent log safeguard does
not. Recommendation: validate structured error records as well as native PHP messages and prove
the gate rejects both. This was reproduced in Docker CLI with the real trait, not Apache.
[Evidence](review-evidence/HANDLED-FATAL.log).

### 6. P2: Webapp body tests cannot detect discarded or incorrectly parsed bodies

In flat-webapp tests/Functional/ApiTest.php, POST bodies are checked only against the same
unsupported-method 500 response. Returning an empty input map for every non-GET request leaves
those expected responses unchanged. That conclusion follows directly from the assertions and
resource interfaces; the HTTP mutation could not be run because dependency installation failed.

Malformed JSON and PATCH are explicitly excluded because the log gate rejects their intentional
fatal path. Thus the suite does not test that failure contract either. Recommendation: observe the
parsed map at a focused input boundary, and test expected fatal paths separately with exact log
expectations. Keep the owner's chosen 500 behavior; a 405 remains deferred to v2.

### 7. P2: API comparator misses return-by-reference changes

`tests/tools/apidump.php` records parameter references but omits ReflectionMethod::returnsReference.
In separate scratch classes, changing value(int $x) to &value(int $x) reports no differences.
The positive control, changing int to string, is correctly reported. No such API regression was
found in the phase's source diff; this finding limits the tool's assurance. Recommendation: include
this method-signature flag and a failing control. [Evidence](review-evidence/TOOL-PROBES.log).

### 8. P2: A fresh webapp run does not pin the reviewed framework

The frozen flat-webapp composer.json requires dev-php-7.3 from the remote Git repository and no
application composer.lock is tracked. docker/bin/test resolves dependencies anew. Therefore a
checkout of 9b8cc1a can use a later flat commit rather than 8bee551, plus later dependency versions.
This limits reproducibility even when downloads succeed. Recommendation: pin the reviewed pair
for review runs and use the published matching release when available, as the repository rules
require. Do not change the consumer's application behavior to address this tooling issue.

## Claims confirmed and qualifications

- Cold build of flat-tests-php73 succeeded with the supplied proxy CA. All 1,432 framework tests
  passed, 6,357 assertions, including MongoDB and RabbitMQ, using --offline --coverage after the
  images had been prepared. [Run](review-evidence/BASELINE.log).
- Coverage reports 331/331 classes, 1,062/1,062 methods, 8,050/8,050 statements. Clover includes
  all 629 source PHP files, including six empty files. The handoff's suggestion that entire files
  are absent was not reproduced. Runtime loadability still differs from test coverage: three SSL
  helpers are explicitly required under their actual legacy namespaces by tests. Seven expected
  class names fail the API dumper's normal loading, which is not proof that all seven files are
  wholly untested. No unsupported alternative coverage percentage is asserted here.
- The image uses php.ini-development and E_ALL. Independent user-notice and user-deprecation
  canaries both fail PHPUnit: [canaries](review-evidence/ERROR-CANARIES.log). This does not prevent
  a library or test installing its own capturing handler; finding 2 is one such blind spot.
- Re-dumping 620 API entries and comparing with the recorded v1.72.0 baseline produced no
  differences: [comparison](review-evidence/API-COMPARISON.log). The older baseline dump was not
  independently regenerated; finding 7 demonstrates a known limit of this comparison.
- ORM mapping matches the baseline for empty input, positional null/false/zero, associative input
  and sparse numeric keys. XML resource fallback matches byte-for-byte for three dump/checksum
  combinations using native var_dump. Xdebug's enhanced var_dump embeds source paths and line
  numbers, so its bytes naturally differ between scratch files; disabled that formatting only,
  not error reporting. [Differential checks](review-evidence/EQUIVALENCE.log).
- The quote-starting regex fix preserves execution results in the exercised regex, division and
  end-of-input cases. Finding 1 is separate unchanged minifier behavior. The changed response
  assertions about absent attributes are stronger than reading an undefined property as null;
  the null-coalescing reads check absence of caller text, not message existence.
- The ordinary XML document checksum still hashes null, regardless of input. The tests explicitly
  pin that legacy behavior; they do not establish useful data-integrity checks. The resource
  checksum decision in Q42 remains deferred and was not reopened or changed here.
- Both test lock diffs change only content hashes and platform versions; no packages moved.
  Composer validate --check-lock reports no stale lock, but exits unsuccessfully on missing
  publish-only name/description fields in the private manifests. [Output](review-evidence/LOCKS.log).
- Nineteen files are pure CRLF-to-LF conversions. Two more (ORM and collection controller) also
  have the reviewed code changes, accounting for 21 conversions. No existing file mode changed;
  tracked files have no CRLF. No tracked binary conversion was found.
- The four approved exception locations carry their comment blocks. No additional @ suppression,
  coverage-ignore markers or skipped-test calls were found in the inspected source/tests.
  Deliberately capturing and asserting expected diagnostics was distinguished from discarding them.
- Additional service-enabled mutation samples: seed 731, 11 killed and one survivor out of 12;
  seed 732, nine killed, two survivors and one invalid out of 12. The mapper survivor changes an
  obsolete unused option. XML :327 cannot encounter an algorithm name starting with a digit in
  this PHP runtime; :655 changes a redundant comparison after htmlspecialchars returns string
  or the resource branch yields null. These are equivalent for the reviewed reachable inputs,
  not three demonstrated missing tests. [731](review-evidence/MUTATION-731.log),
  [732](review-evidence/MUTATION-732.log). This sample does not prove overall mutation adequacy.

## Original review limits

- The webapp HTTP baseline could not complete: GitHub dependency downloads returned CONNECT
  tunnel HTTP 403 and Composer fell back to source downloads. Stopped that install after repeated
  failures; no webapp test pass is claimed. [Install log](review-evidence/WEBAPP-INSTALL.log).
- No exhaustive resolution check of every historical image digest/upstream commit, broad fuzzing,
  cold rebuild of both webapp images, or full mutation campaign. No claim of exhaustive correctness.
- The working-tree handoff correction passed docker/bin/check-docs before this report was written;
  the report and evidence are also checked before delivery. No fixes, releases or merges performed.
