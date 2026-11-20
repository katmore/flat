# API comparison: v1.5.5 and v1.72.0

What the public API of flat looked like in `v1.5.5` and in the php-7.2 code released as
`v1.72.0`, compared by PHP's Reflection, and what differs. Written to back the statement
in the release notes that the release is a drop-in replacement. Generated parts are the
two JSON files and the "Differences" section; the verdicts are written by hand.

## What was compared

Every class, interface and trait under `src/flat` (third-party `vendor` folders excluded)
was loaded in PHP 7.2.34 with the project's real dependencies, one class per process, and
described with Reflection: kind, abstract and final, parent, interfaces, and everything it
declares that is not private: constants (value), properties (default, static), and methods
(visibility, static, abstract, final, every parameter with its type, default, by-reference
and variadic flags, and the return type). 611 files in `v1.5.5` and 620 in `v1.72.0`.

- `API-V1.5.5.json` and `API-V1.72.0.json` in this folder are the two dumps.
- To reproduce: `php tests/tools/apidump.php dump <src-root> --autoload=<composer autoload.php> > api.json`
  for each version (run in the PHP 7.2 test container), then
  `php tests/tools/apidump.php compare old.json new.json`.
- Not compared: what the code does (that is the job of the tests and
  `active/CONSUMER-IMPACT.md`), private members, and classes that cannot be loaded.

## Result

No class, method, property or constant that existed and worked in `v1.5.5` was removed, and
no parameter or return type changed. Everything that differs is listed below with a verdict.

Changes to something a consumer could have been using:

- `flat\core\rand::__invoke()`: the default of `$min` is now `NULL` (was `0`). Part of the
  `rand` bounds fix (KD-72, MOD-140); calls that pass arguments are unaffected.
- `flat\core\curl\config::$request_method`: the declared default is now `NULL` (was `'GET'`).
  The constructor already set it to `NULL` in `v1.5.5`, so the value an object really had did
  not change (KD-70, MOD-136; described there as "the request method is still unset unless given").
- `flat\cloud\aws\s3::multipart_min_size`: now `104857600` (100 MB; was `10737418240`, 10 GiB,
  above S3's own limits). Documented in `active/CONSUMER-IMPACT.md` (S3 multipart upload).
- `flat\core\xml\encode::flat_xml_ver`: now `'0.3'` (was `'0.2'`). The documented XML
  wire-format change (MOD-019).
- `flat\data\orm\driver\session\handler\native::_get_ormkey()`: now `static` (was an instance
  method). It is `final protected`, so no subclass could override it, and a static method can
  still be called through `$this`. **This one was not in the consumer-impact document**; an
  entry was added with this report.
- `flat\core\controller\validate::RETIRED`: a constant added when the unfinished validation
  controller was retired (KD-68, MOD-134).
- `flat\core\curl\exception\empty_response` and `not_ok`: files removed. They were 0 bytes
  and declared no class, so there was nothing to use (MOD-125).

Changes that could not have broken anyone, because the class could not be used before:

- 24 classes could not be loaded in `v1.5.5` (a fatal error on load, for example a missing
  parent class) or their file declared no class, and now load. Examples: the Authorize.Net
  subscription and customer classes, `util\validate\hostname`, `data\orm\driver\session`,
  `tmpl\display`. Where a file now declares a class under a corrected name (for example
  `payment_profile\bankAccount` and `creditCard`), the old name never loaded either. The pthread
  `event\alert\listener` is the one retired class (MOD-107). I checked these against the loading
  behavior, not one by one against the change log.
- 11 files were added, all of them new, working classes (mostly exceptions).
- Seven classes cannot be loaded in either version and are unchanged
  (`AuthorizeNetCIM`, `util\validate\collection`, and six MongoDB SSL file classes).

## Differences (generated)


- constant added: 1
- constant changed: 2
- file added: 11
- file removed: 2
- load state changed: 24
- method modifier changed: 1
- parameter default changed: 1
- property changed: 1

### constant added

- `flat\core\controller\validate::RETIRED`

### constant changed

- `flat\cloud\aws\s3::multipart_min_size`: {"visibility":"public","value":"10737418240"} to {"visibility":"public","value":"104857600"}
- `flat\core\xml\encode::flat_xml_ver`: {"visibility":"public","value":"'0.2'"} to {"visibility":"public","value":"'0.3'"}

### file added

- `flat\api\header\exception`
- `flat\api\header\exception\missing_request_field`
- `flat\cloud\authorize\customer\payment_profile\bankAccount`
- `flat\cloud\authorize\customer\payment_profile\creditCard`
- `flat\cloud\authorize\subscription\cancelled`
- `flat\core\bad_interface`
- `flat\core\data\create_properties`
- `flat\core\util\factory\exception\not_match_interface`
- `flat\data\orm\driver\session\exception\bad_param`
- `flat\data\session\driver`
- `flat\data\session\driver\session`

### file removed

- `flat\core\curl\exception\empty_response`
- `flat\core\curl\exception\not_ok`

### load state changed

- `flat\api\response_header\exception\missing_request_field`: unloadable before, declares nothing now
- `flat\cloud\authorize\customer\payment`: unloadable before, ok now
- `flat\cloud\authorize\customer\payment\bankAccount`: unloadable before, declares nothing now
- `flat\cloud\authorize\customer\payment\creditCard`: unloadable before, declares nothing now
- `flat\cloud\authorize\customer\transaction`: unloadable before, ok now
- `flat\cloud\authorize\customer\transaction\authcapture`: unloadable before, ok now
- `flat\cloud\authorize\customer\transaction\authonly`: unloadable before, ok now
- `flat\cloud\authorize\customer\transaction\captureonly`: unloadable before, ok now
- `flat\cloud\authorize\subscription`: unloadable before, ok now
- `flat\cloud\authorize\subscription\canceled`: declares nothing before, ok now
- `flat\cloud\authorize\subscription\not_found`: unloadable before, ok now
- `flat\core\curl\exception\missing_curlinfo`: declares nothing before, ok now
- `flat\core\event\listener\factory`: unloadable before, ok now
- `flat\core\exception\bad_interface`: declares nothing before, ok now
- `flat\core\exception\bad_interface\message`: unloadable before, ok now
- `flat\core\md\convert\exception\invalid_source`: unloadable before, ok now
- `flat\core\ssh\exception\input_error\bad_param`: declares nothing before, ok now
- `flat\core\util\validate\factory\exception\not_match_interface`: unloadable before, ok now
- `flat\core\util\validate\hostname`: unloadable before, ok now
- `flat\data\orm\driver\session`: unloadable before, ok now
- `flat\db\driver\mongo\db\exception\bad_interface`: unloadable before, ok now
- `flat\event\alert\listener`: unloadable before, declares nothing now
- `flat\report\meta`: unloadable before, ok now
- `flat\tmpl\display`: unloadable before, ok now

### method modifier changed

- `flat\data\orm\driver\session\handler\native::_get_ormkey()`: static false to true

### parameter default changed

- `flat\core\rand::__invoke()` parameter 1 ($min): "0" to "NULL"

### property changed

- `flat\core\curl\config::$request_method`: {"visibility":"public","static":false,"default":"'GET'"} to {"visibility":"public","static":false,"default":"NULL"}
