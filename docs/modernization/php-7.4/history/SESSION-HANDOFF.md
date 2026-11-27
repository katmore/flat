> Archived: completed phase material or a superseded work snapshot. Current status and work: [phase overview](../active/SESSION-HANDOFF.md). The record below describes its original checkpoint.

# Session handoff, php-7.4

Updated 2026-11-27, to continue the phase in another session. The plan and rules are in
[../HANDOFF.md](../HANDOFF.md); this page only says where things stand. When it is used up, move it to
`history/`.

## Branches

- flat: `php-7.4` (created at `673df9f`, `master`, `v1.73.2`). All work so far is pushed there.
- flat-webapp: `php-7.4` (created at `bcb91ec`, its `master`). Its lock moved to flat `v1.74.0` and its packages to the newest 7.4 releases.

## Done

- MOD-280 to MOD-284: the phase folder; the test image on PHP 7.4.33; `tests/tools/apidump.php` fixed; platform pins;
  full verification and source review.
- MOD-285 to MOD-289, MOD-294: tools and dependencies at their newest 7.4-compatible releases; see
  [../notes/ENVIRONMENT.md](../notes/ENVIRONMENT.md). The service audit of 2026-11-27 found every other pin current.
- MOD-292, MOD-293: the owner answered Q51, Q52 and Q54 "yes": the root `composer.json` requires `php >=7.4.0` and
  `doctrine/annotations ^2.0`.
- MOD-295 to MOD-297 (Q53, "fix it now"): a corrected copy of the Mongo adapter's read-preference trait, ext-mongodb
  1.20.1 and `mongodb/mongodb` 1.20.0.
- MOD-291: flat-webapp on 7.4.33. MOD-298: review of every PHP message under 7.4 (none left).
- MOD-299: release preparation: release notes, API comparison, consumer check, dependency audit.
- MOD-300 to MOD-305: the unloadable classes looked into: `validate\collection` removed (KD-108), the MongoDB SSL classes renamed
  to match their paths (KD-107), the rest recorded as KD-109 / Q57.
- MOD-316, MOD-317: the API tool discards the Authorize.Net SDK's notice (approved exception), so every class in `src` loads; the
  retired `event/alert/listener.php` is deleted.
- MOD-314, MOD-315: the four mismatched classes declared at their own path, their forwarding files deleted, seven empty files deleted,
  and `tests/Unit/CodingRulesTest.php` guarding the rules of `AGENTS.md` (no `class_alias`, names match paths, no class includes).
- MOD-330: flat-webapp locks flat `v1.74.0`, and its packages and test tools are at the newest 7.4 releases (changes in its own `CHANGELOG.md`).
- MOD-327, MOD-328: the last PHP function overrides and the fake AWS client replaced by real conditions and a real S3 emulator (Adobe S3Mock).
- MOD-329: KD-115 (a named argument left out kept another's value) and KD-116 (a failed abort replaced the upload failure) fixed (Q60, Q61).
- MOD-312, MOD-313: `flat\fsm` and `flat\orm\invalid_field` declared under their path names (Q57).
- MOD-306 to MOD-310: the five `class_alias` calls removed with their references fixed, and a rule against `class_alias`
  in both repositories' `AGENTS.md`. [../notes/ALIASES.md](../notes/ALIASES.md) is the record.

## Adversarial fixes

MOD-333 to MOD-335 fix KD-117 to KD-127: PDO, cURL and SSH. Q62 keeps unpinned
SSH callers allowed and verifies supplied pins before either authentication method.
The sample app passed with the modified framework mounted into its containers.

## Next

1. Repair KD-128 (mutation runner), then complete the closeout in [../notes/EXIT-REVIEW.md](../notes/EXIT-REVIEW.md).
2. The owner decides whether to publish the patch; notes: [../RELEASE-NOTES-V1.74.1.md](../RELEASE-NOTES-V1.74.1.md).
3. The next phase (PHP 8.0) needs the owner's go-ahead.

## Working in a cloud session

- Docker Hub can answer 429 on pulls; pull the service images one at a time, then use `--offline`.
- Behind the cloud proxy, set `FLAT_TEST_CA_FILE=/root/.ccr/ca-bundle.crt` for builds.
- `composer update --lock` rewrites some download URLs; keep the lock diff to real changes.
- After an environment change the Docker daemon can be gone: `pkill containerd`, then start `dockerd`; the images are kept.
- `composer update --no-security-blocking` is needed to resolve a package that has advisories (see
  [../notes/DEPENDENCY-ALERTS.md](../notes/DEPENDENCY-ALERTS.md)); never commit an ignore entry.

## Waiting for the owner

No open implementation questions. Q51 to Q62 are answered; the owner tags releases.
