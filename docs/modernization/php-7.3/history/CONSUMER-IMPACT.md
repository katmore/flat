> Archived: completed phase material or a superseded work snapshot. Current status and work: [phase overview](../README.md). The record below describes its original checkpoint.

# Consumer impact, php-7.3 phase

Behavior changes of this phase that an application using flat may notice, in the format of
[../../php-7.2/active/CONSUMER-IMPACT.md](../../php-7.2/history/CONSUMER-IMPACT.md): what
consumers saw before, what they see now, and what to search for in their code.

## Behavior changes

### MOD-196 (KD-80) — JavaScript minifier keeps the character after a regex starting with a quote

- Before: the minifier (`\flat\vendor\tedious\JShrink\minifier`) dropped one character after a
  regex literal whose first character is a quote, for example `return /'a'/.test(s);` came out as
  `return /'a'/test(s);`, and `return /'q'/; foo();` lost its `;`. The minified JavaScript was broken.
- After: every character is kept: `return /'a'/.test(s);` and `return /'q'/;foo();`.
- Search consumer code for: `JShrink`, `minifier::minify`, and any minified JavaScript with a regex
  literal that starts with `'` or `"`; re-minify those files.
- Verified: yes (tests fail without the fix).

### MOD-202 (KD-83) — `bad_dim` message includes the reason

- Before: `flat\core\util\image\exception\bad_dim` always said `bad dimension specified: .`.
- After: `bad dimension specified: width must be numeric.` (or `height`, or `... must evaluate to an
  integer 0 or greater.`). Same class, same code, same conditions.
- Search consumer code for: `bad_dim`, code or logs that match the old message text.
- Verified: yes.

### MOD-202 (KD-85) — Mapper exception names the missing property

- Before: asking the mapper for a property that does not exist threw `cannot_get_property` with
  an empty name: `mapper cannot get property:  reason: does not exist`.
- After: `mapper cannot get property: missing reason: does not exist`. Same class and condition.
- Search consumer code for: `cannot_get_property`, `ignore_cannot_get`, code that matches the old text.
- Verified: yes.

### MOD-218 (KD-89) — JavaScript comments preserve program behavior

- Before: `var/**/result=1` became `varresult=1`, `return/**/1` became `return1`, and
  `x+/**/+x` became invalid `x++x`. Empty-comment text inside quoted strings was deleted.
- After: comments preserve needed token separators and line breaks; quoted comment text is
  unchanged. A line break inside a block comment after `return` still ends that return statement.
- Search consumer code for: `JShrink`, `minifier::minify`, and generated JavaScript containing
  block comments or quoted `/**/`. Re-minify affected assets.
- Verified: PHPUnit output regressions and Docker JavaScript execution checks fail without the fix.

### MOD-219 (KD-90) — HTML parsing preserves libxml reporting

- Before: cURL DOM parsing and Markdown HTML blocks enabled libxml error capture globally.
  Their HTML diagnostics and errors from later, unrelated XML operations could go unreported.
- After: both HTML parsers respect the caller's reporting mode and existing error buffer.
  With normal PHP reporting enabled, HTML parser warnings reach the caller. This includes
  libxml's warning for HTML5 tags such as `section`; rendered Markdown remains the same unless
  the caller's warning handler interrupts it. cURL invalid XPath still throws `xpath_error`
  with its diagnostic, and restores the caller's reporting mode on success or failure.
- Search consumer code for: `use_dom`, `get_dom`, `simplexpath`, Markdown HTML blocks,
  `libxml_use_internal_errors`, and warning handlers around those operations.
- Verified: state/buffer and warning regressions fail before the fix; output assertions retained.

### MOD-220 (KD-91, KD-92) — cURL form keys and missing URL schemes

- Before: form names such as `a&b` and `a=b` changed the fields received by the server. URLs
  such as `/relative` raised a PHP notice before validation.
- After: form names are URL-encoded, preserving those keys and their values. Missing schemes
  raise `bad_param` with a missing-scheme reason, without a PHP notice.
- Search consumer code for: `flat\core\curl\request`, form keys containing `&`, `=`, `+` or
  non-ASCII text, and relative or scheme-relative URLs passed to cURL requests.
- Verified: reserved-key round trips and four missing-scheme cases failed before the fixes and pass now.

### MOD-221, MOD-226 — API comparison requires complete signature metadata

- Before: the API tool missed method return-reference changes and legacy dumps did not record
  that flag. After: fresh dumps record and compare it; incomplete inputs exit 2 with instructions
  to re-dump both versions instead of assuming a value return.
- Search comparison scripts for `tests/tools/apidump.php` and regenerate their input dumps with
  the current tool. This changes test tooling, not the framework's public API.

### MOD-227 (KD-93, Q45) — Reject unsupported form field values

- Before: nested form arrays, non-stringable objects and resources reached `urlencode()` and
  raised native PHP warnings; without a strict error handler they could become empty values.
- After: the request constructor throws `flat\core\curl\exception\bad_param` for the data
  parameter before encoding an unsupported field. Scalar, null and stringable values retain
  their bytes. JSON bodies still accept nested structures.
- Search consumer code for: cURL URL-encoded request data containing arrays, non-stringable
  objects or resources, including bracketed fields in a query-string body. Supply flat values
  or deliberately use JSON for nested data.
- Verified: seven invalid-value cases raised native warnings before the fix; regressions now
  require the declared exception and preserve valid form/JSON output.

### MOD-242 (KD-95) — Mongo update counts work on MongoDB 5.1 and later

- Before: `update()` with `count` or `not_modified_exception`, and `get_affected_records()`, relied on the
  server's `getLastError` command. On MongoDB 5.1 or later (it was removed) they threw `not_modified`
  ("no update data found").
- After: the count comes from the update's own result, so they work on every MongoDB version. Answers are
  the same as before on old servers (matched records are counted, even when the update changed nothing; no
  match is still "not updatedExisting"). One difference: `get_affected_records()` now reports the last update
  made through that driver object, not whatever operation the connection ran last.
- Search consumer code for: `get_affected_records`, `not_modified_exception`, `'count'=>true` in `update` calls.
- Verified: yes (service tests on MongoDB 9.0 and 5.0; unit tests fail without the fix).


### MOD-244 (KD-96) — Default queues work on RabbitMQ 4

- Before: `publish()` with a queue name declared it non-durable and non-exclusive. RabbitMQ 4 denies that
  and closed the connection (`transient_nonexcl_queues is deprecated`), so the default did not work there.
- After: on a RabbitMQ 4 or later broker the default queue is declared durable (an explicit `durable` or
  `exclusive` is never overridden). On older brokers nothing changes. A queue that a RabbitMQ 3 broker
  already holds as non-durable is not affected by a 4.x broker; if an application moves a default queue
  from a 3.x broker to a 4.x one, its old non-durable declaration does not exist there.
- Search consumer code for: `publish(` with a queue name or `'queue'=>`, `queue_declare`, `durable`, and
  anything that redeclares a queue flat declared (a redeclaration must now use `durable` true on RabbitMQ 4).
- Verified: yes (service tests on RabbitMQ 4.4; on 3.13 the declaration stays non-durable).


### MOD-246 (KD-97) — RabbitMQ driver works with php-amqplib 3

- Before: with php-amqplib 3 installed, connecting failed (`Class 'PhpAmqpLib\Connection\AMQPConnection' not found`).
- After: it works. With php-amqplib 2 nothing changes: the connection is still an `AMQPConnection`. With 3 it is an
  `AMQPStreamConnection`. The documented return type of `get_connection()` is the common base,
  `\PhpAmqpLib\Connection\AbstractConnection`.
- Search consumer code for: `get_connection(`, `AMQPConnection` (type hints and `instanceof`; on version 3 use
  `AbstractConnection` or `AMQPStreamConnection`).
- Verified: yes (rabbitmq tests pass on php-amqplib 2.12.3 and 3.7.5; the new test fails without the fix).


### MOD-253 — Authorize.Net wrappers deprecated (no behavior change)

- Before: `flat\cloud\authorize\*` were presented as current.
- After: every class is marked `@deprecated since v1.73.1` (docblock only; flat raises no notice). Authorize.Net
  no longer supports the SDK classes they wrap, and they will be removed in v2.X. Nothing changes until then.
- Search consumer code for: `flat\cloud\authorize`, `AuthorizeNetCIM`, `AuthorizeNetARB`, `AuthorizeNetAIM_Response`.
- Verified: yes (a test fails if a wrapper class loses its tag).


### MOD-258 (KD-99) — Authorize.Net customer profiles keep their fields

- Before: `flat\cloud\authorize\customer::create()` sent an empty profile (the fields `merchantCustomerId`,
  `description` and `email` were dropped), which Authorize.Net refuses with `E00041` (seen on the real sandbox);
  `update()` changed nothing.
- After: the fields are sent. `create()` returns the new profile id; `update()` changes the fields you pass.
- Search consumer code for: `flat\cloud\authorize\customer`, `->create(`, `->update(` on a customer, `E00041`,
  and any workaround that sets the profile fields another way.
- Verified: yes (unit test fails without the fix; real-sandbox result from the owner).

### MOD-259 (KD-98) — Authorize.Net wrappers raise `no_response` when there is no usable answer

- Before: when the request to Authorize.Net failed (no connection, a timeout, an untrusted certificate, a
  non-XML reply), `customer` and `subscription` methods returned as if it had worked: `update()` and the delete
  methods returned normally, `create()` returned no id, and no exception was raised.
- After: they throw `flat\cloud\authorize\no_response` (a subclass of `unexpected_response`), whose message says
  there was no usable reply, that it may be a temporary issue, and that the request may or may not have been
  processed. An answer that is an error (`response_error`) is unchanged.
- Search consumer code for: `flat\cloud\authorize\customer`, `flat\cloud\authorize\subscription`, `unexpected_response`,
  `response_error`; add a handler (retry, or check before repeating a payment-related call).
- Verified: yes (unit tests fail without the fix; seen live on the sandbox with TLS failing).

### MOD-261 (KD-101) — A subscription object sends each call on its own

- Before: on one `flat\cloud\authorize\subscription` object, the second and later calls resent the data of every
  earlier call (a status check, update or cancel after a `create()` carried the whole create request).
- After: each call sends only its own data. A `setRefId()` set on the client applies to the next request only.
- Search consumer code for: `flat\cloud\authorize\subscription`, code that makes several calls on one object,
  `_prepare_AuthorizeNetRequest`, `setRefId`.
- Verified: yes (unit test fails without the fix).

### MOD-261 (KD-102) — `subscription::get_info()` works against the real service

- Before: always threw `unexpected_response` ("'totalNumInResultSet' field not found"); it read an element the
  response does not have.
- After: it reads the response as the schema defines it and returns the subscription's details, or throws
  `subscription\not_found`.
- Search consumer code for: `get_info(`, `unexpected_response`, `not_found`, and any lookup you built to work around it.
- Verified: yes (unit tests, and the owner's real-sandbox run of 2026-11-23). A subscription created a moment ago may
  not be in Authorize.Net's list yet: `get_info()` then throws `not_found`; try again after a few seconds.

### MOD-262 (KD-100) — Authorize.Net payment methods work

- Before: `customer::create_paymethod()` and `update_paymethod()` (and a `payment` given to `create()`) could not
  work: an array raised a PHP `Error` ("Cannot instantiate abstract class ...payment_profile"), and a
  `payment_profile` object produced invalid XML that Authorize.Net refuses. Without a `payment` they sent an
  empty payment profile.
- After: they build valid requests (see the docblock of `create_paymethod()` for the shapes). Unknown fields, both
  payment types, an empty profile and a missing `payment` throw `bad_param` or `missing_required_param` before any
  request. The returned id and the other exceptions are unchanged.
- Search consumer code for: `create_paymethod`, `update_paymethod`, `payment_profile` (a class of your own that extends
  it still works), `customer\payment_profile\creditCard`, and any workaround that calls the SDK directly.
- Verified: yes (unit tests, and the owner's real-sandbox run of 2026-11-23: add, change, remove).

### MOD-263 (KD-103) — `customer::create()` works without a payment

- Before: `create()` without a `payment` always sent the `liveMode` validation mode (the default), which
  Authorize.Net refuses for a profile without payment profiles (`E00013`).
- After: without a `payment` the mode sent is `none`, whatever `validate` says. With a `payment` nothing changes.
- Search consumer code for: `customer`, `->create(` without `payment`, `validate`, `validationMode`, `E00013`.
- Verified: yes (unit test fails without the fix; the error is the owner's real-sandbox result).

### MOD-265 (KD-105) — Authorize.Net customer transactions send valid requests

- Before: `customer::create_transaction()` sent a request with an empty element name and an extra `<type>` field,
  which Authorize.Net refuses; and for `authonly` and `captureonly` the amount and the ids were dropped (those
  classes had no fields).
- After: each kind sends its own valid request. `authonly` takes the fields of `authcapture`; `captureonly` takes the
  same plus `approvalCode`. A transaction object of another kind than the one named still throws `bad_param`.
- Search consumer code for: `create_transaction`, `customer\transaction\authonly`, `customer\transaction\captureonly`,
  `authcapture`, and any workaround that builds the SDK's `AuthorizeNetTransaction` itself.
- Verified: unit tests (fail without the fix); the real-sandbox result is the owner's next run.

### MOD-270 (KD-106) — Capture Only is removed

- Before: `customer\transaction\captureonly`, `customer::captureonly()` (it always threw `BadMethodCallException`) and
  `captureonly` in `create_transaction()` existed. Authorize.Net refuses the transaction type (`E00027`: it has
  withdrawn the method), so a request could never succeed.
- After: they are gone. `create_transaction()` with `transactionType` `captureonly` throws `bad_param`, and with the
  `captureonly` key throws `missing_required_param`. The class cannot be loaded and the method does not exist.
- Search consumer code for: `captureonly`, `CaptureOnly`, `approvalCode`, `profileTransCaptureOnly`. Use `authcapture`,
  or `authonly` followed by a capture, instead.
- Verified: yes (unit test; the sandbox's refusal is the owner's run of 2026-11-23).

### MOD-276 — forceutf8 updated from 2.0 to 2.0.4

- Before: flat bundled an old copy of forceutf8 2.0 that used the `$text{$i}` string-offset syntax (deprecated in PHP
  7.4, removed in 8.0) and loose comparisons.
- After: upstream 2.0.4, with only the namespace changed. Conversions give the same results for everything the tests
  cover. Two differences: `fixUTF8()` of a number, boolean or null now returns it unchanged (before, `42` became
  `'42'`, and `null` and `false` became `''`); and `toISO8859()` and `toLatin1()` now accept the iconv option, as
  `toWin1252()` already did.
- Search consumer code for: `flat\core\util\utf8`, `forceutf8\Encoding`, `fixUTF8`, `toISO8859`, `toLatin1`.
- Verified: yes (the new tests fail on the old file).

### MOD-277 — Markdown: Parsedown 1.8.0 and ParsedownExtra 0.9.0 (security fix, rendering changes)

- Before: `flat\core\md\convert` rendered with Parsedown 1.5.0 (2015) and ParsedownExtra 0.6.x. One of its flaws: an
  image whose address contains a quote broke out of the attribute, so `![a](../active/x"onerror="alert(1))` produced
  `<img src="x"onerror="alert(1)" ...>` (attribute injection).
- After: Parsedown 1.8.0 and ParsedownExtra 0.9.0. The injection is fixed (the quote is escaped). In a set of 55
  typical documents, 40 render identically; the rest differ as the newer parsers follow the Markdown rules more
  closely:
  - an ordered list starts at its first number (`<ol start="3">`), including a line such as `2015. A great year`;
  - a different bullet (`*`, `+`, `-`) starts a new list, and a blank line between quotes makes two quotes;
  - in a loose list every item is a paragraph; nested lists differ in whitespace (`<li>b<ul>`, no newline);
  - a fenced block's first word is its language (`language-php`), and `{.class}` attributes work on it;
  - quotes in text are not entity-encoded (`"` was `&quot;`); a lone `*` is an empty list; `a || b` rows with an
    empty cell are no longer a table.
- Raw HTML is no longer passed through by default: see MOD-279 below.
- Search consumer code for: `flat\core\md\convert`, `string_to_html`, `parsedown\extra`, and stored or cached
  rendered HTML or tests that compare it; re-render and compare.
- Verified: yes (the new tests fail on the old parser).

### MOD-278 — JavaScript minifier: JShrink 1.8.1 (modern JavaScript no longer corrupted)

- Before: flat bundled JShrink 1.1.0 (2013) with local fixes. It corrupted JavaScript written after 2015: inside a
  template literal it dropped spaces (`` `n is ${n} and ${n + 1}` `` came out as `` `n is ${n}and ${n+1}` ``), cut a
  template that contains `//`, and collapsed its newlines; and `//@cc_on` followed by code came out as
  `//@cc_onvar x=1;`, which comments the code out.
- After: JShrink 1.8.1 with flat's fixes re-applied. In 49 test programs (arrow functions, template literals, `?.`,
  `??`, classes, generators, regex edge cases and more) the minified program gives the same result as the original,
  checked by running both in Node; flat's old copy broke 4 of them. The output text changes a little: `return/x/`
  (no space), `0??9`, and the IE-only `//@cc_on` comment is dropped.
- Search consumer code for: `JShrink`, `minifier::minify`, `flat\deploy\minify`, and any stored minified assets
  (re-minify them; the bytes differ, the behavior is the same or fixed).
- Verified: yes (the new tests fail on the old copy; Node runs 45 programs before and after).

### MOD-279 — Markdown: raw HTML and unsafe addresses are escaped by default

- Before: `flat\core\md\convert` let raw HTML in the Markdown through untouched, so `<script>alert(1)</script>`,
  `<iframe>` or `<b onclick="...">` in user text reached the page, and `[x](javascript:alert(1))` kept its address.
- After: by default the text is treated as untrusted (Parsedown's safe mode). Raw HTML is shown as text
  (`&lt;script&gt;`), `javascript:`, `data:` and `vbscript:` addresses are removed from links and images, and `on*`
  attributes are dropped. Ordinary Markdown and Markdown Extra (tables, footnotes, `{#id .class}`) render as before.
  Typed character entities are shown literally (`&copy;` appears as the text `&copy;`).
- To let raw HTML through for text you wrote yourself, pass `['safe' => false]`:
  `convert::string_to_html($text, ['safe' => false])`, `new convert($text, ['safe' => false])`,
  `new convert\file($path, ['safe' => false])`. An unknown option throws `InvalidArgumentException`.
- Not changed: flat's template controller still renders `.md`, `.txt`, `.md.php` and `.txt.php` templates with raw
  HTML allowed, because they are written by the developer.
- Search consumer code for: `flat\core\md\convert`, `string_to_html`, `convert\file`, and Markdown that holds HTML
  you want kept (add `['safe' => false]` if the text is trusted).
- Verified: yes (nine of the new tests fail on the old converter).

## No behavior change

- MOD-189 (environment) changes no behavior a caller can see.
- One thing consumers can notice, which is not a flat change: the root `composer.json` now
  requires `php >=7.3.0` (it was `>=7.2.1`). A PHP 7.2 host keeps installing `v1.72.x`; Composer
  skips `v1.73.0` for it.
