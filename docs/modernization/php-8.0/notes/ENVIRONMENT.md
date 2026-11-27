# Environment

Target: PHP 8.0.30 with E_ALL and PHPUnit failures for notices, warnings and deprecations.
Docker definitions and both Composer locks remain in the repository.

## Baseline attempt

The initial full-suite build failed before tests: the SSH fixture's HTTP Alpine mirror
returned HTTP 403 in this environment. A PHP-only baseline build is in progress.
No test pass is claimed until execution completes.
