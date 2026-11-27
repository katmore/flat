# PHP 7.4 exit review

Checked 2026-11-27 against flat `986d3cc` and flat-webapp `9e6fb73`.
No PHP version switch, merge, rebase, release tag or master update was made by this review.

## Repair before the PHP step

**KD-128: the mutation runner cannot establish a clean baseline.**

`docker/bin/mutate --limit=1 --seed=74 src/flat/core/mapper.php` exits before
running a mutant. Its throwaway checkout copies src and tests but omits bin and
docker, which CodingRulesTest now scans. The first error is a missing /app/bin
directory (296 tests reached, one error). The ordinary repository suite passes;
this is a broken test-tool checkout, not a new failure in framework behavior.

The same wrapper's service mode starts only MongoDB and RabbitMQ. By inspection,
it also lacks the WireMock, SSH and S3 services and environment inputs now needed
by the service-group tests. Repair both modes and prove their baselines pass;
do not skip the coding-rules or service tests to obtain a result. No mutation
adequacy claim is made from this failed probe.

## Release closeout

1. Merge the reviewed php-7.4 fixes to master and publish the patch release,
   normally v1.74.1, on the owner's instruction. Only v1.74.0 currently exists;
   it does not include MOD-333 to MOD-335. Draft notes:
   [../RELEASE-NOTES-V1.74.1.md](../RELEASE-NOTES-V1.74.1.md).
2. Move flat-webapp's lock from v1.74.0 to the published patch, run its Docker
   suite, and finish its own phase merge on the owner's instruction. Its master
   remains at the earlier PHP 7.3 merge; its php-7.4 branch has the PHP 7.4 work.
3. Complete the closed-source consumer scan at the owner's timing. The public
   sample app has been tested with the patched source, but that does not test
   the private application. Live Authorize.Net sandbox checks also need the
   owner's sandbox environment; the ordinary suite uses the documented mock.
4. Declare PHP 7.4 complete, then create PHP 8.0 branches from the completed
   phase. Release publishing and master changes remain the owner's decisions.

### Why GitHub reports one commit behind

Flat's master at 2812136 is the merge commit of PR 6. Its tree is identical to
its php-7.4 parent da6427f, but the phase branch continued from that parent and
does not contain the merge commit itself. At the check, php-7.4 was six commits
ahead and one behind. This is ancestry, not a missing code fix or a rewritten
branch. After this check, the owner requested merging master back into php-7.4.
That merge includes 2812136 without changing the phase's file contents; the branch
is no longer behind master. Master itself remains at 2812136.

## Evidence already complete

The production source is unchanged since the preceding verified fix session:
1,703 Docker tests, 7,470 assertions, no errors/warnings/failures/skips; 100%
class/method/line coverage. The sample app passed 67 tests and its server-log
check against the patched source; all 45 JavaScript programs passed. A fresh
API comparison found only the added declared SSH auth fingerprint property.
All eleven defects from the adversarial review are fixed and Q62 is answered.

All four lock audits were rerun in the pinned Composer container for this exit
check. Both runtime locks still have the same four AWS SDK/MongoDB advisories;
both tool locks are clean. The fixed releases require PHP 8.1, so the PHP 8.0
step will not resolve them. The abandoned doctrine/annotations package remains.
GitHub's current Dependabot dismissal state could not be reread: both API
requests returned "Resource not accessible by integration" (HTTP 403). The
previous recorded alert list remains in [DEPENDENCY-ALERTS.md](DEPENDENCY-ALERTS.md).
No GitHub alert was changed.

## Carry forward, without expanding the PHP step

- Revisit the older untriaged mutation survivors after repairing the runner.
  They are leads about test strength, not eight proven application bugs:
  [archived mutation sample](../../php-7.3/history/MUTATION-PROBE.md).
- The Mongo localcert subclasses' inheritance/passphrase oddity is already
  recorded under KD-107. The Mongo driver does not use those helpers. Do not
  silently redesign them as part of a PHP runtime upgrade.
- The deliberately deferred API status/PATCH, Authorize.Net wrapper removal
  and XML resource-checksum changes stay in [../../DEFERRED-V2.md](../../DEFERRED-V2.md).

## PHP 8.0 handoff essentials

Keep the runtime step separate from bug fixes. Move both Docker runtimes and
Composer platform pins to the last PHP 8.0 patch, check E_ALL, and address every
runtime diagnostic at its cause. Test cURL's resource-to-object change, SSH
resource lifetime, stricter internal argument types, method-signature compatibility and vendored
code as well as flat itself. Bring every tool/dependency to its newest PHP
8.0-compatible release, one at a time with the full suite after each change.
Reevaluate the explicit annotations requirement with fresh SDK integration
checks rather than assuming PHP 8 makes it removable. Keep the approved AWS
exception until PHP 8.1 and the Authorize.Net exceptions until the v2 removal.
