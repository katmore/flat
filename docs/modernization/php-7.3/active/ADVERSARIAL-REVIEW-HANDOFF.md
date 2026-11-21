# Adversarial review of the php-7.3 phase: handoff

You are a second agent checking the php-7.3 work of another agent. Assume it is wrong somewhere
and try to prove it. Believe nothing in the documents until you have reproduced it. The author
already missed things once (see "Where I expect weaknesses"), so do not trust its searches either.

## Ground rules

- `AGENTS.md` binds you, above all the two prime directives: `master` is the maintainer's alone,
  and you never hide an error from PHP. You never push to `master`, and you suppress, skip or
  ignore nothing to get a result.
- Report, do not fix. You change no code, tests or locks. A finding goes in the review file with
  evidence; the owner decides.
- Run everything through Docker (`docker/bin/test`, `docker/bin/mutate`, `docker/bin/check-docs`).
  Use fresh `mktemp -d` clones; put no vendor tree in the repository. Do not run PHP as root.
- Short chat replies; long output goes in the review file.
- `app/` and `web/` in flat-webapp model the one known consumer: findings there are reported only.

## What is under review

Frozen review targets:

- flat: `v1.72.2` (`c257625`) to `8bee551` (`master` and `php-7.3`), 47 commits.
- flat-webapp: `202644b` to `9b8cc1a` (`master` and `php-7.3`), 13 commits.
- This review is part of preparing v1.73.1, which is not released yet. The owner works through
  confirmed findings one at a time with the author, who proposes options for each.
- Start with `docs/modernization/php-7.3/README.md`, `HANDOFF.md` (the plan the author followed)
  and `CHANGELOG.md` (MOD-188 to MOD-214; every change has an entry). The claims are in
  `RELEASE-NOTES.md`.

## Claims to try to break

1. **Public API unchanged from `v1.72.0`.** Re-dump with `tests/tools/apidump.php`. Then test
   the tool itself: change a signature in a scratch copy and see whether it notices.
2. **Behavior changes are exactly the three listed** (KD-80, KD-83, KD-85) in
   `active/CONSUMER-IMPACT.md`. Read the whole `git diff v1.72.2 8bee551 -- src` and look for any
   hunk that changes behavior and is not listed or is mislabeled "no behavior change"
   (KD-81, 82, 84, 86, 87, 88 and the `array_key_first()` modernization).
3. **KD-80 (`continue 3` in the minifier) is correct.** Differential-test it on real JavaScript:
   division versus regex, a regex at the end of input, nested quotes. Could it now corrupt
   output the old code got right?
4. **KD-86 (`each()` replaced by `key()`, `current()`, `next()`) behaves the same** for null
   values, non-list arrays and empty input. KD-88 (XML resource) gives byte-identical output.
5. **Nothing is hidden from PHP.** Search beyond the author's greps: every `set_error_handler`,
   `libxml_use_internal_errors`, `error_log`, `ini_set`, `assert`, return-true handlers, PHPUnit
   attributes and groups, Docker and entry-point settings. Only four places are approved
   exceptions (`AGENTS.md`); check each carries its comment block and has not spread.
6. **The test environment reports `E_ALL` and fails on deprecations.** Confirm in a fresh image;
   try to make a notice and a deprecation slip through.
7. **Test changes since `v1.72.2` weakened no assertion.** Review
   `git diff v1.72.2 8bee551 -- tests`, including the `?? ''` reads and the
   `assertObjectNotHasAttribute` swaps in `tests/Unit/Api/ResponseTest.php`.
8. **Line endings:** the 21 converted files differ from before only by CR; no file mode or
   content moved; `.gitattributes` does not mangle any binary or vendored file.
9. **Locks:** the two test locks were patched by hand to the new platform and content hash
   (MOD-189). Run `composer validate --check-lock` on both; confirm no package moved against
   `v1.72.2`.
10. **Reproducible from a fresh clone with only Docker**, including a cold image cache, the CA
    input, and `--offline`. Check every digest and pinned commit still resolves.
11. **The release notes and docs match reality:** counts, versions, and the statement that
    `v1.72.x` raised the same notices.

## Where I expect weaknesses

- **Coverage is "100% of the files that load".** `phpunit.xml.dist` has
  `processUncoveredFilesFromWhitelist="false"`, so a source file no test loads is not in the
  denominator. Seven classes cannot be loaded at all. Quantify the real gap.
- **The author's first audit missed two swallowed errors**: a test helper discarding the
  Authorize.Net deprecation and a test pinning four XML warnings. Look for more of that kind,
  especially tests that capture an error and then only count it.
- **The flat-webapp log check may have holes.** `docker/bin/test` in flat-webapp greps the
  server log for `PHP Warning|Notice|Deprecated|Strict Standards|Parse error|Fatal error`. It
  would not match `Catchable` or `Recoverable fatal error`, and I believe it misses the app's own
  `error_log(json_encode(...))` lines (a handled fatal error). Verify and report.
- **The functional tests cover the shipped application only** (36 tests); request bodies are not
  observable and the fatal-error page is untested. How much do they really prove?
- **The mutation probe was one sample of 40** with 8 survivors, untriaged
  (`active/MUTATION-PROBE.md`). Run more seeds, especially over the code changed in this phase.
- **Not measured:** code that no test reaches; a PHP 7.2 host resolving to `v1.72.x` (needs the
  tag); the RabbitMQ container sometimes exits at startup (see `HANDOFF.md`, traps).
- **Judgement calls to challenge:** `php >=7.3.0` with no upper bound; the three approved
  exceptions and their removal conditions; keeping `500` for an unsupported API method; the
  `app/` change to `InputTrait` (a literal bug, a PHP fatal error by design).

## Method

Clone both repositories fresh at the frozen commits. Reproduce the author's checks first
(`docker/bin/test all --coverage`, `docker/bin/check-docs`, `git ls-files --eol`,
`docker/bin/mutate --services`), then go after the list above. Prefer experiments that can fail:
break something on purpose and see whether the suite notices.

## Deliverable

A new file, ADVERSARIAL-REVIEW.md, in this folder, with: findings ordered by
severity, each with evidence and a way to reproduce it; the claims you confirmed; what you could
not check and why. Keep it short; link to logs instead of pasting them.
