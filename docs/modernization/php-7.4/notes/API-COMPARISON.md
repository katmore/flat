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

- 620 classes, interfaces and traits in both. **No differences** in any class, method, property, constant,
  parameter, type or default: the 7.4 code is a drop-in replacement for `v1.73.2` as far as the API goes.
- The corrected adapter trait (MOD-295) is under `src/flat/vendor`, which the tool skips; it is not part of flat's API.
- The two dumps differ in one stored message only: the fatal-error text of the one class that cannot load
  (`flat\core\util\validate\collection`, already so in every dump since `v1.72.0`), which PHP 7.4 words differently.

## Classes that cannot be loaded

Eight, the same eight in the `v1.73.1`, `v1.73.2` and 7.4 dumps (the list is in the dump; `v1.72.0` and `v1.73.0` have
seven). The eighth, `flat\cloud\authorize\AuthorizeNetARB`, gives the Authorize.Net SDK's own deprecation notice
("AuthorizeNetARB is deprecated, use AuthorizeNet::API instead") as its reason. The 7.3 release notes say seven.
