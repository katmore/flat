# PHP 8.0 environment

The repository Dockerfiles, Compose services and two test locks are the complete
harness. Root composer.lock is an ignored local installation artifact; the two
committed test locks are the reproducibility inputs. The published root manifest
validates independently of that local artifact. PHP 8.0.30 is pinned by official manifest digest; both Composer platforms
pin 8.0.30. The build verifies the exact PHP/extension versions and E_ALL.

## Current versions

- Composer 2.11.3; PHPUnit 9.6.39; Xdebug 3.5.3; Node 26.13.1.
- imagick 3.8.1, ImageMagick 7.1.2-36, ssh2 1.6.1, libssh2 1.11.1.
- Isolated SSH crypto and standalone OpenSSL CLI: 4.1.0. Symbolic binding keeps
  their static crypto separate from PHP's original OpenSSL 1.1.1w. PHP's embedded
  libraries follow the archived official PHP 8.0.30 base; they are not relinked.
- MongoDB server 9.0; RabbitMQ 4.4; WireMock 3.13.2; S3Mock 5.2.3;
  SSH fixture OpenSSH 10.3 on Alpine 3.24. Images are pinned by digest.
- Yasumi 2.7.0; constant_time_encoding 3.1.5; psr/cache 3.0.0; psr/log 3.0.2;
  deprecation-contracts 3.0.2; deep-copy 1.14.0. Both locks hold exact versions.

## Compatibility and security boundaries

- PHPUnit 10 and newer need PHP 8.1; 9.6.39 is the newest resolved here.
- ext-mongodb 1.20.1, mongodb/mongodb 1.20.0 and aws/aws-sdk-php 3.337.3 are
  the newest compatible with PHP 8.0. The fixed advisory releases need PHP 8.1.
- Symfony YAML 5.4.57 is the patched PHP 8.0 stream. Higher-numbered 6.0.19
  has five advisories; the manifests allow patched newer streams on newer PHP.
- Authorize.Net 1.9.9 retains the legacy wrappers deferred to v2.X.
  doctrine/annotations 2.0.2 remains required by the SDK; the strict offline
  probe fails without it. Details: [dependency alerts](DEPENDENCY-ALERTS.md).
- Bundled Parsedown 1.8.0, ParsedownExtra 0.9.0, forceutf8 2.0.4 and JShrink
  1.8.1 remain the current upstream releases checked for this phase. Preserve
  the documented namespace and behavior patches when updating them.

## Test execution

Native bin/test and Compose select XDEBUG_MODE=coverage. Develop mode's strong
exception-argument cache retains SSH sessions; coverage mode preserves normal
PHP object lifetimes. E_ALL and PHPUnit's strict diagnostic handling stay enabled.
All approved reporting exceptions remain exactly where AGENTS.md lists them.

Pinned source builds share one layer to reduce VFS snapshot space. imagick's
release version is stamped into the git archive's packaging placeholder and
verified. Every downloaded source archive is SHA-256 checked before extraction.
Proxy CA trust is an optional build secret, including the HTTPS SSH
fixture mirror. Fresh checkouts need no private paths or prebuilt images.

## Result

Full suite: 1,728 tests, no errors/failures/skips; 100% of the configured loaded-code
coverage: 332 classes, 1,105 methods and 8,874 lines. All 602 framework API entries
load, all source/test PHP files pass syntax checks, and all 45 minifier programs
preserve their JavaScript results. Lock audits: four inherited runtime advisories;
tools clean. No YAML advisory remains. The owner controls phase completion and releases.

Assertion totals vary with subprocess coverage mode and signal polling. Use
test/failure counts and coverage as checkpoints; historical assertion counts
remain per-run observations.
