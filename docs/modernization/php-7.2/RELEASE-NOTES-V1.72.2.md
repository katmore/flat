# flat v1.72.2

A patch release of the PHP 7.2 line. The public API is unchanged (no signature, class or
constant was added or removed). 1,428 tests pass with 100% class, method and line coverage on
PHP 7.2.34.

## What changed

- **A request method the resource does not implement now answers `500 Internal Server Error`
  instead of `405 Method Not Allowed`.** For example `POST` to a resource that only implements
  `GET`. The message is unchanged ("no interface found matching the resolved request method"),
  and the response handler is told as before.

  Why: a 405 must list the supported methods in an `Allow` header, and flat sends no HTTP
  headers itself, so flat could not answer a correct 405 without asking every consumer to change
  its response handler. The maintainer chose 500 over that. It is less RESTful, because a client
  mistake is reported as a server error, and it is a change from v1.72.0 for applications that
  updated to it. (v1.5.5 answered an empty `200` here.) Code that wants a 405 can return
  `\flat\api\response\method_not_allowed` itself; that response is unchanged.
- Tooling only: PHPUnit's result cache moved to `build/tests/`, and `docker/bin/test` sends
  Docker build progress to stderr so `docker/bin/test inventory` prints clean Markdown.

A correct 405 with an `Allow` header is planned for a v2.X release (`docs/modernization/DEFERRED-V2.md`).

## Upgrading from v1.72.1

Nothing to change in your code. If you check for a `405` from an API resource that lacks a
method, or monitor `500` responses, look at those places. Details are in the consumer-impact
document, entry MOD-186.

## Known limits

- Some dependency advisories affect only the test environment and cannot be fixed on PHP 7.2
  (`docs/modernization/php-7.2/notes/DEPENDENCY-ALERTS.md`).
