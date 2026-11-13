# Session and HTTP-helper unit coverage

## Sessions and nonces

`tests/Unit/Core/Session/SessionTest.php` runs each case in an isolated PHP process
with temporary session storage. It tests start/refresh/id/snapshot behavior,
falsy stored values and deletion, one-time nonce consumption, expiry without
sleeping, corrupt TTLs, invalid keys/TTL/configuration, weak randomness rejection,
missing session globals, failed storage/retries, destruction listeners, cookie
cleanup control paths, and restart behavior.

Native session functions and real temporary storage are used. The cryptographic
failure case supplies controlled bytes with the strong flag false through a
test-local namespaced primitive; ordinary generation uses native OpenSSL. These
are PHP-session unit checks, not browser/cookie or flat-webapp integration tests.

The suite initially passed without coverage but isolated coverage processes did
not inherit the parent's command-line zend_extension setting. The local test
helper now passes PHP_INI_SCAN_DIR including its retained conf.d/99-xdebug.ini,
so both parent and children load Xdebug. No framework behavior was changed for
that runner correction, and no tests were skipped to hide the driver issue.

Only session source change: the wrong-instance guard immediately after
`new nonce_meta(...)` is retained in a CONSERVATIVELY DISABLED comment. A successful
new expression returns that class instance; a failed constructor throws before
the guard. The unreachable dump/die branch is not data validation. TTL, consumed,
expiry, random-strength, and session-global checks remain active and tested.

Session controller coverage is **114/114 lines, 12/12 methods**. One observed
policy is preserved for later review: failed session storage exhausts retries and
returns an empty id with PHP_SESSION_NONE, while $_SESSION exists. This batch
does not turn that outcome into a new exception policy.

## HTTP request and configuration

`tests/Unit/Core/Curl/{RequestTest,ConfigTest}.php` exercise URL components and
validation, form encoding, JSON failure, existing raw-mode behavior, explicit
configuration, defaults, selector behavior, numeric validation, and cache paths.
No HTTP requests are sent by those tests.

Current findings preserved pending explicit decisions:

- JSON currently labels its body application\\json rather than application/json.
- Raw mode rejects a supplied nonempty content type and never assigns the body.
  The maintainer has been asked whether to implement the documented formats.
- The config constructor overwrites declared text defaults with null. Its Chrome
  selector expansion is overwritten by the raw numeric selector afterward.
- Current response code sets body headers but does not set CURLOPT_POSTFIELDS.
  Transport body correctness is not established merely by constructor coverage.

Request coverage is **36/36 lines, 2/2 methods**. Configuration coverage is
**23/23 lines, 1/1 methods**. These characterize current behavior; reaching line
coverage does not declare the above malformed formats intentional or fixed.

## Controlled response tests

`tests/Unit/Core/Curl/ResponseTest.php` supplies controlled cURL primitives. Handles
are real allocated cURL resources, but execution/results/options are controlled by
the fixture; no outbound HTTP traffic occurs. Fixture state, handles, libxml
settings, and temporary files are cleaned up.

Cases cover status families, constructor input forms, method/header setup,
temporary and caller-owned response files, save flags, cookie-file options and
cleanup, invalid files, DOM/XPath, transfer errors and error getters, missing info,
robots user-agent/path precedence, zero-valued text, and filesystem-write errors.
The save-path failure test observes the expected fopen warning and then verifies
the library's system_error; it does not suppress unrelated warnings.

Response coverage is **183/183 lines, 10/10 methods**. This is unit coverage of
wrapper behavior; live networking, TLS/proxy interoperability, and browser-level
cookies remain unverified. Other findings are preserved: get_dom checks doc
emptiness rather than DOM state; missing_curlinfo's exception definition is absent,
so that defensive path raises Error. No response source fix was guessed.

## Latest full run

PHP 7.2.34 / PHPUnit 8.5.56 / Xdebug 2.9.8:
**404 tests passed / 2,609 assertions**, no failures, errors, or skips.

- Framework lines: **25.25% (2,741/10,854)**.
- Framework methods: **30.70% (326/1,062)**.
- Framework classes: **24.17% (80/331)**.

No coverage exclusions were added. Static estimates are replaced with runtime
line counts as files load, so denominator changes are not all source deletion.
Raw reports and retained local runner configuration are in
`/workspace/flat-environment/php72-tools`.

## Approved request-format repairs (MOD-030)

The maintainer approved fixing both malformed formats. This supersedes the JSON
and raw-mode findings above: JSON now uses application/json, and raw requests
require a nonempty string content type and preserve the supplied data. JSON's
strict encoding-failure check accepts valid `0`. Both modes accept supplied falsy
values; NULL still indicates no supplied body. Form empty-input handling remains
unchanged. Regression cases include 0, false, empty strings, JSON empty arrays,
raw embedded NUL bytes, invalid content types, and no-body defaults. The new
expectations failed against the original code before the repair.

Full PHP 7.2.34 run: **418 passing tests / 2,636 assertions**. Request coverage:
**37/37 lines, 2/2 methods (100%)**. Framework: **25.26% lines (2,742/10,855)**,
**30.70% methods (326/1,062)**, **24.17% classes (80/331)**.

Configuration defaults/selector behavior and response body transport remain
follow-ups. Request construction tests do not verify outbound body delivery.

## MOD-053 facade

cURL facade is **10/10 lines, 1/1 method (100%)** using controlled primitives.
Undefined callback scaffolding is retained as disabled original code, removing
its notices without adding a callback API. Third cookie argument is still unused;
actual request-body transport, networking, and flat-webapp integration remain open.


Legacy ORM session continuation (MOD-092): both the historically declared session-driver name and its ORM filepath name load and share one implementation. Handler constructor options propagate. Configuration uses root-relative data/orm keys because config's flat-prefix truncation remains characterized separately. Real isolated PHP children persist a data object through native file sessions. Native destroy's regenerate-after-destroy warning is preserved pending lifecycle intent; root flat/data/orm selection remains unresolved.


REST continuation (MOD-095): returned error responses supply their own message/data; optional metadata is initialized to null for status-only errors. Handled ordinary exceptions receive their original message/exception and no always callback when no response exists. The backing request-method getter is still unassigned, pending incoming-versus-dispatched verb intent.


Legacy root ORM continuation (MOD-100): the declared fallback session driver is now constructed and stored. Real default set_data persists a native session object; get still discards the driver result and returns null, preserving current behavior. Nonempty constructor arrays still cannot act as drivers. Custom selection and return contracts remain unresolved. API's unassigned private request-method return is retained in a conservative comment; its exception behavior remains unchanged.
