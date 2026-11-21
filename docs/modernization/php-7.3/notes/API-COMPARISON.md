# API comparison: v1.72.0 and the php-7.3 code

The public API of the php-7.3 code, compared by Reflection with the dump of `v1.72.0`
([../../php-7.2/notes/API-V1.72.0.json](../../php-7.2/notes/API-V1.72.0.json)). The method is
described in [../../php-7.2/notes/API-COMPARISON.md](../../php-7.2/notes/API-COMPARISON.md).
Dump: [API-V1.73.0.json](API-V1.73.0.json) (the name is the planned release).

Made on 2026-11-20 in the PHP 7.3.33 test container, after MOD-190 and the modernization
M1 (see [MODERNIZATIONS.md](MODERNIZATIONS.md)):

```sh
php tests/tools/apidump.php dump src --autoload=/opt/flat-test/runtime/vendor/autoload.php > API-V1.73.0.json
php tests/tools/apidump.php compare ../php-7.2/notes/API-V1.72.0.json API-V1.73.0.json
```

## Result

- 620 classes, interfaces and traits in both. **No differences** in any class, method,
  property, constant, parameter, type or default: the 7.3 code is a drop-in replacement for
  `v1.72.0` as far as the API goes.
- The two JSON files are not byte-identical, only in metadata: the dump tool now records a
  `reason` for each of the seven classes that cannot be loaded (the same seven as in 7.2, listed
  in [../../php-7.2/notes/API-COMPARISON.md](../../php-7.2/notes/API-COMPARISON.md)). The
  `reason` is PHP's own message; for `AuthorizeNetCIM` it is a `Deprecated:` notice that the
  third-party Authorize.Net SDK raises when its deprecated file is loaded (it did on 7.2 too).

## Current comparison tool

MOD-221 and MOD-226 add return-reference metadata and require it in both inputs. Re-dump both
versions with the current tool before making a new comparison; older JSON dumps remain the
historical record and are rejected as incomplete rather than assumed to describe value returns.
