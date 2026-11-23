# flat v1.73.1

A patch release of the PHP 7.3 line: bug fixes found by an independent review, support for current MongoDB,
RabbitMQ and php-amqplib, a deprecation, and every tool and service of the test environment brought up to date.
The public API is unchanged except for two protected members added to `flat\db\driver\mongo`
(`docs/modernization/php-7.3/notes/API-COMPARISON.md`). 1,481 tests pass on PHP 7.3.33 with 100% class, method
and line coverage.

## Deprecated

- **The Authorize.Net wrappers (`flat\cloud\authorize`) are deprecated and will be removed in v2.X.** Authorize.Net
  no longer supports the SDK classes they are built on (`AuthorizeNetAIM`, `AuthorizeNetCIM`, `AuthorizeNetARB`;
  the SDK's 2.x releases removed them). Every class is marked `@deprecated`; nothing changes until v2.X.

## Fixes

Each has a "before, after, what to search for" entry in `docs/modernization/php-7.3/active/CONSUMER-IMPACT.md`.

- **JavaScript minifier:** an empty comment (`var/**/x`, `return/**/1`, `x+/**/+x`) no longer joins tokens into
  broken code, and `/**/` inside a string is kept. Re-minify affected files.
- **HTML parsing (Markdown HTML blocks, cURL responses)** no longer switches libxml to silent error capture for the
  whole process. Their warnings now reach PHP's error handling, for example for HTML5 tags such as `<section>`:
  applications may see new warnings that were hidden before.
- **cURL requests:** form field names are URL-encoded (`a&b` used to split into two fields); a URL without a scheme
  throws `bad_param` (it raised a PHP notice); a form value that is an array, resource or non-stringable object
  throws `bad_param` (use JSON for nested data).
- **MongoDB:** update counts (`update()` with `count` or `not_modified_exception`, `get_affected_records()`) work on
  MongoDB 5.1 and later. They relied on a server command that MongoDB removed.
- **RabbitMQ:** the default queue works on RabbitMQ 4, where it is declared durable (an explicit `durable` or
  `exclusive` is never overridden); older brokers are unchanged. The driver works with php-amqplib 2 and 3.
- **Authorize.Net wrappers (deprecated):** `customer::create()` and `update()` now send the profile fields
  (`merchantCustomerId`, `description`, `email`); before, the profile was empty and the real service refused it
  (`E00041`). When Authorize.Net gives no usable answer (no connection, timeout, untrusted certificate), the
  wrappers throw `no_response`; before, the call returned as if it had worked.
- **Authorize.Net subscriptions (deprecated):** several calls on one `subscription` object no longer resend the
  earlier calls' data, and `get_info()` works (it always threw before).

## Tooling and services

Everything is at the newest release PHP 7.3.33 allows: PHPUnit 9.6, Composer 2.11, Xdebug 3.1.6, ImageMagick 7.1.2
with imagick 3.8.1, Node 26, MongoDB 9.0, RabbitMQ 4.4, php-amqplib 3.7.5, yasumi 2.4.0 and the rest of the locks
(`docs/modernization/php-7.3/notes/ENVIRONMENT.md`). The API comparison tool now detects return-by-reference changes.
flat-webapp's tests and Docker setup were brought current the same way.

## Known limits

- Two approved exceptions discard a deprecation notice from an SDK in tests: the AWS SDK's (until PHP 8.1) and the
  Authorize.Net SDK's (until the wrappers are removed in v2.X) (`AGENTS.md`).
- The four open dependency advisories (test environment only) need PHP 8.1 for their fixes
  (`docs/modernization/php-7.3/notes/DEPENDENCY-ALERTS.md`).
- Seven classes still cannot be loaded, as in v1.72.0.
