# Deferred to v2.X

Changes the maintainer wants to make, but not in a `v1.X` release, because they would change
what an existing consumer sees or must do. The plan is to make them in the `v2.X` releases,
after the phases have caught up with the current PHP major version. Nothing here is lost or
forgotten: each item says what it is, where it came from and how it was meant to work.

Add an item when a change is wanted but would break the "drop-in" rule of the `v1.X` line.
Only the maintainer decides to move an item into a release.

## 1. A correct 405 for an unsupported API method

**Today (v1.72.2).** A request method the resource does not implement (for example `POST` to a
resource that only implements `GET`) answers `500 Internal Server Error`, from
`\flat\api\response\no_interface`. History: `v1.5.5` answered an empty `200`; `v1.72.0` and
`v1.72.1` answered `405 Method Not Allowed` (MOD-165); `v1.72.2` answers 500 (MOD-186, decision
Q36). See `docs/modernization/php-7.2/CHANGELOG.md` and `php-7.2/active/CONSUMER-IMPACT.md`.

**Why 500 for now.** A 405 must carry an `Allow` header that lists the supported methods
(RFC 7231, 6.5.5). flat never sends HTTP headers itself: the consumer's response handler writes
them (flat-webapp's `ApiController` writes the status and `Content-Type` only). So a correct
405 needs either a consumer change or flat sending headers, and the maintainer wants neither in
`v1.X`. 500 is less RESTful (a client mistake is reported as a server error, and clients,
proxies and monitoring treat 5xx differently) and was accepted knowingly.

**Plan for v2.X.** Answer `405` again, with a correct `Allow` header. The design that was worked
out and then set aside:
- A response header class `\flat\api\response_header\allow` (field `Allow`, value the methods
  joined by `, `), like the existing `etag` header.
- `\flat\api\response\no_interface` takes an optional list of methods, default `['GET']`, and
  carries the `Allow` header; an empty list sends none.
- `\flat\core\controller\api` passes the methods the resource implements, in alphabetical
  order, with `HEAD` listed when `GET` is (a HEAD request is answered by `GET_method()`); `TRACE`
  is not listed (the controller echoes the input for it on every resource). A protected method
  `_get_allowed_methods()` lets a subclass override the list.
- Decide then how the header reaches the client: the handler sends `get_header_strings()`
  (one line in flat-webapp's `ApiController`), or flat sends it. The maintainer's view so far:
  flat does not send headers anywhere else, so it should not start here.
- Tests assert the status, the header and the override; the consumer-impact document records
  the status change back to 405.

## 2. Remove the Authorize.Net wrappers

**Today (v1.73.1).** flat's `flat\cloud\authorize` wrappers (48 files) are built on the Authorize.Net SDK's
legacy classes (`AuthorizeNetAIM`, `AuthorizeNetCIM`, `AuthorizeNetARB`, ...). Authorize.Net deprecated those
in 2018 and no longer supports them; SDK 2.x removed them. Every wrapper class is marked `@deprecated` (no
runtime notice from flat); the SDK itself raises an `E_USER_DEPRECATED` notice when its legacy classes load, so
applications using the wrappers see that. One test helper discards it, as an approved exception (`AGENTS.md`).

The Capture Only transaction type (`customer\transaction\captureonly`) no longer exists at Authorize.Net: the
service refuses it with `E00027` (found on the sandbox, 2026-11-23; its transaction-types article says the method
is "no longer available"). It goes with the wrappers.

**Decision (owner, 2026-11-23).** Not ported. Deprecated in v1.73.1, removed in v2.X.

**Plan for v2.X.** Remove the wrappers, their tests, the test helper and the approved exception, and stop
requiring the SDK in the test environment. (Q41 and Q48, php-7.3.)

## 3. What the checksum of an XML-encoded resource covers

**Today (v1.73.0).** `flat\core\xml\encode::serialize($resource, ['checksum'=>['md5']])` writes
the checksum of the empty string: the original code hashed an undefined variable, and the warnings
were removed without changing the output (KD-88). A checksum of nothing says nothing.

**Plan for v2.X.** Decide what a resource's checksum should cover (for example its type), and change
it then, because changing it changes output a consumer may have stored. (Q42, php-7.3.)

## 4. Support for `PATCH`

**Today (v1.73.0).** flat accepts `DELETE`, `GET`, `HEAD`, `OPTIONS`, `POST`, `PUT` and `TRACE`
(`\flat\core\controller\api::VALID_METHOD_LIST`). Any other method, `PATCH` included, throws
`unknown_method`. In flat-webapp that happens before its error handlers exist, so the request ends
in a PHP fatal error (Q43, php-7.3).

**Decision (owner, 2026-11-21).** `PATCH` stays out until a `v2.X` release, which comes only after
the phases have caught up to the latest stable PHP release.

**Plan for v2.X.** Add `PATCH` to the valid methods with its own method interface
(`\flat\api\method\PATCH`, a `PATCH_method()` like the others), tests, and the matching consumer
guidance. It is a new public interface, so it cannot go into `v1.X`.

