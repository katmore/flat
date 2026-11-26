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
  Coverage totals under it: 332 classes, 1,065 methods, 8,412 statements, all covered.
- JavaScript execution checks: Node 26.13.1, separate `node:26-slim` container pinned
  to `sha256:e6bedef217677b276c591795e169840edd9096dbf13f29df8ef8d1856b8820bc` in
  `docker/bin/test-javascript`. PHP minification remains on PHP 7.3.33.
- Composer 2.11.3, image `composer:2.11` pinned
  `sha256:0049ce90b432795bf413a738b073963ada1a689e1a8708104684baafe94586ec`.
- Composer platform pins, `tests/docker/tools/composer.json` and
  `tests/docker/runtime/composer.json`: `php` 7.3.33 (the runtime one also `ext-mongodb`
  1.16.2). Both locks carry the new content hash and platform.

## Extensions (built from the commits pinned in the Dockerfile, unchanged from 7.2)

- xdebug 3.1.6 (mode `develop,coverage`), mongodb 1.16.2 (with libmongoc), imagick 3.8.1 (the release tag's commit; a build from Git reports its version as
  `@PACKAGE_VERSION@`) against ImageMagick 7.1.2-36 built from source, sockets. The image build checks the exact PHP version and that these extensions load.

## Services (unchanged)

- MongoDB 9.0 and RabbitMQ 4.4.1 (Alpine 3.24.4 build of 2026-11-24), both pinned by digest in `docker/docker-compose.yml`.

## Image name

The test image is `flat-tests-php73` (it was `flat-tests-php72`).

## Test dependencies (runtime lock)

At the newest release PHP 7.3.33 allows: `php-amqplib` 3.7.5, `azuyalabs/yasumi` 2.4.0 (2.5 and later need a newer
PHP), `aws/aws-sdk-php` 3.337.3 (3.368 and later need PHP 8.1), `mongodb/mongodb` 1.16.1 (1.17 and later need the
1.17 extension, PHP 7.4), `alcaeus/mongo-php-adapter` 1.2.5 (the last 1.x), Guzzle 7.17.0 and the rest of the
lock. Held: `authorizenet/authorizenet` 1.9.9 (2.x removes the classes flat's Authorize.Net wrappers are built on; the
wrappers are deprecated and go in v2.X, Q48).

