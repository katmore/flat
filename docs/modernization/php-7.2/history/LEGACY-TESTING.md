# Docker test environment

> **Historical, kept for the record:** the earlier test setup that lived outside the repository. It was replaced by the repository-owned harness in [../../TESTING.md](../../TESTING.md).

The supported entry point is now `./tests.sh`, with all setup files inside this
repository. See [../../TESTING.md](../../TESTING.md) for reproducible commands,
test types, services, cleanup and optional CA configuration. No path outside this
checkout is required. The older notes below are historical diagnostic records,
not current operational instructions.

# Legacy PHP coverage environment

The current diagnostic runner is `/workspace/flat-environment/php72-tools/run-tests`.
It executes PHP/PHPUnit inside Docker, never host PHP, and keeps PHP at **7.2.34**.
The framework and flat-webapp repository dependency locks remain unchanged by
these diagnostic dependency installations.

## Current runner

The named test container is `flat-unit-audit-mongo-php72`, image
`flat-unit-coverage-mongo:7.2`, label `flat.unit.audit=php72-mongo`. It bind mounts
`/workspace`, runs as the workspace user, and uses the internal Docker network
`flat-unit-database`. Its PHP_INI_SCAN_DIR includes the retained Xdebug ini for both
parent and isolated child processes. PHPUnit 8.5.56/Xdebug 2.9.8 report real line,
method and class coverage; branch coverage is not supplied by this driver.

Full run:

```sh
cd /workspace/flat
/workspace/flat-environment/php72-tools/run-tests --testsuite unit \
  --coverage-text=/workspace/flat-environment/php72-tools/coverage.txt \
  --coverage-clover=/workspace/flat-environment/php72-tools/coverage.xml \
  --log-junit=/workspace/flat-environment/php72-tools/junit.xml
```

The earlier helpers/images are retained: `run-tests-without-imagick` and
`run-tests-images`. The expanded test suite requires the image and MongoDB
capabilities, so the old minimal helpers are not full-suite replacements.

## Pinned diagnostic image sources

All Dockerfiles/build logs are retained under the php72-tools directory:

- Dockerfile.coverage: official PHP 7.2 image digest
  `sha256:42ffbc0798e4449bbd1e14fc4dcb87774aa1ad1900a09ef6a965bc0880aa2161`,
  Xdebug 2.9.8 upstream commit `3e09864af111d63b7eb486d88bfc6eb7d6429b85`.
- Dockerfile.images: ImageMagick 6.9.10-23 commit
  `e97831dfdfc064c5246cd7ba195ff6ecd4958f34`, Imagick 3.4.4 source commit
  `42c07db5d0075b26471397fb6de8ffb4bd2692d8`. The Git source extension reports
  the literal @PACKAGE_VERSION@ placeholder; source/tag and actual native
  geometry/file assertions document the tested build.
- Dockerfile.mongo: MongoDB extension 1.5.5 commit
  `1e0329d45437a9b303e5fd59143f879a720c81ae`, C-driver submodule
  `6afb83ef8dd8a1627bdc879e74bfa88bdd120a4b`. Source archive excludes Git metadata.
  This local-only diagnostic build disables MongoDB TLS; remote TLS behavior is
  not tested or established by it.

Sources came from allowed official GitHub repositories with supplied CA trust and
TLS verification retained. This did not grant access to blocked apt/PECL hosts.
The repository's original full development image still needs its separate setup
work. Diagnostic success is not flat-webapp readiness or a release sign-off.

## MongoDB dependencies and service

`/workspace/flat-environment/php72-runtime-deps/composer.json` isolates
mongodb/mongodb **1.4.3** and alcaeus/mongo-php-adapter **1.2.5**, resolved with
PHP 7.2.34 / ext-mongodb 1.5.5 platform constraints. Installation ran in the
existing tooling container; actual framework tests run on native PHP 7.2.34 and
its compiled MongoDB extension. `bootstrap-runtime.php` combines the isolated
PHPUnit/framework autoloader and these compatible runtime libraries. The newer
repository vendor tree is not used for these tests.

Service: container `flat-unit-mongo40`, MongoDB 4.0 image digest
`sha256:4ca81c89ad08f4cfa9906005126112bffe8fb363800466ef5e50f6238f6f6af1`,
label `flat.unit.database=php72`, network `flat-unit-database` (internal).
There is no host port mapping. The runner passes
`FLAT_TEST_MONGODB_URI=mongodb://flat-unit-mongo40:27017`; other environments can
supply their own local test URI (the tests default to localhost).

Tests own randomly named `flat_unit_*` databases and drop only those databases.
No production credentials or cloud service calls are used. Other existing
containers, including flat-webapp-dev, were not restarted or modified.

## RabbitMQ diagnostic service

The same internal `flat-unit-database` Docker network now also contains
`flat-unit-rabbitmq38`, image `rabbitmq:3.8.34-alpine` pinned to
`sha256:711d76bc8d9822adf32fd9bdbe33f53645a77e2bbd0675f8011eed397919f7a4`.
There are no published host ports. Disposable local test credentials are user
`flatunit`, password `flatunit-local-tests`. The broker starts with
`RABBITMQ_SERVER_ADDITIONAL_ERL_ARGS=+S 2:2` to bound Erlang scheduler resources.
The external PHP 7.2 runtime dependency manifest installs php-amqplib 2.12.3
(the deprecated AMQPConnection class used by this driver still exists there).
The root Composer manifest/lock are unchanged. This is a local compatibility
fixture, not a recommendation to deploy these legacy versions.

RabbitMqTest creates randomly named flat_unit_ queues, reads messages back,
and deletes its queue and closes connections/channels in teardown. AlertTest
uses explicit provider doubles with real SDK message/timeout types to control
consumer termination and timeout expiry refresh; it is not a delivery guarantee
or production broker integration claim. Authorize.Net 1.9.9 is also installed
only in the external diagnostic dependencies, matching root composer.lock.

AWS configuration-provider tests use actual AWS SDK 3.337.3 from the external
runtime dependency manifest (resolved for PHP 7.2.34). The repository's unchanged
lock currently selects 3.402.6 requiring PHP 8.2; this diagnostic setup does not
claim that root dependency tree runs on PHP 7.2. No AWS requests are sent. The SDK
notification toggle for PHP deprecation is scoped and restored inside that test.


Additional boundaries: SshBoundary explicitly doubles namespaced SSH transport functions because native SSH2 is unavailable; positive cryptographic/file/subprocess paths use actual local primitives. Native session persistence uses real PHP sessions and test-owned save directories across separate PHP processes; only the disabled-platform status branch uses an explicit namespaced double. Neither claims an external SSH server test.


Holiday diagnostics additionally pin azuyalabs/yasumi 1.8.0 in php72-runtime-deps only (PHP >=5.6; Composer added one package, no updates). It is not newly declared as a framework dependency. Subscription circuit-breaker verification runs the real 100,000-tick/1,000-details loop and adds about 40 seconds. PDO's buffered SELECT-row-count adapter is test-only and counts actual SQLite rows; it is not a claim about SQLite's native rowCount guarantee.


Measured full-suite runtime with the circuit-breaker case and actual Xdebug is 6.76 minutes (56 MB); its ~40-second standalone runtime was without coverage collection. It deliberately executes the real configured guard without patching counters. Raw PHPUnit coverage data is additionally retained outside the repository as php72-tools/coverage.php for review.


Final MOD-104 verification: 1,278 tests / 5,166 assertions pass; line/method/class coverage is 99.34% / 99.34% / 98.79%. Runtime for this run is 9.59 minutes (56 MB); runtime varies with instrumentation/load. All 631 PHP source files pass PHP-7.2 syntax checks. The remaining 53 lines require the three decisions in PENDING-QUESTIONS.md.


MOD-105/106/107 verification uses the existing PHP 7.2.34 image and MongoDB 4.0/RabbitMQ 3.8 services; those service containers had stopped when the environment resumed and were restarted. A test-owned native PHP TCP proxy forwards actual local MongoDB wire bytes and can close connections to exercise cached failure/retries. It creates no host-exposed port, fabricates no protocol reply and cleans up its child process/control files. The pthread scaffold is retired, so no fake Thread class or ZTS-runtime substitution is needed.
