# Consumer impact review of approved behavior changes

Re-examination of the behavior changes made on the PHP 7.2 baseline (MOD-014
through MOD-107), sorted by what they mean for applications that already depend
on flat. The main consumer is closed source and not currently available, so
this is a checklist to run against it later, not a sign-off.

## How changes are classified

- **Silent change**: code that previously ran without error now returns a
  different result or has a different side effect. A consumer may have relied
  on, or compensated for, the old result. These need checking in consumer code.
- **Crash to working**: the old path always raised a fatal `Error`, `TypeError`
  or missing-class failure, so no running consumer could have depended on its
  result. Low risk, unless a consumer caught the failure and carried on.

"Verified" means the before/after was confirmed in the `master` diff during this
review; otherwise the summary comes from the change log entry.

## Silent changes: check consumer code for these

### MOD-026 — Collection iteration keys

- Before: `key()` searched the index for the member object, so keys were always `false` during `foreach`; `$out[$k] = $v` collapsed to one entry
- After: Named members yield their key, unkeyed members their position
- Search consumer code for: `foreach` over `flat\core\collection` objects using `$k =>`; `->key()`
- Verified: yes

### MOD-026 — Collection `range($limit, $start)`

- Before: `$limit` acted as an exclusive end position, so `range(2, 1)` returned 1 member
- After: `$limit` is a member count, so `range(2, 1)` returns 2; negative start is clamped before the range check
- Search consumer code for: `->range(` with a nonzero start, i.e. pagination
- Verified: yes

### MOD-022 — Collection copies

- Before: Falsy copied data became `null`
- After: Falsy values (`0`, `''`, `false`) are preserved; index zero survives
- Search consumer code for: `load_copy(`, `set_member(`
- Verified: changelog

### MOD-023 — Collection constraints/validators

- Before: Declared class/interface constraints and validator hooks were not enforced; invalid members were accepted
- After: Invalid members now fail; validation/filter/ignore/success hooks run
- Search consumer code for: Collection subclasses declaring constraints or validator interfaces
- Verified: changelog

### MOD-019 — XML encoding format

- Before: Flat XML without type/key metadata; decoder errored
- After: Version 0.3 adds type/key attributes and a working decoder. **Wire format change**
- Search consumer code for: `flat\core\xml\encode`; stored XML documents; any other system that parses flat XML output
- Verified: changelog

### MOD-016 — Data mapper `strings_to_scalar(true)`

- Before: Partially numeric text (e.g. `"12abc"`) was converted as a number
- After: Only `is_numeric` strings convert
- Search consumer code for: Mapping of request/form input through `flat\data\mapper`
- Verified: changelog

### MOD-017 — Mapping defaults, scalar fallbacks

- Before: `defaults`/`default_on_empty` were ignored; empty scalar wrappers exposed `null`
- After: Defaults apply; empty wrappers expose `value_on_empty`
- Search consumer code for: `default_on_empty`, `value_on_empty`, mapper `defaults`
- Verified: changelog

### MOD-014 — `require_scalar` validation

- Before: Returned `null` for every input
- After: Returns scalar inputs and honors fallback options
- Search consumer code for: `require_scalar(`
- Verified: changelog

### MOD-030 — HTTP JSON requests

- Before: Sent `Content-Type: application\json` (backslash); falsy JSON data (`[]`, `0`, `false`) sent no body
- After: Sends `application/json`; falsy JSON data is encoded and sent
- Search consumer code for: `new \flat\core\curl\request(` with `"json"`; remote APIs that received these requests
- Verified: yes

### MOD-088 — Shutdown callbacks

- Before: Callbacks registered at an already-used integer priority could be overwritten and never run
- After: Every callback runs, newest first among equal priorities
- Search consumer code for: `shutdown::register(` with integer priorities
- Verified: changelog

### MOD-045 — Image minimum-size resize

- Before: Guard assigned width instead of comparing; resize math differed
- After: Proper comparison and proportional sizing; output dimensions can differ
- Search consumer code for: `resize_min_proportional`, `flat\core\util\image` with width/height bounds
- Verified: yes

### MOD-106 — Mongo eager connections

- Before: `connect=true` clients were not actively checked
- After: New and stale eager clients are pinged and retried (default 5 attempts, honors `connect_attempts`); failures surface at connect time
- Search consumer code for: Mongo configs with `connect` true; startup latency/timeouts
- Verified: changelog

### MOD-033 — PDO `prepare_map`

- Before: Depended on SELECT `rowCount`, so drivers reporting 0 (e.g. SQLite) returned `[]`
- After: Rows are fetched directly
- Search consumer code for: PDO use on drivers other than MySQL
- Verified: changelog

### MOD-094 — Asset directory paths

- Before: A directory request could enter the file-only branch because both flags share a bit
- After: Full flag mask required
- Search consumer code for: Asset system-path lookups for directories
- Verified: changelog

## Changed failure types (still failures)

### MOD-107 — `alert::trigger()`

- Before: Registered the `listener` callback, then always raised `Error` (publish on an unassigned driver)
- After: Throws `BadMethodCallException` immediately; no listener is registered. Code catching `\Error` no longer catches it (verified)

### MOD-067 — Mongo collection-name setters

- Before: Called a nonexistent method (fatal)
- After: Throw the documented `illegal` exception

## Crash to working (low risk)

These paths previously failed on every call, so no consumer could rely on
their results: SSH password/pubkey authentication (branches were inverted and
threw nonexistent `\sf\...` classes; verified), SFTP download (`safe_feof` did
not exist, MOD-105), S3 multipart upload (removed AWS SDK v2 classes, MOD-085),
subscription timezone conversion (unqualified `DateTimeZone`, MOD-093), XPath
node lists (MOD-018), routing-controller subclass caches (MOD-024), API header
getters (MOD-036), template paths and Markdown execution (MOD-032/040), RabbitMQ
first connection and queue parameters (MOD-056), payment transaction/CIM/
subscription loading and identifiers (MOD-089/090/096), validation helpers and
exception declarations (MOD-047/051/071/072/083/087/097), and the legacy ORM
session fallback (MOD-100). A consumer that wrapped any of these in a broad
`catch` and continued would now see them succeed.

## Known-defect fixes (from `KNOWN-DEFECTS.md`)

Every known-defect fix changes behavior a consumer might have relied on.

### MOD-112 (KD-16) — Shutdown callbacks

- Before: Calling `shutdown::invoke_shutdown_callbacks()` explicitly ran every callback, and PHP shutdown then ran them all again
- After: Each callback runs at most once; the queues empty on invocation, and callbacks registered later still run at PHP shutdown
- Search consumer code for: `invoke_shutdown_callbacks(`

### MOD-113 (KD-28) — Core mapper

- Before: Reading a non-public property with `get_private_property` or `get_as_assoc`/`get_as_stdClass(..., true)` raised a PHP `Error` (misspelled reflection call)
- After: Raises `flat\core\mappable\exception\cannot_get_property` ("... access is not supported"). The flag on public-only objects, and `set_private_property`, are unchanged
- Search consumer code for: `get_private_property`, `get_as_assoc(` / `get_as_stdClass(` with a second argument `true`

### MOD-114 (KD-45/46) — Payment conveniences, IP lookup

- Before: `void_transaction`, `refund_transaction`, `authonly`, `authcapture`, `captureonly`, `get_transaction` (customer and subscription) and `ipinfodb::load` did nothing and returned `null`
- After: Throw `BadMethodCallException`; the message names `create_transaction()` or the constructor where one exists
- Search consumer code for: Those method names; code checking their result for `null`

### MOD-124 (KD-10) — API `not_found` status

- Before: `get_status_code()` returned 500
- After: Returns 404
- Search consumer code for: `api\not_found`; clients or logs keyed on 500 for missing resources

### MOD-124 (KD-06) — API response exception

- Before: `new response\exception(null)` called from inside a class raised a PHP `Error` (`getClassName`)
- After: Builds the generic error response, naming the calling class
- Search consumer code for: `response\exception(` with an empty argument

### MOD-124 (KD-27) — CLI arguments

- Before: Missing `$argv` raised a PHP `Error` (class `flat\core\RuntimeException` not found)
- After: Raises `\RuntimeException`
- Search consumer code for: `catch (\Error` around `cli::` calls

### MOD-124 (KD-31) — cURL response

- Before: Missing transfer info raised a PHP `Error` (empty exception file)
- After: Raises `flat\core\curl\exception\missing_curlinfo`
- Search consumer code for: Catches around `curl\response`

### MOD-124 (KD-11, 47, 48, 49) — Exception messages

- Before: Asset "could resolve"; `bad_interface` dropped the class and reason when a method was given; SSH `not_ready`/`pubkey` replaced their text with ": detail"; `duplicate_key` warned on array indexes and ended default messages with ", "
- After: "could not resolve"; full class, method and reason; prefix kept; no warning, "(non-scalar index)", no trailing ", "
- Search consumer code for: Code or log parsing that matches these exact messages

### MOD-126 (KD-25) — Collection callbacks

- Before: The data validator and filter ran twice per `add()`: a filter adding 1 added 2
- After: Each runs once
- Search consumer code for: `set_data_validator(`, `set_data_filter(`

### MOD-126 (KD-36, 65) — Data HTML encoding

- Before: `webview` data and `dump(..., ['htmlencode'])` were not actually encoded
- After: Fields are HTML-encoded on construction for `webview` data; the dump is encoded
- Search consumer code for: `implements \flat\data\webview`; `dump(` with `htmlencode`; code that encoded these values again

### MOD-126 (KD-37, 69) — Data field lists

- Before: `field_list($instance)` raised a PHP `Error`; without an instance, every class after the first got the first class's fields, so `is_field()` could be wrong
- After: Each class gets its own fields
- Search consumer code for: `field_list(`, `is_field(`

### MOD-126 (KD-24) — HTML form encoding

- Before: `form::serialize()` returned `null`
- After: Returns the form HTML
- Search consumer code for: `html\encode\form::serialize(`

### MOD-126 (KD-26) — Map from object

- Before: Mapping an object or array raised `ArgumentCountError`
- After: Maps onto the subclass's declared properties; undeclared names raise `cannot_set_property`
- Search consumer code for: `util\map\from_object`

### MOD-126 (KD-38) — Validation failure

- Before: The message argument was discarded
- After: `get_message()` and `getMessage()` return it
- Search consumer code for: `validate_failure(` with a message

### MOD-126 (KD-17, 18) — Legacy events

- Before: `hookable` passed no data to its handler (with notices); `stub::get_listeners()` returned nothing
- After: The handler receives the event data; the added listeners are returned
- Search consumer code for: `event\hookable`, `event\stub`

### MOD-127 (KD-07) — API request method

- Before: `_get_request_method()` always threw `missing_method`
- After: Returns the method `set_input()` dispatched (`GET` for a `HEAD` request)
- Search consumer code for: API subclasses calling `_get_request_method()`

### MOD-127 (KD-08) — API missing method

- Before: No method and no fallback raised `unknown_method`
- After: Raises `missing_method`
- Search consumer code for: Catches of `unknown_method` around `set_input(`

### MOD-127 (KD-58) — API `HEAD` flag

- Before: Once a controller served a `HEAD` request, `_is_HEAD_request()` stayed true for every later request
- After: Reset per request
- Search consumer code for: `_is_HEAD_request()` on reused controllers

### MOD-127 (KD-59) — API `TRACE`

- Before: Echoed an empty object (input fields were lost)
- After: Echoes the input's fields
- Search consumer code for: `TRACE` requests

### MOD-127 (KD-09) — API status formatting

- Before: `%class-type%`/`%class-description%` produced warnings and wrong text; `get_code_class_type()`/`_description()` returned literal placeholders
- After: Filled from the RFC 7231 class table
- Search consumer code for: `status_code_class_format(`, `format_code(`, `get_code_class_type(`

### MOD-127 (KD-57) — View resolution

- Before: The `view_resolved` event's `resolved` was always `\flat\design` (with notices)
- After: The matched design class path, e.g. `\flat\design\sample\page`
- Search consumer code for: `view_resolved` listeners

### MOD-129 (KD-15) — Shutdown weights

- Before: Weight `0` joined the default queue; weights outside -50 to 50 were silently queued as default
- After: `0` is ordered among the weights; out-of-range integers throw `bad_priority`
- Search consumer code for: `shutdown::register(` with integer priorities

### MOD-129 (KD-52) — UUID versions

- Before: `uuid::get()` with an invalid version and no namespace returned a v4 UUID
- After: Throws `invalid_version` (versions 3, 4 and 5 still fall back to v4 without a namespace)
- Search consumer code for: `uuid::get(` with a computed version

### MOD-129 (KD-55) — Random strings

- Before: Invoking a `rand\str` instance (`$generator()`) ignored its configured pool and length
- After: Uses the instance's pool and length; `$generator(5)` changes only the length; options still build a new pool
- Search consumer code for: `rand\str` objects called as functions

### MOD-130 (KD-13) — Asset HTML output

- Before: Several items in one `register_html_special()`/`register_html([...])` call kept only the first and made the asset depend on itself
- After: All items print, in the order given; no self-dependency (also when `register_html()` gets `resolved`)
- Search consumer code for: `register_html_special(` with several items, `register_html(` with arrays

### MOD-130 (KD-12) — Inline asset source

- Before: `print_contents()` on an inline JavaScript source raised `TypeError`
- After: Prints the `<script>` with the source
- Search consumer code for: `RESOURCE_JS_SOURCE` assets

### MOD-130 (KD-14) — Asset printing

- Before: `print_contents()` of an unprintable type and `print_tag(TAG_STYLE_SOURCE)` printed nothing; inline-source printing of a file asset printed an empty `<script></script>`
- After: These explicit requests throw `app_error`; `print_tag()` without a tag type still prints nothing for assets with no tag form
- Search consumer code for: `print_contents(`, `print_tag(` with explicit types

### MOD-131 (KD-50) — Phone extensions

- Before: `+1 800 555 1234` stored the whole number as its extension (`x8005551234`); `ext 42` and `x89` extracted digits of the main number
- After: `+1` numbers have no extension unless digits follow the 10-digit number; text extensions are read from after the marker
- Search consumer code for: `flat\data\phone` `extension` and string output; stored phone strings

### MOD-131 (KD-51) — Phone country codes

- Before: Most international numbers had no `country_code`; `e164` read `+` plus the whole number
- After: Country code set from the table in `data/phone.php`; `e164`/`e164_ndc`/`e164_intl` split accordingly
- Search consumer code for: `country_code`, `e164*` of non-NANP numbers

### MOD-132 (KD-32) — cURL Chrome user agent

- Before: Selecting `config::user_agent_chrome` sent the user agent `0`
- After: Sends the Chrome user-agent string
- Search consumer code for: `user_agent_chrome`

### MOD-132 (KD-30) — Debug kill exit status

- Before: `debug::kill($msg, ['exit_status'=>N])` ignored N (only a nested form worked) and the fallback exit read undefined variables
- After: The plain form sets the exit status; the nested form still works
- Search consumer code for: `debug::kill(`

### MOD-132 (KD-29) — Debug value descriptions

- Before: Arrays read `{object class: %class%}`; numbers and booleans kept raw placeholders; `"0"` read as `{empty string}`
- After: `{array count: N elements}`, `{integer} 42`, `{boolean} false`, `0`
- Search consumer code for: Logs or tests matching `debug::data2str(` output

### MOD-132 (KD-53) — Template output buffers

- Before: A failing generated template, or a failure inside `tmpl::format()`, left an output buffer open, swallowing later output
- After: Buffers are closed on failure
- Search consumer code for: Code relying on output after a template failure

### MOD-133 (KD-01, 02, 03) — PDO statements

- Before: `fetch` options all threw `bad_option`; `execute` without a callback raised a PHP `Error`; drivers reporting `rowCount()` 0 for SELECT (SQLite) threw `not_found` even with rows
- After: `fetch` true/class/callable add or pass each row, `false` executes without fetching; plain `execute` adds rows as `flat\data\generic`; `not_found` means no rows were fetched
- Search consumer code for: `pdo\statement`, `'fetch'`, `'execute'`, `not_found` handling

### MOD-133 (KD-04) — Mongo CA file

- Before: A failed certificate write returned `null`
- After: Throws `app_error` ("could not write CA file")
- Search consumer code for: `ssl\cafile::get_file(`

### MOD-133 (KD-05) — DB controller `load_read`

- Before: Always returned `null` with an "undefined constant self" warning
- After: Classes implementing `\flat\db\op\read` return `op_read()` (when defined) or `read($param)`
- Search consumer code for: `::load_read(`

### MOD-133 (KD-33, 34) — Legacy data ORM

- Before: `orm::get_data()` and `get()` returned nothing; a constructor array raised a PHP `Error`
- After: They return the stored data (`set()` returns the driver's result); the array is driver configuration (`'driver'` class or object, the rest passed on); an invalid driver throws `bad_param`
- Search consumer code for: `flat\data\orm`

### MOD-133 (KD-35) — Session ORM destroy

- Before: Ended the session, then warned while regenerating the id
- After: Regenerates (removing the old data), clears `$_SESSION` and ends the session, without warnings
- Search consumer code for: `handler\native::destroy(`

### MOD-133 (KD-60, 61) — ORM objects

- Before: `delete()` did nothing on an object that had been read; `crud_error::get_op()` returned the details
- After: Read objects can be deleted (once); `get_op()` returns the operation
- Search consumer code for: `flat\orm` `delete(`, `crud_error`

### MOD-133 (KD-62) — Mongo client

- Before: `load_client()` without options warned about `foreach` on `null`
- After: No warning
- Search consumer code for: `mongo::load_client(`

### MOD-134 (KD-21) — Validation options

- Before: `value_on_pass`, `value_on_fail`, `fail_handler` and `exception_on_fail` were ignored: success returned the value, failure `null`
- After: All four apply; on failure `fail_handler` wins, then `exception_on_fail`, then `value_on_fail`
- Search consumer code for: `::validate(` with options

### MOD-134 (KD-22) — Regex validation

- Before: A PCRE engine error (e.g. invalid UTF-8) returned `false` like a mismatch
- After: Throws `preg_error`
- Search consumer code for: `match\regexp` validation of untrusted text

### MOD-134 (KD-20) — Validation factory

- Before: The default constraint rejected the pairs `add_validate()` builds
- After: Accepts them; other members are rejected
- Search consumer code for: `util\validate\factory` subclasses

### MOD-134 (KD-68) — Validation controller

- Before: Constructed and did nothing
- After: Throws `BadMethodCallException` once its arguments are valid
- Search consumer code for: `core\controller\validate`

### MOD-134 (KD-23) — Image constructor

- Before: Width and height together left the image unchanged
- After: Both are proportional minimums (like `resize_min_proportional`)
- Search consumer code for: `new image($source, $w, $h)` with both

### MOD-134 (KD-19) — Yasumi date queries

- Before: `get_date()`/`get_year()` were `null` for valid input; a cached provider was not attached; `isHoliday()`/`isWorkingDay()` threw; locale ignored; the epoch counted as unparseable
- After: Timestamp and year are set; providers are shared per provider, year and locale; holiday queries work; locale passed to Yasumi
- Search consumer code for: `azuyalabs\yasumi\dateQuery`

### MOD-135 (KD-67) — **SSH private keys (security)**

- Before: The temporary private key written for ssh2 was **unencrypted** (`openssl rsa` ignores `-passout` without a cipher), and failures left temporary key files behind
- After: Written encrypted with AES-256 under the random passphrase; every failure removes the temporary files
- Search consumer code for: Hosts that ran `flat\core\ssh` public-key logins: consider rotating keys and clearing stray `sfnpriv*` files in the temp directory

### MOD-135 (KD-39) — Payment transactions

- Before: The transaction adapter dropped every SDK field initialized to `null`, **including `amount`**
- After: All declared fields are mapped
- Search consumer code for: `customer\transaction\*\AuthorizeNetTransaction`; transactions sent without an amount

### MOD-135 (KD-44) — Payment transaction validation

- Before: Any validation result except `false` or a string passed (e.g. `null`)
- After: Only `true` passes
- Search consumer code for: `_validate_transaction_type(` implementations

### MOD-135 (KD-40, 41) — Subscription create

- Before: `billTo`/`shipTo`/schedule fields initialized to `null` were dropped; `create()` returned `null`
- After: Fields are mapped; `create()` returns the new subscription id
- Search consumer code for: `subscription::create(`

### MOD-135 (KD-42) — Subscription search

- Before: Every page request asked for page 1, so a subscription beyond the first 1,000 was never found
- After: Pages advance
- Search consumer code for: `subscription::get_info(`

### MOD-135 (KD-43) — Payment response errors

- Before: `response_error($code, $text)` ignored the text
- After: Uses it as the message text
- Search consumer code for: `response_error(` with two arguments; logs matching its message

### MOD-135 (KD-66) — Payment API exceptions

- Before: A failing SDK call threw an `\Exception` whose message was a `print_r` of the exception (could exhaust memory)
- After: Message is `Class: message`; the original is `getPrevious()`
- Search consumer code for: Code parsing that message

### MOD-135 (KD-63, 64) — S3 uploads

- Before: Files from 5 GB to 10 GiB went to a single upload, which S3 rejects; multipart uploads lost the content type
- After: Files of 100 MB or more use multipart; the content type is kept
- Search consumer code for: `s3::file_upload(` with large files or a content type

### MOD-136 (KD-70) — cURL request defaults

- Before: Requests without an explicit user agent sent none (the `flat/curl` default was replaced with `null`); `referrer`/`cache_dir` defaults were likewise lost
- After: Default user agent `flat/curl` is sent; other text defaults are kept; the request method is still unset unless given (curl sends GET, or POST with a body)
- Search consumer code for: `curl\config` / `curl::exec(` without a user agent; servers filtering on User-Agent

### MOD-137 (KD-71) — UUID objects

- Before: `$uuid(3, $namespace, $name)` and `$uuid(5, ...)` (calling the object) ignored their arguments and returned a random v4 UUID
- After: Returns the requested version (v3/v5 are deterministic); `$uuid()` is still a random v4
- Search consumer code for: UUID objects used as callables: `$u(` with arguments

### MOD-147 (KD-77) — XML hexBinary labels

- Before: Any string of hex digits was labeled `xs:hexBinary`, including odd lengths such as `a`, `abc` or `cafe0`
- After: Only an even number of hex digits (`ab`, `deadbeef`) is `xs:hexBinary`; odd-length strings are `xs:string`
- Search consumer code for: readers of XML from `flat\core\xml\encode` that look at `xsi:type="xs:hexBinary"`, or that decode such values as bytes

### MOD-146 (KD-76) — XML date labels

- Before: Any string `strtotime()` could parse was labeled `xs:DateTime`: words (`tomorrow`, `mon`), single letters (`z`), `1/2/2020`, `12 January 2020`
- After: Only ISO-8601 style dates and times (`2020-01-01`, `2020-01-01 10:00`, `2020-01-01T10:00:00Z`, with fractions and offsets) are `xs:DateTime`; other text is `xs:string` (or its other detected type)
- Search consumer code for: readers of XML from `flat\core\xml\encode` that look at `xsi:type="xs:DateTime"`, or that parse non-ISO dates from it

### MOD-140 (KD-72) — Random numbers

- Before: `$rand()` on an object built with bounds (`new rand(5, 10)`) ignored them and returned 0 to `getrandmax()`
- After: Without arguments it uses the constructed bounds, like `generate()`; explicit arguments still win
- Search consumer code for: `new rand(` followed by a call of the object, `$rand()`

### MOD-139 (KD-73) — Config keys

- Before: A key starting with `flat/` (e.g. `config::get('flat/data/orm/driver')`) was cut to a nonsense key and returned the default or null
- After: The `flat/` prefix is removed, so the key resolves to the real setting
- Search consumer code for: `config::get(`, `enum_values(` with a first segment `flat`: those calls may now return configured values where they used to return defaults

## Suggested consumer check

When the consumer source is available, search it for the terms in the silent
change table. For each hit, decide whether the consumer used the old result as
is, compensated for it, or never reached it. Record the outcome here, per entry.
