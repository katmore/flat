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

## Suggested consumer check

When the consumer source is available, search it for the terms in the silent
change table. For each hit, decide whether the consumer used the old result as
is, compensated for it, or never reached it. Record the outcome here, per entry.
