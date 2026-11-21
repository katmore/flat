# Errors the test environment hid (Q40)

Status: all fixed (KD-80 to KD-87, MOD-196 and MOD-200 to MOD-203); this is the record of the
measurement. The environment now reports `E_ALL` and fails on deprecations.

The test image has no `php.ini`, so PHP 7.3 uses its built-in `error_reporting`
(`E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED`, 22519), and PHPUnit 8.5 prints
`E_USER_DEPRECATED` but does not fail on it. Measured 2026-11-21 on PHP 7.3.33, unit tests only
(1,378 tests, services excluded), with a throwaway PHPUnit config in the scratchpad that
reports everything and converts notices, warnings and deprecations to failures:

```sh
php -d error_reporting=-1 phpunit --exclude-group services -c <config with convert*ToExceptions="true">
```

Result: 19 failing tests. By cause (the first one, KD-80, is fixed):

- `src/flat/core/html/encode.php` line 58 (9 tests): non-static method `_option_to_param()`
  called statically.
- `src/flat/core/curl/request.php` line 106 (3 tests): undefined variable `fields_string`.
- `src/flat/core/util/image/exception/bad_dim.php` line 29 (2 tests): undefined variable `err`.
- `src/flat/api/response` (4 tests, from `tests/Unit/Api/ResponseTest.php`): undefined property
  `data` or `message` on the response objects.
- `src/flat/core/collection/controller.php` line 157: undefined index `missing`.
- `src/flat/core/mapper.php` line 48: undefined variable `property`.
- `src/flat/orm.php` line 195: `each()` is deprecated (PHP 7.2).
- `src/flat/vendor/tedious/JShrink/minifier.php` line 183: `strpos()` with a non-string needle
  (`$this->b` is `false` at the end of the input; deprecated in PHP 7.3). Was hit by the two
  regex tests of KD-80.

Not measured: the MongoDB and RabbitMQ tests, and code no test reaches. None of this is
new in 7.3 except the last item; the rest has been in the code since the 7.2 phase.
