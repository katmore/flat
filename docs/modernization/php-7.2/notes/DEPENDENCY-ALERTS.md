# Dependency alerts (2026-11-20)

GitHub's Dependabot reported 5 alerts on `katmore/flat` (2 high, 3 medium), all in
`tests/docker/runtime/composer.lock`, the lock file of the test environment. The list was
read from the GitHub API by the owner and matches Composer's own audit (`composer audit
--locked`) of the same file; `tests/docker/tools/composer.lock` (PHPUnit and friends) has no advisories.

## Fixed

- Alert 1: `mongodb/mongodb` (medium): the driver could publish events containing
  authentication data; affected below 1.9.2. The test environment now runs library `1.16.1`
  with MongoDB extension `1.16.2` (was `1.4.3` with `1.5.5`; the library is limited by the
  extension version, so both moved). Checked with the full suite: 1,428 tests pass and
  coverage is still 100% (MOD-178).

## Not fixable on PHP 7.2

The fixed releases need a newer PHP than this phase may use. With PHP 7.2.34 as the
platform, Composer's newest choices are `aws/aws-sdk-php` `3.337.3` and `mongodb/mongodb`
`1.16.1` (the newest MongoDB extension for PHP 7.2 is `1.16`; `1.17` needs PHP 7.4, and
library `1.21.6`, a fixed release, requires PHP `^8.1`). Anyone installing flat on PHP 7.2
gets the same versions, because flat's `composer.json` allows `^3.23` and `^1.0.0`.

- Alert 3: `aws/aws-sdk-php` (high): CloudFront policy document injection via special
  characters; fixed in 3.371.4. flat does not use CloudFront (only a comment mentions it).
- Alert 2: `aws/aws-sdk-php` (medium): the S3 Encryption Client has a key commitment issue,
  CVE-2025-14761; fixed in 3.368.0. flat does not use the S3 Encryption Client.
- Alert 5: `mongodb/mongodb` (medium): GridFS data disclosure and deletion through
  query-operator injection in file IDs, CVE-2026-88023; fixed in 1.21.5. flat does not use GridFS.
- Alert 4: `mongodb/mongodb` (high): the library should reject `.` and NUL bytes in database and
  collection names, CVE-2026-81525; fixed in 1.21.4. flat passes the database and collection
  names it is given to `selectDatabase()` and `selectCollection()` (`db/driver/mongo.php`), so an
  application is exposed only if it passes names that an attacker controls. Applications should
  not do that regardless of this advisory.

## What happens next

- These four are expected to clear when the modernization reaches a PHP version that
  allows the fixed releases (library `1.21.x` needs PHP 8.1, so the php-8.1 phase).
- On GitHub they can be dismissed in the meantime with the reason "Risk is tolerable to this
  project" and a comment pointing here, so that new alerts stay visible; reopen them at the
  php-8.1 phase. Dismissing is the owner's decision; Claude cannot read or change alerts
  (the integration is refused access).
