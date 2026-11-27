# Adversarial review of php-7.4

Reviewed commit `34c638d`, including inherited framework behavior, on 2026-11-27.
The comparison base is the end of php-7.3 (`de2be1e`). This is a review, not a set
of source fixes. These findings describe that snapshot; all eleven are now fixed
(MOD-333 to MOD-335). Current status: [KNOWN-DEFECTS.md](KNOWN-DEFECTS.md).

## Resolution

MOD-333 fixes the five PDO defects, MOD-334 the four cURL defects, and MOD-335 the
two SSH defects. Q62 was answered: verify supplied pins before either authentication
method and keep unpinned callers allowed. Regression tests now cover the reproduced
cases. See [../CHANGELOG.md](../CHANGELOG.md) for verification of the fixes.

## Original findings (at 34c638d)

### KD-127 / P1: Password SSH authentication does not verify the server identity

`src/flat/core/ssh.php`, lines 236–239 and 303–312: password authentication
sends the password immediately after `ssh2_connect()`. Host fingerprint checks
exist only in the public-key branch. Password clients have no equivalent host
identity enforcement in the wrapper.

Reproduced against the real OpenSSH service: assign a deliberately incorrect
`get_auth()->fingerprint` before a password connection, using the same mechanism
the public-key tests use for pinning. The connection still authenticates and
succeeds. The supplied pin is ignored. The existing fingerprint tests use
public-key authentication exclusively.

An attacker able to redirect a connection to their SSH server could receive the
password. SSH encrypts the transport but cannot establish the intended server's
identity without trusted host-key verification.

Recommendation: verify an explicitly trusted fingerprint before sending either
kind of credential. **Category B / Q62:** the owner must decide the supported
pinning API and the default behavior of unpinned callers before a compatibility
change is made. This security issue is open; no trust policy was silently chosen.

### KD-117 / P1: PDO parameters share the last bound value

`src/flat/db/driver/pdo/pdo.php`, lines 174–181: `prepare_map()` calls
`bindParam($k, $v, ...)` repeatedly on the same loop variable. PDO retains a
reference, so all placeholders use the final value when executed.

Reproduced with SQLite: `SELECT :a AS a, :b AS b`,
`'bindParam'=>[':a'=>11, ':b'=>22]`, `'execute'=>true` returns
`{"a":"22","b":"22"}`. This can select or change the wrong records when a
statement has multiple values or predicates. Existing tests bind one parameter.

Recommendation: bind distinct stable storage per parameter, preserving the
method's public calling convention; test multiple parameters with different values.

### KD-118 / P1: Boolean and decimal bindings become SQL NULL

`src/flat/db/driver/pdo/pdo.php`, lines 164–181: the binding type starts as
`null` and is assigned only for integers and strings. Passing that type to PDO
means `PDO::PARAM_NULL`, including for booleans and floats.

Reproduced: `prepare_map()` with `SELECT :v AS v`, `'execute'=>true` and
`'bindValue'=>[':v'=>true]` returns `{"v":null}`; `1.25` also returns NULL.
The same type selection exists in the `bindParam` path. Writes can silently lose
amounts and flags. Existing tests cover integer, string and NULL values.

Recommendation: distinguish NULL, boolean, integer and other scalar values;
preserve decimal precision rather than binding decimals as integers.

### KD-119 / P1: Decimal criteria select a different record

`src/flat/db/driver/pdo/statement.php`, lines 371–377: floats are bound as
`PDO::PARAM_INT`. SQLite truncates the criterion before comparing it.

Reproduced with rows `(id=1, price=1.25)` and `(id=2, price=1.0)`: criteria
`['price'=>1.25]`, mapped to `i.price`, returns row 2, not row 1.
The statement tests cover integer and string criteria, not decimals.

Recommendation: retain the fractional part in bound criteria; assert which row
is selected for adjacent decimal and integer values.

### KD-120 / P2: Structured INNER and RIGHT joins execute as LEFT joins

`src/flat/db/driver/pdo/statement.php`, lines 300–319: the requested direction
is checked but SQL generation always writes `LEFT JOIN`. The condition also
assigns `"OUTER"` instead of comparing it, accepting invalid directions.

Reproduced with an item and an empty labels table: a structured `INNER` join
returns the unmatched item. `RIGHT` and `INVALID` produce the same result.
The existing structured-join test exercises `LEFT` only.

Recommendation: generate the requested supported direction and reject invalid
ones; test an unmatched row so INNER and LEFT have different results.

### KD-121 / P2: A configured method without a body becomes GET

`src/flat/core/curl/response.php`, lines 328–329: `CURLOPT_CUSTOMREQUEST` is
set only inside the nonempty-body branch. Configuring DELETE, HEAD, PUT or POST
without data leaves libcurl's default GET in place.

Reproduced against a real local PHP HTTP server: `new response(new config(new
request($url), 'DELETE'), [])` reaches the server as GET. The tests exercise
GET without data and POST/PUT with data, so they miss this dispatch error.

Recommendation: apply an explicit method independently of body presence;
handle HEAD without consuming a response body. Keep the documented automatic
GET/POST selection when the method is NULL.

### KD-122 / P2: Zero-valued bodies are dropped

`src/flat/core/curl/response.php`, line 328: `empty($request->data)` treats
`"0"` as absent, despite `request` explicitly accepting falsy JSON/raw bodies.
MOD-321 added body transmission inside this existing guard, leaving this case
broken.

Reproduced against the HTTP server: JSON integer `0` configured as POST and raw
string `"0"` configured as PUT both arrive as GET with an empty body. Expected
bodies are the single byte `0`. Request encoding tests alone do not verify the
bytes actually sent.

Recommendation: distinguish an absent body from an empty or zero-valued body;
add transport-level assertions for JSON 0 and raw "0".

### KD-123 / P2: Lazy DOM access returns NULL

`src/flat/core/curl/response.php`, lines 54–56: `get_dom()` checks `_doc`,
the downloaded HTML, rather than `_dom`. An ordinary nonempty response therefore
skips DOM initialization unless constructed with `use_dom` or used with XPath
first.

Reproduced: fetch `<html><body><p>hello</p></body></html>` without `use_dom`, then
call `get_dom()`: NULL. Existing tests preinitialize with `use_dom`; the special
body `"0"` test passes because `empty("0")` happens to initialize it.

Recommendation: initialize based on whether the DOM exists and test ordinary
HTML with no eager-parsing flag.

### KD-124 / P2: Valid robots input raises notices and reuses prior rules

`src/flat/core/curl/response.php`, lines 127–138: `$rule` is assigned only
when a directive has a nonempty value, but is consumed on every line in the
matching group. A standard `Disallow:` with no value means no restriction.

Reproduced with `User-agent: *` followed by `Disallow:`: an undefined-variable
notice is raised. With a prior directive, its value is reused instead. Comments
and unrelated directives also enter this rule-building path. A URL with no path
has a separate missing `path` access at line 147 when rules are present.
Default requests enable robots checking, so normal external robots files can
break otherwise valid transfers under strict error handling.

Recommendation: parse only Allow/Disallow, initialize each rule separately,
ignore empty Disallow, and use `/` for an absent URL path. Test empty directives,
comments, unrelated directives and root URLs.

### KD-125 / P2: Negative limits bypass minimum validation

`src/flat/db/driver/pdo/statement.php`, lines 105–107: `min_range` is passed
at the top level of `filter_var()`'s options array instead of inside `options`.
The advertised lower bounds are never enforced.

Reproduced: rules containing `'limit'=>-1` are accepted. SQLite interprets
LIMIT -1 as unlimited, so an invalid negative limit can remove a caller's result
bound. Existing invalid-input cases test nonnumeric strings only.

Recommendation: nest the filter options correctly; test limit 0, limit -1 and
skip -1 as well as valid boundary values.

### KD-126 / P2: SSH disconnect leaves the session open and usable

`src/flat/core/ssh.php`, lines 433–442: `disconnect()` opens a channel, runs
`echo "EXITING" && exit`, and returns. Exiting that command does not disconnect
the underlying SSH transport; neither connection nor SFTP state is released.

Reproduced against the repository's real OpenSSH container: one SSH session
resource exists before and after `disconnect()`, `is_connected()` remains true,
and `exec('echo still-connected')` still succeeds. Only destroying the client
releases the session. Reconnecting the same object is rejected as already
connected. `SshServerTest::testDisconnectIgnoresMissingConnectionOnlyWhenRequested`
explicitly asserts the incorrect connected state instead of checking closure.

Recommendation: disconnect the transport and release dependent resources,
clear local state, and test commands/reconnection after explicit disconnect.
Keep the existing `ignore_missing` behavior.

## Review validation (before fixes)

All executions use Docker; no host PHP or Composer is used. Initial focused
reproductions ran in the repository's digest-pinned official PHP 7.4.33 CLI base
with `php.ini-development`, E_ALL and an error handler that throws on PHP
messages. SQLite, cURL and DOM are native extensions; HTTP requests go to a real
child PHP server. No PHP function overrides, SDK doubles or production source
changes are involved. Every finding was then reproduced in the complete
repository-built test image; SSH uses its real OpenSSH service.

Raw reproduction results from this workspace are under the ignored
`build/tests/review/` directory. The exploratory scripts were temporary, not
additions to the PHPUnit suite. The compact inputs and actual outputs above
describe each case independently of those temporary files.

Fresh results:

- `docker/bin/test all --offline --coverage`: 1,673 tests, 7,415 assertions,
  exit 0. Coverage: 332/332 classes, 1,105/1,105 methods, 8,877/8,877 lines.
  No tests were skipped or quarantined. The image and all services were first
  built/pulled online using the repository Dockerfiles and locked dependencies.
- flat-webapp commit `9e6fb73`, `php-7.4`, its own `docker/bin/test`: 67 tests,
  203 assertions, exit 0, including its server-log check. It uses the published
  flat release; no consumer code or lock was changed for this review.
- `docker/bin/test-javascript`: all 45 minifier programs preserve their results.
- Fresh API dump against the checked-in 7.4 dump: 602 entries, no differences.
- Consumer scan over flat-webapp: 41 files, all 24 current impact entries,
  zero hits; the generic PHP requirement still needs manual interpretation.
- Both `composer audit --locked` checks ran in the pinned Composer container:
  four runtime advisories (AWS SDK and MongoDB), the already-recorded abandoned
  annotations package, and no test-tool advisories. No advisory was ignored.
- `docker/bin/check-docs`: no problems; rerun after the final documentation edits.

The first full run was interrupted after proxy-related service errors. Docker
injects proxy variables but its default bypass list contained only localhost;
requests for the new internal HTTP services went toward the external proxy.
MOD-331 adds the fixed Compose service names to both proxy bypass variables,
retaining the host-supplied entries. The complete passing run uses that fix.
No production source or existing test assertion was changed to obtain it.
Initial Alpine/GitHub access restrictions were resolved by the owner before
building and running the unchanged repository images.

The real Authorize.Net sandbox was not contacted: its separate mode requires
owner sandbox credentials. The full suite uses the repository's WireMock
service, as designed; a mock pass is not a live payment-service certification.

## Scope and limits

Read the modernization plan, testing instructions, current phase handoff,
change log, defects, consumer impact and reference notes, and consulted previous
phase defects and deferred decisions. Reviewed the phase's production diff and
examined inherited PDO, mapping, cURL, SSH, sessions, routing, API dispatch,
configuration, asset paths and serialization code, alongside relevant tests.
This is a risk-focused review, not a claim that every possible input or every
third-party implementation has been verified.

The owner-approved class renames, deprecation exceptions and deferred API
method/status decisions are not presented as new defects. Release notes and
reference notes contain older test counts; use fresh execution results rather
than treating those snapshots or 100% line coverage as proof of correctness.
