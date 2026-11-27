# Dependency alerts, php-7.4 phase

The end-of-phase audit (README rule "At the end of every phase"). Run on 2026-11-27 with Composer 2.11.3 (the pinned
`composer:2.11` image, `composer audit --locked`) on flat's two test locks and flat-webapp's two locks.
Earlier note: [../../php-7.3/notes/DEPENDENCY-ALERTS.md](../../php-7.3/notes/DEPENDENCY-ALERTS.md).

## Result

- `tests/docker/tools/composer.lock` (PHPUnit and friends): no advisories.
- flat-webapp's test lock (its PHPUnit): no advisories.
- `tests/docker/runtime/composer.lock` of flat, and flat-webapp's application lock: the same four advisories as at the
  end of php-7.3, and nothing new. `doctrine/annotations` is abandoned with no replacement (not an advisory;
  flat requires it since MOD-292).
- **None of the four can be fixed on PHP 7.4.** A dry-run update under the 7.4.33 platform
  (`composer update --dry-run -W --with <package>:<version> --no-security-blocking`) fails for each fixed release:
  - `mongodb/mongodb` 1.21.4 and 1.21.5 (the fixed releases) require PHP `^8.1`.
  - `aws/aws-sdk-php` 3.368.0 and 3.371.4 (the fixed releases) require PHP `>=8.1`.
  - A full dry-run update of flat's runtime lock finds nothing newer: it is at the newest release PHP 7.4 allows.
- What moved in this phase: flat's runtime lock went from `mongodb/mongodb` 1.16.1 to 1.20.0 (MOD-286, MOD-297); the
  new release is affected by the same two advisories, so the exposure is unchanged. `aws/aws-sdk-php` stays at 3.337.3.
  flat-webapp's application lock still holds `mongodb/mongodb` 1.16.1 until it follows flat's release.

## The four, for the owner to keep dismissed on GitHub

- Alert 3, `aws/aws-sdk-php` CloudFront policy injection (high): needs 3.371.4, PHP 8.1.
- Alert 2, `aws/aws-sdk-php` S3 Encryption Client key commitment (medium): needs 3.368.0, PHP 8.1.
- Alert 5, `mongodb/mongodb` GridFS (medium): needs 1.21.5, PHP 8.1.
- Alert 4, `mongodb/mongodb` names with `.` or NUL (high): needs 1.21.4, PHP 8.1.

Nothing for the owner to reopen or resolve after this phase. Expect these to become fixable in the php-8.1 phase.

## For applications that use flat

flat's `composer.json` requires `mongodb/mongodb ^1.0.0` and `aws/aws-sdk-php ^3.23`, so an application on PHP 8.1 or
newer can already install the fixed releases; on PHP 7.4 it cannot.
Whether an application is exposed depends on how it calls the libraries; this audit did not look at that.
