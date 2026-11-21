# Known defects and owner questions, php-7.3 phase

The 79 known defects of php-7.2 (KD-01 to KD-79) are all resolved; the record is
[../../php-7.2/active/KNOWN-DEFECTS.md](../../php-7.2/active/KNOWN-DEFECTS.md). No test
carries `@group known-defect` now. A defect found in this phase starts here as KD-80 and
follows "How each fix lands" in that file. Questions continue the php-7.2 numbering.

## Defects found in this phase

### KD-80 - JShrink drops a character after a regex that starts with a quote

- Category: A in effect (the owner confirmed the intent, Q37). `src/flat/vendor/tedious/JShrink/minifier.php`,
  `loop()`: after `saveRegex()` a `continue;` meant "next loop pass" but, inside two nested
  `switch` statements, only left the inner one. The loop then called `getReal()` a second time and
  lost the character `saveRegex()` had already read: `return /'a'/.test(s);` became
  `return /'a'/test(s);` and `return /'q'/; foo();` lost its `;`.
- Fixed (MOD-196): `continue 3;`. Tests assert the correct output.
- **Fixed.**

## Owner questions

Each has a recommendation; "agree" is enough. Questions 37 to 39 are answered; 40 is open.

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
    **Answered (owner, 2026-11-21):** fix it as intended, even with a behavior change. Done as
    KD-80 (MOD-196): `break` really did corrupt the output.

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
    **Answered (owner, 2026-11-21): (a).** Both tests are now in the "Approved exceptions" list
    of `AGENTS.md` and carry a comment block; the php-8.1 phase must remove them (MOD-198).

39. **`ConfigTest` skips a test when run as root (prime directive 2).**
    `tests/Unit/Core/ConfigTest.php` (line 164),
    `testDirectoriesAndUnreadableFilesAreNotLoadedAsConfig`, calls `markTestSkipped()` when
    the process is root, because root can read every file. The harness never runs PHP as root
    (`docker/bin/test` uses user 1000) and the full run reports no skipped test, so nothing is
    hidden today. But "never skip a test" has no exception for this, and a run as root would
    pass quietly without running the check. Not changed.
    Recommend: replace the skip with an assertion that fails when the process is root
    (the check cannot work there), so a wrong environment is red, not silently green.
    This is a test-only change; it needs your word because it touches a rule.
    **Answered (owner, 2026-11-21): agreed.** Done in MOD-197.

40. **Errors the test environment cannot see (prime directive 2).** The test image has no
    `php.ini`, so PHP's built-in default applies: `E_NOTICE`, `E_STRICT` and `E_DEPRECATED`
    are off. PHPUnit 8.5 also does not turn deprecations into failures. So real notices and
    deprecations have never shown in the suite, on 7.2 or 7.3: the earlier statements "no
    warning, notice or deprecation" (MOD-189 to MOD-192, the handoff) only covered warnings and
    errors. A measured run with everything on finds 19 failing unit tests in 11 places
    (list and command: [HIDDEN-ERRORS.md](HIDDEN-ERRORS.md)); the service tests were not measured.
    Recommend: (1) the test image uses PHP's own `php.ini-development` (E_ALL), as flat-webapp's
    image already does, and `phpunit.xml.dist` sets `convertDeprecationsToExceptions="true"`;
    (2) fix every cause through the known-defect process, one entry each, with the behavior
    changes in the consumer-impact list; (3) none of this is done yet. It also affects the
    published `v1.72.x`, so you may want to say so in the `v1.73.0` notes.
    Not changed: it raises what the tests report, so I want your word first.
    **Open.**
