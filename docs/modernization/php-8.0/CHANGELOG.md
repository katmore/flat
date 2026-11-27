# PHP 8.0 change log

## MOD-341 — Start the PHP 8.0 phase (2026-11-27)

Owner authorized PHP 8.0 and aggressive compatible syntax, tooling and dependency
updates. Fetched the existing php-8.0 branch at 11b5381 and checked it out.
Added the phase work list and carried unresolved mutation and private checks forward.
Master is untouched. Validation is recorded as each migration change lands.

## MOD-342 — Use HTTPS for the SSH fixture mirror (2026-11-27)

The HTTP mirror returned 403 before the baseline could run. Use HTTPS and the
existing optional build secret for proxy trust; no certificate verification is disabled.
The secret is read only during package installation and is not copied into an image.

## MOD-343 — Pin the PHP 8.0 runtime (2026-11-27)

The Docker target is PHP 8.0.30, pinned by manifest digest. Both test Composer
platforms move to 8.0.30 without changing package versions. Composer regenerated
the lock metadata and download URLs. The application requirement becomes >=8.0.0.
The build continues to verify the exact PHP version, E_ALL and every extension.
Full-suite results will be recorded after the image builds.

## MOD-344 — Preserve positional event callbacks (2026-11-27)

Runtime compatibility, no intended behavior change. PHP 8 treats string keys
passed to call_user_func_array as named parameters. Normalize only the outer
handler argument list with array_values; aggregated payload keys remain intact.
The original tests exposed seven errors and one variadic-key mismatch on PHP 8.
Five regression cases cover absent, call, event, stored and aggregated data.

### MOD-344 validation

The event/debug/facade regression selection passes unchanged on both PHP 7.4.33
and PHP 8.0.30: 43 tests, 116 assertions. The inherited PHP 7.4 unit baseline,
before the fix, passed 1,496 tests and 6,823 assertions. A fresh API baseline
contains 602 loadable entries. The inventory was regenerated for the five new cases.

### MOD-343 compatibility checks

The PHP 7.4 full baseline plus the positional-callback regression passes:
1,708 tests, 7,495 assertions. PHP 8.0 needs three harness adaptations: recognize
T_NAME_QUALIFIED for namespace declarations, assert the new native warning
capitalization, and require PHP 8.0 in the runtime guard. No checks are removed.
FLAT_TEST_IMAGE optionally selects a generic explicit comparison image; the
committed default remains the pinned PHP 8.0 image.

## MOD-345 — Complete source-load review (2026-11-27)

Correction to the initial migration review: the first lint report was inspected
before its command finished. Its completed output and the full API dump find
that validate\match is a reserved class name on PHP 8. KD-129/Q63 records the
required public-rename decision. Native PHP 8 also adds Stringable interfaces
and inferred __toString return metadata; those are runtime reflection changes.

## MOD-346 — Adapt native PHP 8 resource and error handling (2026-11-27)

The first full PHP 8 run exposed a real cookie cleanup regression: curl_close
is now a no-op, so releasing the handle after unlinking recreated its cookie
jar. Release the private CurlHandle before deleting temporary files. Release
the robots handle at its previous close point too.

Disabled proc_open is undefined on PHP 8. Check availability before invocation
so SSH still raises its existing syserror and cleans key files. The subprocess
test now asserts that complete framework error rather than PHP 7's warning.
Invalid XPath argument types now raise native TypeError; the test verifies
that precise error and both libxml state-restoration paths. No diagnostics are hidden.

## MOD-347 — Rename the reserved match base class (2026-11-27)

Owner approved Q63: rename the unused empty abstract validate\match class to
validate\match_rule. Source path map:
`src/flat/core/util/validate/match.php` → `src/flat/core/util/validate/match_rule.php`.
No repository code references the old class. The child namespace match remains
valid on PHP 8 and retains every existing class name. No alias or shim is added.
A syntax guard parses every framework file with TOKEN_PARSE, including classes
not reached by execution coverage. KD-129/Q63 is archived in history/RESOLVED-DEFECTS.md.

### Runtime checkpoint validation (MOD-343, MOD-346, MOD-347)

PHP 8.0.30 full Docker suite: 1,709 tests, 8,098 assertions, no errors, failures
or skips. Coverage: 100% of 332 classes, 1,105 methods and 8,878 executable lines.
The full API dump has 602 loadable entries. The intentional class rename is the
only source API rename; PHP itself adds Stringable on 302 classes and infers
string returns on 16 __toString methods. Both platform locks retain their
previous package versions at this checkpoint. Source syntax guard and inventory
are included. Documentation validation reports zero problems.

## MOD-348 — Adopt PHP 8.0 syntax (2026-11-27)

Modernization, no intended behavior change. String predicates replace boolean
substring tests and fixed nonnumeric prefix/suffix checks; position-dependent
strpos calls and overridable loose-comparison prefixes remain unchanged.
Validated integer asset dispatch and PCRE error selection use match and throw
expressions. Known objects use ::class; string conversion capability uses
Stringable; optional libxml details use nullsafe access. Unused catch bindings
are removed and collection members promote their private untyped constructor storage.
Public names, parameter names/types/defaults and payloads stay the same.

### MOD-348 validation

The full suite passes: 1,712 tests, 8,110 assertions (including three new API-tool
cases). Coverage remains 100% of all loaded classes, methods and lines. The
602-entry API dumps immediately before and after modernization are identical.
Existing test assertions were not changed for these syntax rewrites.

### MOD-348 assertion-count correction

The recorded run reports 1,712 tests and **8,104** assertions, not 8,110. Coverage
is 332/332 classes, 1,104/1,104 methods and 8,867/8,867 lines. The promoted empty
constructor reduces executable instrumentation; the public API dump is unchanged.

## MOD-349 — Describe PHP 8 types in API tooling (2026-11-27)

ReflectionUnionType has no getName and mixed must not be rendered as ?mixed.
Use ReflectionType's own string representation. Three new cases cover union,
mixed and nullable types. The targeted API-tool suite passes 8 tests and 70
assertions; the full suite containing these cases also passes (MOD-348 above).

## MOD-350 — Upgrade Xdebug to 3.5.3 (2026-11-27)

Pinned upstream commit 127bbcb980400752221cfaa54bdc1420e6ef3c12, the newest
stable release verified to support PHP 8.0. Its develop-mode exception stack
cache retains argument objects, delaying SSH destruction and exhausting fixture
connections. Upstream src/develop/stack.c confirms the strong trace cache.
Garbage collection did not release those references; that attempted test change
was discarded. Keep the strict session-count assertion and use Xdebug coverage
mode for test runs (native entry point and Compose). PHP E_ALL, warning/error
conversion and every existing diagnostic assertion remain enabled.

Validation: 57 SSH tests/199 assertions, then 1,712 full-suite tests/8,091
assertions; 100% of 332 classes, 1,104 methods and 8,867 lines. No errors or skips.

## MOD-351 — Upgrade ssh2 to 1.6.1 (2026-11-27)

Pinned upstream e5d1333605d58805ea317f4349458201786dd1ca. This release requires
PHP 8.0 and retains the existing libssh2 1.11.1/OpenSSL 3.6.5 builds. Full Docker
suite passes 1,712 tests and 8,094 assertions with 100% coverage. Password and
key logins, host pins, failed sessions, SFTP, process checks and cleanup pass.

## MOD-352 — Preserve the alias guard under PHP 8 tokenization (2026-11-27)

PHP 8 represents qualified names as single tokens. Extend the existing no-alias
rule to recognize qualified and fully qualified calls, retaining its previous
meaning. Five cases cover plain, absolute and qualified names, comments and
string literals. Coding-rule tests: 9 tests, 610 assertions, all pass.

## MOD-353 — Reduce Docker snapshot space and verify extension versions (2026-11-27)

VFS Docker copies a full filesystem for every layer; intermediate comparison
images and repeated source-build layers exhausted the workspace. Discarded only
this task's obsolete images and unused build cache, preserving source and the
current services. The pinned source builds now share one RUN layer, bootstrap
travels with the dependency stage, and permissions/version checks share a layer.
No private image or host path is required by the committed harness.

Git's imagick source archive retains @PACKAGE_VERSION@ in its header. Stamp the
pinned release's package.xml version (3.8.1) as the upstream packager does.
Build assertions verify PHP, imagick, Xdebug, ssh2, MongoDB, E_ALL and required
extensions. Full suite: 1,717 tests, 8,111 assertions; 100% coverage. No tool or
package version changes in this checkpoint. The interrupted Yasumi update was
restored before this validation and will be retried independently.

## MOD-354 — Close the CLI mutation assertion gap (2026-11-27)

The PHP 8 CLI sample caught seven of eight mutants; relaxing in_array's strict
flag survived. Add an unrelated boolean option to the existing line-handler
contract test, requiring error=false. Source behavior stays unchanged. The
three mutants at that line are now all killed, with no invalid mutants or
timeouts. The full compact-harness run includes the tightened assertion.

## MOD-355 — Upgrade azuyalabs/yasumi (2026-11-27)

2.6.0 → 2.7.0, resolved against PHP 8.0.30. No other
package version changes in this checkpoint. Full Docker suite: 1717 tests,
8116 assertions, no errors/failures/skips; 100% method and line coverage.

## MOD-356 — Upgrade paragonie/constant_time_encoding (2026-11-27)

v2.8.2 → v3.1.5, resolved against PHP 8.0.30. No other
package version changes in this checkpoint. Full Docker suite: 1718 tests,
8098 assertions, no errors/failures/skips; 100% method and line coverage.

## MOD-357 — Upgrade psr/cache (2026-11-27)

1.0.1 → 3.0.0, resolved against PHP 8.0.30. No other
package version changes in this checkpoint. Full Docker suite: 1718 tests,
8105 assertions, no errors/failures/skips; 100% method and line coverage.

## MOD-358 — Upgrade psr/log (2026-11-27)

1.1.4 → 3.0.2, resolved against PHP 8.0.30. No other
package version changes in this checkpoint. Full Docker suite: 1718 tests,
8100 assertions, no errors/failures/skips; 100% method and line coverage.

## MOD-359 — Upgrade symfony/deprecation-contracts (2026-11-27)

v2.5.4 → v3.0.2, resolved against PHP 8.0.30. No other
package version changes in this checkpoint. Full Docker suite: 1718 tests,
8104 assertions, no errors/failures/skips; 100% method and line coverage.

## MOD-360 — Upgrade symfony/yaml (2026-11-27)

v5.4.57 → v6.0.19, resolved against PHP 8.0.30. No other
package version changes in this checkpoint. Full Docker suite: 1718 tests,
8111 assertions, no errors/failures/skips; 100% method and line coverage.

## MOD-361 — Upgrade myclabs/deep-copy (2026-11-27)

1.13.4 → 1.14.0, resolved against PHP 8.0.30. No other
package version changes in this checkpoint. Full Docker suite: 1718 tests,
8106 assertions, no errors/failures/skips; 100% method and line coverage.

## MOD-362 — Upgrade the isolated OpenSSL build to 4.1.0 (2026-11-27)

The final upstream check found stable OpenSSL 4.1.0. Its release archive is
pinned by SHA-256 and builds with the current libssh2/ssh2 on PHP 8.0. Link the
static SSH crypto with -Bsymbolic so it cannot interpose on PHP's original
OpenSSL 1.1.1w. The standalone /usr/bin/openssl command now uses the pinned
4.1.0 build; the build verifies its version. PHP's original crypto stays intact.
An isolated key round trip succeeds between PHP's crypto and the new CLI.

Full Docker suite: 1,718 tests, 8,103 assertions, no errors/failures/skips.
Coverage is 100%: 332 classes, 1104 methods and 8867 lines.

## MOD-363 — Select patched YAML release streams (2026-11-27)

The final audit corrects MOD-360's selection: higher-numbered YAML 6.0.19 has
five advisories that patched 5.4.57 does not. Return the PHP 8.0 lock to 5.4.57.
Root and runtime manifests allow patched 5.4, 6.4, 7.4 and 8.1 streams, so newer
PHP hosts can still select newer secure releases. This is a security constraint,
not a hold for a consumer's old API. No advisory is ignored or dismissed.

Composer now reports the original four AWS/MongoDB advisories; the five YAML
advisories are gone. Full Docker suite: 1,718 tests, 8,112 assertions, no failures,
errors or skips; 100% of 332 classes, 1,104 methods and 8,867 lines.
