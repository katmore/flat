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

Made after MOD-302 to MOD-315 (the removals and renames of this phase); the first dump of this phase, made before them, had no
differences at all.

- 620 entries in the `v1.73.2` dump, 603 in this one. **Every one of them loads:** none is reported as unloadable or as declaring nothing. Every class, method, property, constant, parameter, type and default of
  every class that kept its name is the same.
- **Seventeen names gone.** Eleven are renames or removals of classes that existed under that name; the owner decided each one,
  none has an alias, and each is in [../active/CONSUMER-IMPACT.md](../active/CONSUMER-IMPACT.md) with what to search for:
  - `flat\api\header\exception` and `...\exception\missing_request_field` are now `flat\api\response_header\...` (MOD-314);
  - `flat\cloud\authorize\customer\payment_profile\bankAccount` and `...\creditCard` are now `...\customer\payment\...` (MOD-314);
  - `flat\cloud\authorize\subscription\cancelled` (MOD-306), `flat\data\session\driver` and `flat\data\session\driver\session`
    (MOD-308) and `flat\core\util\factory\exception\not_match_interface` (MOD-309), which existed only as `class_alias` names;
  - `flat\core\util\validate\collection`, which could never be loaded (MOD-302).
  The other six are files that declared nothing and are deleted: `flat\cloud\disqus`, four under `flat\cloud\pci`,
  `flat\orm\missing_required_field` (MOD-315) and `flat\event\alert\listener`, the retired pthread listener kept as comments (MOD-317).
- **Fifteen entries now load that did not:** the seven MongoDB SSL classes (MOD-303), `flat\fsm` and `flat\orm\invalid_field`
  (MOD-312, MOD-313), the four renamed classes above (MOD-314), which are now declared at their own path, and the two Authorize.Net
  wrappers `AuthorizeNetARB` and `AuthorizeNetCIM` (MOD-316).
- **Six methods and one constant "added"**, on `subscription\canceled`, `ssh\...\input_error\bad_param` and `data\orm\driver\session`
  (its constructor, `get_data()`, `set_data()` and `fallback_handler`). They are not new: the tool records only what a class declares
  under the name its path gives, so while these were aliases of another class their entries looked empty and the members were
  recorded under the old names.
- The corrected adapter trait (MOD-295) is under `src/flat/vendor`, which the tool skips; it is not part of flat's API.
- The two dumps also differ in the stored fatal-error text of a class that cannot load, which PHP 7.4 words differently.

## Classes that cannot be loaded

None. `flat\cloud\authorize\AuthorizeNetARB` and `AuthorizeNetCIM` were reported as unloadable in every dump since `v1.73.1`, but they
load and work: the Authorize.Net SDK raises an `E_USER_DEPRECATED` notice from its own `lib/deprecated/` files when the classes they
extend load, and the notice printed ahead of the tool's JSON made the tool give up. The tool now discards that one notice, an
exception approved by the owner on 2026-11-27 (`AGENTS.md`, "Approved exceptions"); `tests/Unit/Tools/ApiDumpTest.php` shows that
the same notice from anywhere else, or another severity from the SDK's folder, still makes a class unloadable. The `v1.72.0` and
`v1.73.0` dumps list seven unloadable classes and `v1.73.1` and `v1.73.2` eight; the 7.3 release notes say seven.

## Entries that declare nothing

None. `tests/Unit/CodingRulesTest.php` checks that every file in `src/flat` declares the class its path names.
