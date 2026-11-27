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

- 620 entries in the `v1.73.2` dump, 604 in this one. Every class, method, property, constant, parameter, type and default of
  every class that kept its name is the same.
- **Sixteen names gone.** Eleven are renames or removals of classes that existed under that name; the owner decided each one,
  none has an alias, and each is in [../active/CONSUMER-IMPACT.md](../active/CONSUMER-IMPACT.md) with what to search for:
  - `flat\api\header\exception` and `...\exception\missing_request_field` are now `flat\api\response_header\...` (MOD-314);
  - `flat\cloud\authorize\customer\payment_profile\bankAccount` and `...\creditCard` are now `...\customer\payment\...` (MOD-314);
  - `flat\cloud\authorize\subscription\cancelled` (MOD-306), `flat\data\session\driver` and `flat\data\session\driver\session`
    (MOD-308) and `flat\core\util\factory\exception\not_match_interface` (MOD-309), which existed only as `class_alias` names;
  - `flat\core\util\validate\collection`, which could never be loaded (MOD-302).
  The other five are empty files (`flat\cloud\disqus` and four under `flat\cloud\pci`) and `flat\orm\missing_required_field`, which
  declared nothing (MOD-315).
- **Thirteen entries now load that did not:** the seven MongoDB SSL classes (MOD-303), `flat\fsm` and `flat\orm\invalid_field`
  (MOD-312, MOD-313) and the four renamed classes above (MOD-314), which are now declared at their own path.
- **Six methods and one constant "added"**, on `subscription\canceled`, `ssh\...\input_error\bad_param` and `data\orm\driver\session`
  (its constructor, `get_data()`, `set_data()` and `fallback_handler`). They are not new: the tool records only what a class declares
  under the name its path gives, so while these were aliases of another class their entries looked empty and the members were
  recorded under the old names.
- The corrected adapter trait (MOD-295) is under `src/flat/vendor`, which the tool skips; it is not part of flat's API.
- The two dumps also differ in the stored fatal-error text of a class that cannot load, which PHP 7.4 words differently.

## Classes that cannot be loaded

Two: `flat\cloud\authorize\AuthorizeNetARB` and `AuthorizeNetCIM`. Loading them prints the Authorize.Net SDK's own
deprecation notice ("is deprecated, use AuthorizeNet::API instead"), which the tool counts as a failure. They go with the
Authorize.Net wrappers in v2.X. The 7.3 release notes said seven; the `v1.73.1` and `v1.73.2` dumps list eight (the
two above, `validate\collection` and the five SSL classes); the `v1.72.0` and `v1.73.0` dumps list seven.

## Entries that declare nothing

One: `flat\event\alert\listener`, the pthread listener the owner retired in MOD-107; the file keeps the old scaffolding as comments.
`tests/Unit/CodingRulesTest.php` now checks that every other file in `src/flat` declares the class its path names.
