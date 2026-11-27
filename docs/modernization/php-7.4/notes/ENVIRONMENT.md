# Test environment on PHP 7.4

Everything is defined in this repository (`docker/Dockerfile`, `docker/docker-compose.yml`,
`tests/docker/`). Recorded 2026-11-26, when the suite moved from PHP 7.3.33 (MOD-281 to MOD-289). It lists
what is at its newest release and, for the rest, the reason.

## PHP and tools

- PHP 7.4.33 (NTS, CLI), the last 7.4 release. Base image `php:7.4-cli`, pinned
  `sha256:620a6b9f4d4feef2210026172570465e9d0c1de79766418d3affd09190a7fda5`. PCRE 10.35.
- `error_reporting` is `E_ALL`: the image uses PHP's `php.ini-development` and the build fails if it
  is not. PHPUnit fails on notices, warnings and deprecations (`phpunit.xml.dist`).
- Newest: Composer 2.11.3 (`composer:2.11`, pinned by digest to the rebuild of 2026-11-27), Node 26.13.1 for the JavaScript checks,
  ImageMagick 7.1.2-36 built from source, imagick 3.8.1, MongoDB 9.0, RabbitMQ 4.4 and WireMock 3.13.2
  (service images pinned by digest).
- Held, with the reason:
  - PHPUnit 9.6.39 (with php-code-coverage 9.2): PHPUnit 10 needs PHP 8.1.
  - Xdebug 3.1.6: 3.2 and later need PHP 8.0.
  - ext-mongodb 1.20.1: the newest that builds on PHP 7.4 (1.21 needs PHP 8.1). It deprecates what
    `alcaeus/mongo-php-adapter` does; flat carries a corrected copy of that trait (MOD-295, Q53).

## Test dependencies

The two locks carry the platform pin PHP 7.4.33 (the runtime lock also `ext-mongodb` 1.20.1).

- Runtime lock, newest that PHP 7.4 allows: `mongodb/mongodb` 1.19.1 (MOD-286), `jms/serializer` 3.32.9 with
  `doctrine/annotations` 2.0.2 required explicitly (MOD-287), `azuyalabs/yasumi` 2.6.0 (MOD-288),
  `php-amqplib/php-amqplib` 3.7.5, `alcaeus/mongo-php-adapter` 1.2.5 (the last release), Guzzle 7.17.0 and the
  rest. `composer update --dry-run` finds nothing newer.
- Tools lock: PHPUnit 9.6.39, `nikic/php-parser` 5.9.0 (MOD-289).
- Held, with the reason:
  - `aws/aws-sdk-php` 3.337.3: newer releases need PHP 8.1.
  - `authorizenet/authorizenet` 1.9.9: held on purpose; the wrappers are deprecated and go in v2.X.
- Composer 2.11 blocks releases with security advisories. Every `mongodb/mongodb` that runs on PHP 7.4 has
  them (the fix needs PHP 8.1), so its lock was resolved with `--no-security-blocking`. The owner dismissed
  these advisories as "risk tolerable".

## Image name

The test image is `flat-tests-php74` (it was `flat-tests-php73`).

## Results

`docker/bin/test all --coverage`: 1,624 tests, 6,954 assertions, all pass; coverage 100% (332 classes, 1,099
methods, 8,824 lines).
