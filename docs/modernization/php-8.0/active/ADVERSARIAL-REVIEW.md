# Adversarial review

Independent review of php-8.0 at b8dc3b5 (2026-11-28). Everything was rerun
from a fresh container; the handoff was read but not trusted. Open items are
tracked in the [defect index](KNOWN-DEFECTS.md).

## Findings

### 1. Not installable on PHP 8.0 with default Composer (KD-130, Q64)

Every `mongodb/mongodb` and `aws/aws-sdk-php` release that supports PHP 8.0
has an advisory. Composer 2.11.3, the pinned version, blocks insecure versions
during resolution by default. So a fresh `composer update` fails, both on
`tests/docker/runtime/composer.json` and in a new project that requires this
branch of flat with platform PHP 8.0.30:

```
katmore/flat 1.80.0 requires mongodb/mongodb ^1.0.0 -> satisfiable by
mongodb/mongodb[1.21.5, 1.21.6] ... require php ^8.1
```

The harness hides this because it only runs `composer install` from the committed
lock. The per-package updates in MOD-355 to MOD-363 kept mongodb and aws locked,
so the full resolution was never tried. With `audit.block-insecure` set to false,
the resolver changes nothing, so the lock itself is current.

A consumer project resolves once it ignores these four advisories in
`config.audit.ignore` (tested): `PKSA-4t1p-xpk2-nsss`, `PKSA-dxyf-6n16-t87m`,
`PKSA-y93m-jgy6-r9ns`, `PKSA-61k5-cqr9-b8b4`.

### 2. PHP 8 comparison semantics change flag lookups (KD-131, Q65)

The migration review does not cover PHP 8's "saner string to number
comparisons" change. About 30 flag and option checks in `src` use non-strict
`in_array` or `array_search`. On PHP 7, `'flag' == 0` was true, so an option
array that held an integer 0 turned on every string flag. On PHP 8 it does not.
Same calls, branch base on PHP 7.4.33 compared with HEAD on PHP 8.0.30:

- `$data->field(['on_empty' => 0])` on a `\flat\data` field: tags are stripped
  on 7.4 (`xHello`). On 8.0 the raw `<script>x</script>Hello` is returned.
- `tmpl::format($missing, [0])`: 7.4 acts as if `silent_fail` were given and
  returns `''`. 8.0 throws `bad_design`.

Other affected sites include `pdo` (`execute`/`exec`), `session` flags,
`xml\dom`, `data\scalar`, `curl\response` flags and `tmpl` (`strip_tags`,
`nl2br`). No consumer-impact entry records the change. The 92 loose comparisons
against number literals were also scanned. They compare counts, status digits or
digit-only strings, so they look safe.

### 3. `reserved::make_safe_classname` misses PHP 8 reserved words (KD-132, Q66)

`flat\core\util\reserved::keywords` has no `match` (reserved in PHP 8.0, the
cause of KD-129) and no `mixed` (a reserved type name since 8.0). It also misses
older words: `fn`, `yield`, `self`, `parent` and the scalar type names (`int`,
`float`, `bool`, `string`, `true`, `false`, `null`, `void`, `iterable`,
`object`). For each of these, the "safe" name it returns cannot be declared as a
class on PHP 8.0.30 (verified one by one).

### 4. A failed request leaks its temporary cookie jar (KD-133, pre-existing)

`new curl\response($config, ['accept_cookies'])` creates a jar with `tempnam`.
When the request fails, the constructor throws `curlexec_error`. PHP never runs
the destructor of a half-built object, so the jar is never deleted. One
unreachable URL leaves one `flat_curl_cookie_*` file, on 7.4.33 and on 8.0.30.

### 5. Assertion totals are not reproducible

The records give exact assertion totals as validation results. The documented
final total is 8,112. This review got 8,108 with `--coverage` and 8,083 without
it, with the same 1,718 passing tests. A per-test JUnit comparison shows why:

- 26 subprocess tests assert one thing more under coverage mode.
- `testKillWaitsForTheFirstSignalBeforeSendingTheSecond` asserts once per poll.

That explains the 8,091 to 8,116 spread in the change log and the MOD-348
"correction". Recommendation: report tests, failures and coverage, and stop
treating the assertion total as a checkpoint.

### 6. Stale records and smaller points

- The handoff still asks to verify the PHP 7.4 patch publication. v1.74.1 was
  published on 2026-11-27 and is fully contained in php-8.0. The php-7.4 line of
  `docs/modernization/README.md` lists no releases and no merge to master.
- MOD-363 makes `symfony/yaml` a direct requirement of flat. Before, it was only
  a transitive dependency (through authorizenet). The consumer-impact entry does
  not say that. A `conflict` entry would block the vulnerable releases without
  adding a dependency. This is low priority.
- 517 source headers still say `PHP version >=7.x`. Only the touched files say
  8.0. This is cosmetic.
- Four PHP 8.0-era build sources (Xdebug, ImageMagick, imagick, MongoDB) are
  pinned only by commit tarball, with no checksum. OpenSSL and libssh2 do have
  SHA-256 checks.

## Claims verified

- Full Docker suite: 1,718 tests pass, no errors, failures or skips. Coverage is
  100% of 332 classes, 1,104 methods and 8,867 lines. Only four never-loaded
  files contain code (7 trivial methods).
- The API dump reproduces `notes/API-PHP80.json` with no differences. Compared
  with v1.74.0, the only changes are the match_rule rename, the Stringable
  interfaces, the `__toString` return types and v1.74.1's
  `ssh\auth::$fingerprint`.
- `TEST-INVENTORY.md` is current. `bin/check-docs` reports 0 problems.
- Every file in `src`, `tests` and `bin` lints cleanly on 8.0.30 with
  `php.ini-development`. On 7.4 with all errors reported, the branch base shows
  no deprecations that would hint at silent PHP 8 changes.
- The rewrites are equivalent: `switch` to `match` only ever sees int inputs,
  and a trait-provided `__toString` still satisfies `Stringable` on 8.0.30. The
  cURL handle release and the `proc_open` guard are correct. Every other
  `call_user_func_array` site passes a positional list.
- The tools lock audit is clean. The runtime lock has exactly the four
  documented advisories. The YAML advisory data and stream constraints match
  Packagist. The only error-reporting override in `src` is the approved one in
  `asset.php`.
