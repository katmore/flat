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

| Entry | Area | Before | After | Search consumer code for | Verified |
| --- | --- | --- | --- | --- | --- |
| MOD-026 | Collection iteration keys | `key()` searched the index for the member object, so keys were always `false` during `foreach`; `$out[$k] = $v` collapsed to one entry | Named members yield their key, unkeyed members their position | `foreach` over `flat\core\collection` objects using `$k =>`; `->key()` | yes |
| MOD-026 | Collection `range($limit, $start)` | `$limit` acted as an exclusive end position, so `range(2, 1)` returned 1 member | `$limit` is a member count, so `range(2, 1)` returns 2; negative start is clamped before the range check | `->range(` with a nonzero start, i.e. pagination | yes |
| MOD-022 | Collection copies | Falsy copied data became `null` | Falsy values (`0`, `''`, `false`) are preserved; index zero survives | `load_copy(`, `set_member(` | changelog |
| MOD-023 | Collection constraints/validators | Declared class/interface constraints and validator hooks were not enforced; invalid members were accepted | Invalid members now fail; validation/filter/ignore/success hooks run | Collection subclasses declaring constraints or validator interfaces | changelog |
| MOD-019 | XML encoding format | Flat XML without type/key metadata; decoder errored | Version 0.3 adds type/key attributes and a working decoder. **Wire format change** | `flat\core\xml\encode`; stored XML documents; any other system that parses flat XML output | changelog |
| MOD-016 | Data mapper `strings_to_scalar(true)` | Partially numeric text (e.g. `"12abc"`) was converted as a number | Only `is_numeric` strings convert | Mapping of request/form input through `flat\data\mapper` | changelog |
| MOD-017 | Mapping defaults, scalar fallbacks | `defaults`/`default_on_empty` were ignored; empty scalar wrappers exposed `null` | Defaults apply; empty wrappers expose `value_on_empty` | `default_on_empty`, `value_on_empty`, mapper `defaults` | changelog |
| MOD-014 | `require_scalar` validation | Returned `null` for every input | Returns scalar inputs and honors fallback options | `require_scalar(` | changelog |
| MOD-030 | HTTP JSON requests | Sent `Content-Type: application\json` (backslash); falsy JSON data (`[]`, `0`, `false`) sent no body | Sends `application/json`; falsy JSON data is encoded and sent | `new \flat\core\curl\request(` with `"json"`; remote APIs that received these requests | yes |
| MOD-088 | Shutdown callbacks | Callbacks registered at an already-used integer priority could be overwritten and never run | Every callback runs, newest first among equal priorities | `shutdown::register(` with integer priorities | changelog |
| MOD-045 | Image minimum-size resize | Guard assigned width instead of comparing; resize math differed | Proper comparison and proportional sizing; output dimensions can differ | `resize_min_proportional`, `flat\core\util\image` with width/height bounds | yes |
| MOD-106 | Mongo eager connections | `connect=true` clients were not actively checked | New and stale eager clients are pinged and retried (default 5 attempts, honors `connect_attempts`); failures surface at connect time | Mongo configs with `connect` true; startup latency/timeouts | changelog |
| MOD-033 | PDO `prepare_map` | Depended on SELECT `rowCount`, so drivers reporting 0 (e.g. SQLite) returned `[]` | Rows are fetched directly | PDO use on drivers other than MySQL | changelog |
| MOD-094 | Asset directory paths | A directory request could enter the file-only branch because both flags share a bit | Full flag mask required | Asset system-path lookups for directories | changelog |

## Changed failure types (still failures)

| Entry | Area | Before | After |
| --- | --- | --- | --- |
| MOD-107 | `alert::trigger()` | Registered the `listener` callback, then always raised `Error` (publish on an unassigned driver) | Throws `BadMethodCallException` immediately; no listener is registered. Code catching `\Error` no longer catches it (verified) |
| MOD-067 | Mongo collection-name setters | Called a nonexistent method (fatal) | Throw the documented `illegal` exception |

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

| Entry | Area | Before | After | Search consumer code for |
| --- | --- | --- | --- | --- |
| MOD-112 (KD-16) | Shutdown callbacks | Calling `shutdown::invoke_shutdown_callbacks()` explicitly ran every callback, and PHP shutdown then ran them all again | Each callback runs at most once; the queues empty on invocation, and callbacks registered later still run at PHP shutdown | `invoke_shutdown_callbacks(` |
| MOD-113 (KD-28) | Core mapper | Reading a non-public property with `get_private_property` or `get_as_assoc`/`get_as_stdClass(..., true)` raised a PHP `Error` (misspelled reflection call) | Raises `flat\core\mappable\exception\cannot_get_property` ("... access is not supported"). The flag on public-only objects, and `set_private_property`, are unchanged | `get_private_property`, `get_as_assoc(` / `get_as_stdClass(` with a second argument `true` |
| MOD-114 (KD-45/46) | Payment conveniences, IP lookup | `void_transaction`, `refund_transaction`, `authonly`, `authcapture`, `captureonly`, `get_transaction` (customer and subscription) and `ipinfodb::load` did nothing and returned `null` | Throw `BadMethodCallException`; the message names `create_transaction()` or the constructor where one exists | Those method names; code checking their result for `null` |
| MOD-124 (KD-10) | API `not_found` status | `get_status_code()` returned 500 | Returns 404 | `api\not_found`; clients or logs keyed on 500 for missing resources |
| MOD-124 (KD-06) | API response exception | `new response\exception(null)` called from inside a class raised a PHP `Error` (`getClassName`) | Builds the generic error response, naming the calling class | `response\exception(` with an empty argument |
| MOD-124 (KD-27) | CLI arguments | Missing `$argv` raised a PHP `Error` (class `flat\core\RuntimeException` not found) | Raises `\RuntimeException` | `catch (\Error` around `cli::` calls |
| MOD-124 (KD-31) | cURL response | Missing transfer info raised a PHP `Error` (empty exception file) | Raises `flat\core\curl\exception\missing_curlinfo` | Catches around `curl\response` |
| MOD-124 (KD-11, 47, 48, 49) | Exception messages | Asset "could resolve"; `bad_interface` dropped the class and reason when a method was given; SSH `not_ready`/`pubkey` replaced their text with ": detail"; `duplicate_key` warned on array indexes and ended default messages with ", " | "could not resolve"; full class, method and reason; prefix kept; no warning, "(non-scalar index)", no trailing ", " | Code or log parsing that matches these exact messages |
| MOD-126 (KD-25) | Collection callbacks | The data validator and filter ran twice per `add()`: a filter adding 1 added 2 | Each runs once | `set_data_validator(`, `set_data_filter(` |
| MOD-126 (KD-36, 65) | Data HTML encoding | `webview` data and `dump(..., ['htmlencode'])` were not actually encoded | Fields are HTML-encoded on construction for `webview` data; the dump is encoded | `implements \flat\data\webview`; `dump(` with `htmlencode`; code that encoded these values again |
| MOD-126 (KD-37, 69) | Data field lists | `field_list($instance)` raised a PHP `Error`; without an instance, every class after the first got the first class's fields, so `is_field()` could be wrong | Each class gets its own fields | `field_list(`, `is_field(` |
| MOD-126 (KD-24) | HTML form encoding | `form::serialize()` returned `null` | Returns the form HTML | `html\encode\form::serialize(` |
| MOD-126 (KD-26) | Map from object | Mapping an object or array raised `ArgumentCountError` | Maps onto the subclass's declared properties; undeclared names raise `cannot_set_property` | `util\map\from_object` |
| MOD-126 (KD-38) | Validation failure | The message argument was discarded | `get_message()` and `getMessage()` return it | `validate_failure(` with a message |
| MOD-126 (KD-17, 18) | Legacy events | `hookable` passed no data to its handler (with notices); `stub::get_listeners()` returned nothing | The handler receives the event data; the added listeners are returned | `event\hookable`, `event\stub` |
| MOD-127 (KD-07) | API request method | `_get_request_method()` always threw `missing_method` | Returns the method `set_input()` dispatched (`GET` for a `HEAD` request) | API subclasses calling `_get_request_method()` |
| MOD-127 (KD-08) | API missing method | No method and no fallback raised `unknown_method` | Raises `missing_method` | Catches of `unknown_method` around `set_input(` |
| MOD-127 (KD-58) | API `HEAD` flag | Once a controller served a `HEAD` request, `_is_HEAD_request()` stayed true for every later request | Reset per request | `_is_HEAD_request()` on reused controllers |
| MOD-127 (KD-59) | API `TRACE` | Echoed an empty object (input fields were lost) | Echoes the input's fields | `TRACE` requests |
| MOD-127 (KD-09) | API status formatting | `%class-type%`/`%class-description%` produced warnings and wrong text; `get_code_class_type()`/`_description()` returned literal placeholders | Filled from the RFC 7231 class table | `status_code_class_format(`, `format_code(`, `get_code_class_type(` |
| MOD-127 (KD-57) | View resolution | The `view_resolved` event's `resolved` was always `\flat\design` (with notices) | The matched design class path, e.g. `\flat\design\sample\page` | `view_resolved` listeners |
| MOD-129 (KD-15) | Shutdown weights | Weight `0` joined the default queue; weights outside -50 to 50 were silently queued as default | `0` is ordered among the weights; out-of-range integers throw `bad_priority` | `shutdown::register(` with integer priorities |
| MOD-129 (KD-52) | UUID versions | `uuid::get()` with an invalid version and no namespace returned a v4 UUID | Throws `invalid_version` (versions 3, 4 and 5 still fall back to v4 without a namespace) | `uuid::get(` with a computed version |
| MOD-129 (KD-55) | Random strings | Invoking a `rand\str` instance (`$generator()`) ignored its configured pool and length | Uses the instance's pool and length; `$generator(5)` changes only the length; options still build a new pool | `rand\str` objects called as functions |
| MOD-130 (KD-13) | Asset HTML output | Several items in one `register_html_special()`/`register_html([...])` call kept only the first and made the asset depend on itself | All items print, in the order given; no self-dependency (also when `register_html()` gets `resolved`) | `register_html_special(` with several items, `register_html(` with arrays |
| MOD-130 (KD-12) | Inline asset source | `print_contents()` on an inline JavaScript source raised `TypeError` | Prints the `<script>` with the source | `RESOURCE_JS_SOURCE` assets |
| MOD-130 (KD-14) | Asset printing | `print_contents()` of an unprintable type and `print_tag(TAG_STYLE_SOURCE)` printed nothing; inline-source printing of a file asset printed an empty `<script></script>` | These explicit requests throw `app_error`; `print_tag()` without a tag type still prints nothing for assets with no tag form | `print_contents(`, `print_tag(` with explicit types |
| MOD-131 (KD-50) | Phone extensions | `+1 800 555 1234` stored the whole number as its extension (`x8005551234`); `ext 42` and `x89` extracted digits of the main number | `+1` numbers have no extension unless digits follow the 10-digit number; text extensions are read from after the marker | `flat\data\phone` `extension` and string output; stored phone strings |
| MOD-131 (KD-51) | Phone country codes | Most international numbers had no `country_code`; `e164` read `+` plus the whole number | Country code set from the table in `data/phone.php`; `e164`/`e164_ndc`/`e164_intl` split accordingly | `country_code`, `e164*` of non-NANP numbers |
| MOD-132 (KD-32) | cURL Chrome user agent | Selecting `config::user_agent_chrome` sent the user agent `0` | Sends the Chrome user-agent string | `user_agent_chrome` |
| MOD-132 (KD-30) | Debug kill exit status | `debug::kill($msg, ['exit_status'=>N])` ignored N (only a nested form worked) and the fallback exit read undefined variables | The plain form sets the exit status; the nested form still works | `debug::kill(` |
| MOD-132 (KD-29) | Debug value descriptions | Arrays read `{object class: %class%}`; numbers and booleans kept raw placeholders; `"0"` read as `{empty string}` | `{array count: N elements}`, `{integer} 42`, `{boolean} false`, `0` | Logs or tests matching `debug::data2str(` output |
| MOD-132 (KD-53) | Template output buffers | A failing generated template, or a failure inside `tmpl::format()`, left an output buffer open, swallowing later output | Buffers are closed on failure | Code relying on output after a template failure |
| MOD-133 (KD-01, 02, 03) | PDO statements | `fetch` options all threw `bad_option`; `execute` without a callback raised a PHP `Error`; drivers reporting `rowCount()` 0 for SELECT (SQLite) threw `not_found` even with rows | `fetch` true/class/callable add or pass each row, `false` executes without fetching; plain `execute` adds rows as `flat\data\generic`; `not_found` means no rows were fetched | `pdo\statement`, `'fetch'`, `'execute'`, `not_found` handling |
| MOD-133 (KD-04) | Mongo CA file | A failed certificate write returned `null` | Throws `app_error` ("could not write CA file") | `ssl\cafile::get_file(` |
| MOD-133 (KD-05) | DB controller `load_read` | Always returned `null` with an "undefined constant self" warning | Classes implementing `\flat\db\op\read` return `op_read()` (when defined) or `read($param)` | `::load_read(` |
| MOD-133 (KD-33, 34) | Legacy data ORM | `orm::get_data()` and `get()` returned nothing; a constructor array raised a PHP `Error` | They return the stored data (`set()` returns the driver's result); the array is driver configuration (`'driver'` class or object, the rest passed on); an invalid driver throws `bad_param` | `flat\data\orm` |
| MOD-133 (KD-35) | Session ORM destroy | Ended the session, then warned while regenerating the id | Regenerates (removing the old data), clears `$_SESSION` and ends the session, without warnings | `handler\native::destroy(` |
| MOD-133 (KD-60, 61) | ORM objects | `delete()` did nothing on an object that had been read; `crud_error::get_op()` returned the details | Read objects can be deleted (once); `get_op()` returns the operation | `flat\orm` `delete(`, `crud_error` |
| MOD-133 (KD-62) | Mongo client | `load_client()` without options warned about `foreach` on `null` | No warning | `mongo::load_client(` |
| MOD-134 (KD-21) | Validation options | `value_on_pass`, `value_on_fail`, `fail_handler` and `exception_on_fail` were ignored: success returned the value, failure `null` | All four apply; on failure `fail_handler` wins, then `exception_on_fail`, then `value_on_fail` | `::validate(` with options |
| MOD-134 (KD-22) | Regex validation | A PCRE engine error (e.g. invalid UTF-8) returned `false` like a mismatch | Throws `preg_error` | `match\regexp` validation of untrusted text |
| MOD-134 (KD-20) | Validation factory | The default constraint rejected the pairs `add_validate()` builds | Accepts them; other members are rejected | `util\validate\factory` subclasses |
| MOD-134 (KD-68) | Validation controller | Constructed and did nothing | Throws `BadMethodCallException` once its arguments are valid | `core\controller\validate` |
| MOD-134 (KD-23) | Image constructor | Width and height together left the image unchanged | Both are proportional minimums (like `resize_min_proportional`) | `new image($source, $w, $h)` with both |
| MOD-134 (KD-19) | Yasumi date queries | `get_date()`/`get_year()` were `null` for valid input; a cached provider was not attached; `isHoliday()`/`isWorkingDay()` threw; locale ignored; the epoch counted as unparseable | Timestamp and year are set; providers are shared per provider, year and locale; holiday queries work; locale passed to Yasumi | `azuyalabs\yasumi\dateQuery` |
| MOD-135 (KD-67) | **SSH private keys (security)** | The temporary private key written for ssh2 was **unencrypted** (`openssl rsa` ignores `-passout` without a cipher), and failures left temporary key files behind | Written encrypted with AES-256 under the random passphrase; every failure removes the temporary files | Hosts that ran `flat\core\ssh` public-key logins: consider rotating keys and clearing stray `sfnpriv*` files in the temp directory |
| MOD-135 (KD-39) | Payment transactions | The transaction adapter dropped every SDK field initialized to `null`, **including `amount`** | All declared fields are mapped | `customer\transaction\*\AuthorizeNetTransaction`; transactions sent without an amount |
| MOD-135 (KD-44) | Payment transaction validation | Any validation result except `false` or a string passed (e.g. `null`) | Only `true` passes | `_validate_transaction_type(` implementations |
| MOD-135 (KD-40, 41) | Subscription create | `billTo`/`shipTo`/schedule fields initialized to `null` were dropped; `create()` returned `null` | Fields are mapped; `create()` returns the new subscription id | `subscription::create(` |
| MOD-135 (KD-42) | Subscription search | Every page request asked for page 1, so a subscription beyond the first 1,000 was never found | Pages advance | `subscription::get_info(` |
| MOD-135 (KD-43) | Payment response errors | `response_error($code, $text)` ignored the text | Uses it as the message text | `response_error(` with two arguments; logs matching its message |
| MOD-135 (KD-66) | Payment API exceptions | A failing SDK call threw an `\Exception` whose message was a `print_r` of the exception (could exhaust memory) | Message is `Class: message`; the original is `getPrevious()` | Code parsing that message |
| MOD-135 (KD-63, 64) | S3 uploads | Files from 5 GB to 10 GiB went to a single upload, which S3 rejects; multipart uploads lost the content type | Files of 100 MB or more use multipart; the content type is kept | `s3::file_upload(` with large files or a content type |

## Suggested consumer check

When the consumer source is available, search it for the terms in the silent
change table. For each hit, decide whether the consumer used the old result as
is, compensated for it, or never reached it. Record the outcome here, per entry.
