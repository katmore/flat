# Dependency alerts, php-7.3 phase

The end-of-phase audit (HANDOFF section 2, README rule "At the end of every phase"). Run on
2026-11-20 with Composer 2.11.2 (image `composer:2`, digest
`sha256:50ea4d33fdcde1adb0f045547295faa1548b33872e5f789dae5b02ce2fb5de93`; the pinned
Composer 2.2 of the test image has no `audit` command), on copies of both locks.
Earlier note: [../../php-7.2/notes/DEPENDENCY-ALERTS.md](../../php-7.2/notes/DEPENDENCY-ALERTS.md).

## Result

- `tests/docker/tools/composer.lock` (PHPUnit and friends): no advisories.
- `tests/docker/runtime/composer.lock`: the same four advisories as at the end of php-7.2,
  and nothing new. No lock moved in this phase except its platform (MOD-189).
- **None of the four can be fixed on PHP 7.3.** A dry-run update (`composer update --dry-run -W
  <package>:<version>`, with `--no-security-blocking` when a version is affected by another
  advisory) under the 7.3.33 platform fails for each package, checked version by version:
  - `mongodb/mongodb` 1.21.4, 1.21.5 and 1.21.6 (the fixed releases) require PHP `^8.1`.
  - `aws/aws-sdk-php` 3.368.0 and 3.371.4 and later require PHP `>=8.1` (the newest, 3.402 and
    later, `>=8.2`).
  - The locked versions stay `mongodb/mongodb` 1.16.1 and `aws/aws-sdk-php` 3.337.3.

## The four, for the owner to keep dismissed on GitHub

- Alert 3, `aws/aws-sdk-php` CloudFront policy injection (high): needs 3.371.4, PHP 8.1.
- Alert 2, `aws/aws-sdk-php` S3 Encryption Client key commitment (medium): needs 3.368.0, PHP 8.1.
- Alert 5, `mongodb/mongodb` GridFS (medium): needs 1.21.5, PHP 8.1.
- Alert 4, `mongodb/mongodb` names with `.` or NUL (high): needs 1.21.4, PHP 8.1.

Nothing for the owner to reopen or resolve after this phase. Expect these to become fixable in
the php-8.1 phase. Also reported, not an advisory: `doctrine/annotations` is abandoned with no
replacement.
