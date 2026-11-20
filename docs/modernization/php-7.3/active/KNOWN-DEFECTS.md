# Known defects and owner questions, php-7.3 phase

The 79 known defects of php-7.2 (KD-01 to KD-79) are all resolved; the record is
[../../php-7.2/active/KNOWN-DEFECTS.md](../../php-7.2/active/KNOWN-DEFECTS.md). No test
carries `@group known-defect` now. A defect found in this phase starts here as KD-80 and
follows "How each fix lands" in that file. Questions continue the php-7.2 numbering.

## Defects found in this phase

None so far.

## Owner questions

Each has a recommendation; "agree" is enough.

37. **JShrink: what did `continue` in the regex branch mean?** In
    `src/flat/vendor/tedious/JShrink/minifier.php`, `loop()`, a `continue;` sits inside two
    nested `switch` statements. In a `switch` it behaves like `break`, so it only left the inner
    switch; PHP 7.3 reports this (a warning, which PHPUnit turns into a failure). MOD-190 wrote
    it as `break`, which is exactly what it did before, so output is unchanged. The probable
    intent was to go straight to the next loop pass (`continue 3`), which would skip the
    "reg check of doom" that follows. That would change what the minifier outputs for some input,
    so it is a behavior change, not a compatibility fix.
    Recommend: keep `break` (no behavior change in this phase). If you want the loop
    `continue`, it goes through the known-defect process as KD-80, with a test that pins the
    input where the two differ.
    **Open.**

38. **Two tests switch off the AWS SDK's PHP deprecation notice (prime directive 2).**
    `tests/Unit/Cloud/AwsConfigTest.php` and `tests/Unit/Cloud/S3MultipartTest.php` set the
    environment variable `AWS_SUPPRESS_PHP_DEPRECATION_WARNING=true` (the first with the comment
    "deliberately verifies the maintained PHP 7.2 baseline"; both since KD-19-23, commit
    `66ec091`). The locked SDK (3.337.3) emits an `E_USER_DEPRECATED` whenever a client is built
    on a PHP version below 8.1, telling the user to upgrade to 8.1. So the notice is about the
    PHP version the whole project runs on, and it is the same on 7.2 and 7.3. The suppression
    was not found by the audit of v1.72.1 (MOD-180 to MOD-184), which searched for
    `error_reporting`, `ini_set`, `@` and ignore lists. flat's own code does not set it; this is
    test code only, so consumers are not affected by it.
    The cause cannot be fixed in a PHP 7.x phase: the notice goes away only on PHP 8.1 or with an
    older SDK, and neither is this phase's step. Nothing was changed and the tests still pass with
    the suppression. Options:
    - (a) Approve the two test setups as an exception in the "Approved exceptions" list of
      `AGENTS.md`, with a comment block in each test saying so, until the PHP 8.1 phase removes
      the cause. Recommended: it is honest (the notice is true), narrow, and test-only.
    - (b) Lock `aws/aws-sdk-php` in `tests/docker/runtime/composer.json` to a release that
      predates the notice, which removes the cause but tests an older SDK than the one the
      lock holds now.
    - (c) Remove the suppression and let the tests fail until PHP 8.1. Not recommended: a red
      suite for three phases hides real failures.
    Recommend (a). Only the owner can add to the exceptions list.
    **Open.**
