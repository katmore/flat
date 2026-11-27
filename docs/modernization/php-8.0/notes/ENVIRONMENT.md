# Environment

PHP 8.0.30, pinned official CLI manifest in docker/Dockerfile. E_ALL is checked
at image build; PHPUnit converts notices, warnings and deprecations to failures.
All test definitions, platform pins and locks live in this repository.

## Runtime checkpoint

The compatibility run retains all inherited package and tool versions:
Xdebug 3.1.6, ssh2 1.4.1, PHPUnit 9.6.39, ext-mongodb 1.20.1 and Composer 2.11.3.
New compatible tools and packages are upgraded in later isolated checkpoints.

Full suite: 1,709 tests, 8,098 assertions; 100% of 332 classes, 1,105 methods and
8,878 lines. The SSH fixture uses HTTPS with optional build-time CA trust.
