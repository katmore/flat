# API comparison: v1.73.2 and the php-7.4 code

The public API of the php-7.4 code, compared by Reflection with the dump of `v1.73.2`
([../../php-7.3/notes/API-V1.73.2.json](../../php-7.3/notes/API-V1.73.2.json)). The method is described in
[../../php-7.2/notes/API-COMPARISON.md](../../php-7.2/notes/API-COMPARISON.md). Dump:
[API-V1.74.0.json](API-V1.74.0.json) (the name is the planned release).

Made on 2026-11-27 in the PHP 7.4.33 test container, after MOD-298:

```sh
php tests/tools/apidump.php dump src --autoload=/opt/flat-test/runtime/vendor/autoload.php > API-V1.74.0.json
php tests/tools/apidump.php compare ../php-7.3/notes/API-V1.73.2.json API-V1.74.0.json
```

## Result

Made after MOD-303 (the SSL classes renamed) and MOD-302 (`validate\collection` removed); the first dump of this phase, made
before them, had no differences at all.

- 620 entries in the `v1.73.2` dump, 619 in this one. Every class, method, property, constant, parameter, type and default of
  the classes that loaded before is the same.
- **One file removed:** `flat\core\util\validate\collection`, which could never be loaded (KD-108).
- **Seven entries changed from "cannot be loaded" or "declares nothing" to "loads":** the MongoDB SSL classes, now
  `flat\db\driver\mongo\ssl`, `ssl\cafile`, `ssl\cafile\base64`, `ssl\cafile\file`, `ssl\localcert`, `ssl\localcert\base64`
  and `ssl\localcert\file` (KD-107). The old names `flat\db\mongo\ssl...` no longer exist.
- The corrected adapter trait (MOD-295) is under `src/flat/vendor`, which the tool skips; it is not part of flat's API.
- Apart from that, the two dumps differ in one stored message only: the fatal-error text of a class that cannot load, which
  PHP 7.4 words differently (now `flat\cloud\authorize\AuthorizeNetARB` and `AuthorizeNetCIM` are the only ones).

## Classes that cannot be loaded

Two: `flat\cloud\authorize\AuthorizeNetARB` and `AuthorizeNetCIM`. Loading them prints the Authorize.Net SDK's own
deprecation notice ("is deprecated, use AuthorizeNet::API instead"), which the tool counts as a failure. They go with the
Authorize.Net wrappers in v2.X. The 7.3 release notes said seven; the `v1.73.1` and `v1.73.2` dumps list eight (the
two above, `validate\collection` and the five SSL classes); the `v1.72.0` and `v1.73.0` dumps list seven.

## Entries that declare nothing

The tool lists 14 entries as "declares nothing" (it looks for the class named by the path). Checked one by one:

- Six declare a class under another name and load under neither: four stray duplicates (`api\response_header\exception`,
  `api\response_header\exception\missing_request_field`, `cloud\authorize\customer\payment\bankAccount` and `creditCard`)
  and two (`flat\fsm`, `flat\orm\invalid_field`). They are KD-109 and Q57 in
  [../active/KNOWN-DEFECTS.md](../active/KNOWN-DEFECTS.md).
- Six are empty files (the known leftovers of the php-7.3 handoff).
- Two declare no class: `flat\event\alert\listener` (the retired class of MOD-107) and `flat\orm\missing_required_field`.
