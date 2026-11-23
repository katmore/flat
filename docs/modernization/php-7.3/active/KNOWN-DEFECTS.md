# Known defects and owner questions, php-7.3 phase

The 79 known defects of php-7.2 (KD-01 to KD-79) are all resolved; the record is
[../../php-7.2/active/KNOWN-DEFECTS.md](../../php-7.2/active/KNOWN-DEFECTS.md). No test
carries `@group known-defect` now. A defect found in this phase starts here as KD-80 and
follows "How each fix lands" in that file. Questions continue the php-7.2 numbering.

## Defects found in this phase

### KD-80 - JShrink drops a character after a regex that starts with a quote

- Category: A in effect (the owner confirmed the intent, Q37). `src/flat/vendor/tedious/JShrink/minifier.php`,
  `loop()`: after `saveRegex()` a `continue;` meant "next loop pass" but, inside two nested
  `switch` statements, only left the inner one. The loop then called `getReal()` a second time and
  lost the character `saveRegex()` had already read: `return /'a'/.test(s);` became
  `return /'a'/test(s);` and `return /'q'/; foo();` lost its `;`.
- Fixed (MOD-196): `continue 3;`. Tests assert the correct output.
- **Fixed.**

The next seven were hidden until the test image reported everything (Q40, MOD-200). Each is
fixed at its cause; "behavior" says what a caller can see.

### KD-81 - Html encode calls a non-static method statically

- `flat\core\html\encode::_option_to_param()` is private, uses no `$this`, and was called
  statically (deprecated in PHP 7, fatal in 8). Now `static`. Behavior: none. **Fixed (MOD-201).**

### KD-82 - Curl request appends to an undefined variable

- `flat\core\curl\request`, `urlencoded` bodies: `$fields_string` was never initialized (notice).
  Now `''`. Behavior: none. **Fixed (MOD-201).**

### KD-83 - `bad_dim` message lost its reason

- `flat\core\util\image\exception\bad_dim::__construct($reason)` used an undefined `$err`, so the
  message was always `bad dimension specified: .`. Now it uses `$reason`. Behavior: the message
  is `bad dimension specified: width must be numeric.` and so on. **Fixed (MOD-202).**

### KD-84 - Collection `get()` and `is_exist()` read a missing key

- `flat\core\collection\controller`: an unknown key read an undefined index (notice) before
  answering `null` / `false`. Now checked with `isset`. Behavior: none. **Fixed (MOD-201).**

### KD-85 - Mapper exception did not name the missing property

- `flat\core\mapper`: the `cannot_get_property` thrown for a missing property used an undefined
  `$property`, so its message had an empty name. It now names the property. Behavior: the message
  is `mapper cannot get property: missing reason: does not exist`. **Fixed (MOD-202).**

### KD-86 - `each()` in the ORM field map

- `flat\orm`: `each()` is deprecated since PHP 7.2. Replaced by `key()`, `current()` and
  `next()` on the same array. Behavior: none. **Fixed (MOD-201).**

### KD-87 - JShrink `strpos()` with a `false` needle

- `src/flat/vendor/tedious/JShrink/minifier.php`: at the end of the input `$this->b` is `false`
  and went to `strpos()` (deprecated in PHP 7.3; it was never found, and still is not). The call is
  now guarded. Behavior: none. **Fixed (MOD-201).**

### KD-88 - XML encode of a resource raised warnings

- `flat\core\xml\encode`: a resource went through `htmlspecialchars()` (warning, returns `null`)
  and its checksum read an undefined `$input` (notice, hashes the empty string). The 7.2 phase knew
  and deferred it; the test pinned the four warnings. Fixed without changing any output: only
  scalars are escaped, and the checksum hashes `''` explicitly. The test now fails on any warning.
  Whether the checksum should cover something else is Q42. **Fixed (MOD-207).**

### KD-89 - JShrink removes comment separators and changes quoted strings

- Category: A. Adversarial finding 1. The global removal of empty comments joined tokens and
  deleted literal text inside strings. Other block comments also lost their separating whitespace.
- Fixed (MOD-218): parse empty comments normally, preserve token separators and comment
  newlines, and keep adjacent plus/minus operators separate. String contents remain literal.
- Tests: the minifier comment cases check output in PHPUnit and execute original/minified
  programs with `docker/bin/test-javascript`. Both checks failed before the fix.

### KD-90 - cURL and Markdown HTML parsing leak libxml error capture

- Category: A. Adversarial finding 2. Both HTML parsers enabled global libxml error capture,
  leaving later XML operations silent. cURL XPath error handling also depended on that leak.
- Fixed (MOD-219): HTML parsing respects the caller's setting and buffered errors. XPath captures
  diagnostics only for the query, reports failures through the existing `xpath_error`, and restores
  the setting in `finally`. No errors are discarded by the parsers.
- Regression checks cover both caller modes, existing buffered errors, invalid XPath and warning
  propagation. The nested HTML5 Markdown fixture checks its two native parser warnings as well
  as its rendered output; those warnings were previously hidden.

### KD-91 - Form keys containing delimiters are sent incorrectly

- Category: A. Finding 3, fixed (MOD-220): encode field names as well as values. Regression
  checks decode reserved characters and UTF-8 keys back to their original fields.

### KD-92 - URLs without a scheme raise undefined-index notices

- Category: A. Finding 4, fixed (MOD-220): reject absent schemes through `bad_param` before
  accessing the parsed scheme. Four missing-scheme URL cases now exercise the public constructor.

### KD-93 - Nested form values reach urlencode as arrays

- Category: B, resolved by the owner's Q45 decision. Fixed (MOD-227): reject nested arrays,
  non-stringable objects and resources with `bad_param` before form encoding. Scalar, null and
  stringable values retain their encoding; nested JSON bodies are unaffected.

## Owner questions

Each has a recommendation; "agree" is enough. Questions 37 to 45 are answered; none is open.

37. **JShrink: what did `continue` in the regex branch mean?** In
    `src/flat/vendor/tedious/JShrink/minifier.php`, `loop()`, a `continue;` sits inside two
    nested `switch` statements. In a `switch` it behaves like `break`, so it only left the inner
    switch; PHP 7.3 reports this (a warning, which PHPUnit turns into a failure). MOD-190 wrote
    it as `break`, which is exactly what it did before, so output is unchanged. The probable
    intent was to go straight to the next loop pass (`continue 3`), which would skip the
    "reg check of doom" that follows. That would change what the minifier outputs for some input,
    so it is a behavior change, not a compatibility fix.
    Recommend: keep `break` (no behavior change in this phase). If you want the loop
    `continue`, it goes through the known-defect process as KD-80, with a test that pins the
    input where the two differ.
    **Answered (owner, 2026-11-21):** fix it as intended, even with a behavior change. Done as
    KD-80 (MOD-196): `break` really did corrupt the output.

38. **Two tests switch off the AWS SDK's PHP deprecation notice (prime directive 2).**
    `tests/Unit/Cloud/AwsConfigTest.php` and `tests/Unit/Cloud/S3MultipartTest.php` set the
    environment variable `AWS_SUPPRESS_PHP_DEPRECATION_WARNING=true` (the first with the comment
    "deliberately verifies the maintained PHP 7.2 baseline"; both since KD-19-23, commit
    `66ec091`). The locked SDK (3.337.3) emits an `E_USER_DEPRECATED` whenever a client is built
    on a PHP version below 8.1, telling the user to upgrade to 8.1. So the notice is about the
    PHP version the whole project runs on, and it is the same on 7.2 and 7.3. The suppression
    was not found by the audit of v1.72.1 (MOD-180 to MOD-184), which searched for
    `error_reporting`, `ini_set`, `@` and ignore lists. flat's own code does not set it; this is
    test code only, so consumers are not affected by it.
    The cause cannot be fixed in a PHP 7.x phase: the notice goes away only on PHP 8.1 or with an
    older SDK, and neither is this phase's step. Nothing was changed and the tests still pass with
    the suppression. Options:
    - (a) Approve the two test setups as an exception in the "Approved exceptions" list of
      `AGENTS.md`, with a comment block in each test saying so, until the PHP 8.1 phase removes
      the cause. Recommended: it is honest (the notice is true), narrow, and test-only.
    - (b) Lock `aws/aws-sdk-php` in `tests/docker/runtime/composer.json` to a release that
      predates the notice, which removes the cause but tests an older SDK than the one the
      lock holds now.
    - (c) Remove the suppression and let the tests fail until PHP 8.1. Not recommended: a red
      suite for three phases hides real failures.
    Recommend (a). Only the owner can add to the exceptions list.
    **Answered (owner, 2026-11-21): (a).** Both tests are now in the "Approved exceptions" list
    of `AGENTS.md` and carry a comment block; the php-8.1 phase must remove them (MOD-198).

39. **`ConfigTest` skips a test when run as root (prime directive 2).**
    `tests/Unit/Core/ConfigTest.php` (line 164),
    `testDirectoriesAndUnreadableFilesAreNotLoadedAsConfig`, calls `markTestSkipped()` when
    the process is root, because root can read every file. The harness never runs PHP as root
    (`docker/bin/test` uses user 1000) and the full run reports no skipped test, so nothing is
    hidden today. But "never skip a test" has no exception for this, and a run as root would
    pass quietly without running the check. Not changed.
    Recommend: replace the skip with an assertion that fails when the process is root
    (the check cannot work there), so a wrong environment is red, not silently green.
    This is a test-only change; it needs your word because it touches a rule.
    **Answered (owner, 2026-11-21): agreed.** Done in MOD-197.

40. **Errors the test environment cannot see (prime directive 2).** The test image had no
    `php.ini`, so PHP's default hid `E_NOTICE`, `E_STRICT` and `E_DEPRECATED`, and PHPUnit 8.5 does
    not fail on deprecations; the statements "no notice or deprecation" of MOD-189 to MOD-192
    only covered warnings and errors. Details: [HIDDEN-ERRORS.md](HIDDEN-ERRORS.md).
    **Answered (owner, 2026-11-21):** deprecations and warnings are never suppressed on dev, and
    everything found is fixed now, not deferred. Done: the image uses `php.ini-development`
    (`E_ALL`) and PHPUnit fails on deprecations (MOD-200); the 20 failing tests, 7 causes plus
    test corrections, are fixed (KD-81 to KD-87, MOD-201 to MOD-203). The rule is in `AGENTS.md`.

41. **A test helper swallows the Authorize.Net SDK's deprecation notice (prime directive 2).**
    `tests/Support/LegacyAuthorizeSdk.php` installs an error handler while a legacy SDK class
    loads, and discards every `E_USER_DEPRECATED` raised from the SDK's `lib/deprecated/` files
    ("AuthorizeNetCIM is deprecated, use AuthorizeNet::API instead"). It is not in the approved
    list and my earlier searches missed it. The cause is that flat's Authorize.Net wrappers are
    built on the SDK's deprecated classes; removing it means porting them to the SDK's current
    API, which changes flat's public API, so it cannot be done in a PHP step. Applications that
    use those wrappers see the same notice at run time. Not changed.
    Recommend: approve it as an exception like Q38 (narrow: only that notice, only from those
    files, only in this helper; comment block and an `AGENTS.md` entry), and add the port to
    `docs/modernization/DEFERRED-V2.md`. Alternative: do the port now as a v2 change.
    **Answered (owner, 2026-11-21): agreed.** Done in MOD-209 (`AGENTS.md`, comment block, DEFERRED-V2 item 2).

42. **XML resource checksum covers empty data (intent).** KD-88 removed the warnings and kept the
    output: `encode::serialize($resource, ['checksum'=>['md5']])` writes the checksum of the empty
    string (the old code hashed an undefined `$input`). A checksum of nothing says nothing.
    Recommend: keep it for the 1.x line (changing it changes output for any consumer that stored
    it) and decide at the v2.X releases what a resource's checksum should cover; add it to
    `docs/modernization/DEFERRED-V2.md` if you agree.
    **Answered (owner, 2026-11-21): agreed.** Written down as DEFERRED-V2 item 3 (MOD-209).

43. **A `PATCH` request kills the application with a PHP fatal error (flat-webapp, your code).**
    `app/AppWeb/ApiController.php` calls `\flat\core\controller\api::set_method()` before it installs
    its error handlers. flat accepts only `DELETE GET TRACE OPTIONS POST PUT HEAD`, so `PATCH` (or any
    other method) throws `unknown_method` uncaught: a `PHP Fatal error` in the server log and a bare
    500 page instead of JSON. Found by the functional tests, whose server-log check fails on it.
    Not changed (`app/` is yours); `PATCH` is left out of the tests until you decide.
    Recommend: answer `501 Not Implemented` (RFC 7231: the server does not recognize the method),
    for example around the call:
    `try { api::set_method($requestMethod); } catch (api\exception\unknown_method $e) { http_response_code(501); header('Content-Type: application/json'); echo json_encode(['message'=>'method not implemented']); exit; }`
    Supporting `PATCH` itself would be a new flat interface: a v2.X matter.
    **Answered (owner, 2026-11-21):** a 500 is fine, and flat has no 501 status or response (checked:
    its API statuses stop at `server_error`), so no new status is invented and `app/` is not changed.
    `PATCH` support stays out until v2.X, after the phases reach the latest stable PHP
    (`DEFERRED-V2.md` item 4, MOD-211). The fatal error stays a known behavior of that application.

44. **Malformed JSON is treated as empty input (flat-webapp, your code).**
    `app/AppWeb/InputTrait.php` checks `json_decode()` against `false`, but a malformed document
    gives `null`, so the "malformed JSON in request document" error can never happen and the
    request carries on with empty input. The functional tests pin the current behavior.
    Not changed. Recommend: test `json_last_error() !== JSON_ERROR_NONE` instead; the existing
    error then gives a 500 "Unable to process this request" (a `400` would suit a client mistake
    better).
    Owner's rule (2026-11-21): `app/` and `web/` model the one known consumer and change only for a
    literal bug; no custom 500 handling and no new flat object. This is a literal bug. The obvious
    fix does not give a clean error response: `_get_input()` runs before the controller installs its
    error handlers, so the branch's `trigger_error(E_USER_ERROR)` is a PHP fatal error (blank 500 and
    a `PHP Fatal error` in the log).
    **Answered (owner, 2026-11-21): fix it, nothing more.** Done in flat-webapp (MOD-214): the check
    is `JSON_ERROR_NONE === json_last_error()`. A malformed JSON body now ends in that fatal error,
    and a valid JSON `false` body is accepted. The functional suite cannot include a malformed
    body, because its server-log check rejects any PHP fatal error.

45. **Nested form values (follow-up to finding 3).** The existing form encoder accepts arrays
    or objects of scalar values. Nested arrays reach `urlencode()` and raise a native warning.
    Encoding reserved characters in keys is fixed independently. Should nested values be rejected
    with `bad_param`, or encoded as bracketed form fields? Recommend rejecting nested values
    explicitly; supporting nested form structures is a separate behavior choice. No nested-value
    behavior was changed while fixing key encoding.

    **Answered (owner):** reject nested form values explicitly. Done in MOD-227 (KD-93), with
    regressions for array/object form containers, bracketed query strings, invalid field values
    and preserved flat-value/JSON encoding.

46. **flat's Mongo update count cannot work on MongoDB 5.1 or later.**
    `flat\db\driver\mongo::get_affected_records()` sends the server the `getLastError` command
    and reads `updatedExisting` and `n` from the answer. MongoDB removed that command in 5.1, so
    after an update, `update()` with `count` or `not_modified_exception` throws `not_modified`
    ("no update data found") on a 5.1 or newer server. Found when the test service moved to MongoDB 7.0
    (`CrudTest::testUpdatedRecordCountUsesTheRealAdapterWriteResult` and one more fail there). The
    adapter already computes the same fields client-side and returns them from `update()`; flat
    discards that result. The test service is MongoDB 5.0, the newest where flat works. An
    application on a newer server hits this today. Not changed (a library change, outside the tooling
    step). Recommend: read `n` and `updatedExisting` from the `update()` result; same answers on
    supported servers, and it works on 5.1 and later. Then raise the test service to 7.0.
    Which MongoDB version does your consumer run? **Open.**

47. **flat's default RabbitMQ queue is rejected by RabbitMQ 4.** The driver's defaults
    (`src/flat/db/driver/rabbitmq.php`) are `durable` false, `exclusive` false, `auto_delete` true.
    RabbitMQ 4.0 and later deny transient non-exclusive queues, so declaring a queue with those
    defaults closes the connection (`INTERNAL_ERROR - Feature transient_nonexcl_queues is
    deprecated`). Found when the test service moved to RabbitMQ 4.4; two `RabbitMqTest` tests fail
    there. The test service is RabbitMQ 3.13.7, the newest where the unchanged library works.
    A caller on a 4.x broker can pass `durable` or `exclusive` itself; the defaults cannot change in
    v1.X (a changed default is a behavior change). Not changed.
    Recommend: note it in the release notes, and decide the default at v2.X (`DEFERRED-V2.md`).
    Which RabbitMQ version does your consumer run? **Open.**

