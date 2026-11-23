# Test environment on PHP 7.3

Everything is defined in this repository (`docker/Dockerfile`, `docker/docker-compose.yml`,
`tests/docker/`). Recorded 2026-11-20, when the suite moved from PHP 7.2.34 (MOD-189).

## PHP and tools

- PHP 7.3.33 (NTS, CLI), the last 7.3 release. Base image `php:7.3-cli`, pinned
  `sha256:bd6f2dc7a4e4537e927355c94a74c3022911aa504278f5f0fd94016c90dfaecc` (the tag
  `php:7.3.33-cli` has the same digest).
- PCRE library 10.32 (PCRE2). PHP 7.2.34 had PCRE 8.41.
- `error_reporting` is `E_ALL`: the image uses PHP's `php.ini-development` and the build fails if it
  is not. PHPUnit fails on notices, warnings and deprecations (`phpunit.xml.dist`). MOD-200.
- PHPUnit 9.6.39 with php-code-coverage 9.2.32 (9.6 is the last line for PHP 7.3; PHPUnit 10 needs 8.1).
  Coverage totals under it: 332 classes, 1,063 methods, 8,393 statements, all covered.
- JavaScript execution checks: Node 22.23.5, separate `node:22-bookworm-slim` container pinned
  to `sha256:e9f565d32859f0cbfb52125eafe8be39390f24dac302dc3cdf566e8ba3ab21ee` in
  `docker/bin/test-javascript`. PHP minification remains on PHP 7.3.33.
- Composer 2.11.2, image `composer:2.11` pinned
  `sha256:50ea4d33fdcde1adb0f045547295faa1548b33872e5f789dae5b02ce2fb5de93`.
- Composer platform pins, `tests/docker/tools/composer.json` and
  `tests/docker/runtime/composer.json`: `php` 7.3.33 (the runtime one also `ext-mongodb`
  1.16.2). Both locks carry the new content hash and platform.

## Extensions (built from the commits pinned in the Dockerfile, unchanged from 7.2)

- xdebug 3.1.6 (mode `develop,coverage`), mongodb 1.16.2 (with libmongoc), imagick 3.8.1 (the release tag's commit; a build from Git reports its version as
  `@PACKAGE_VERSION@`) against ImageMagick 6.9.13-61 built from source, sockets. The image build checks the exact PHP version and that these extensions load.

## Services (unchanged)

- MongoDB 9.0 and RabbitMQ 3.13.7, both pinned by digest in `docker/docker-compose.yml`.

## Image name

The test image is `flat-tests-php73` (it was `flat-tests-php72`).
