# Session handoff, php-7.4

Updated 2026-11-26, to continue the phase in another session. The plan and rules are in
[../HANDOFF.md](../HANDOFF.md); this page only says where things stand. When it is used up, move it to
`history/`.

## Branches

- flat: `php-7.4` (created at `673df9f`, `master`, `v1.73.2`). All work so far is pushed there.
- flat-webapp: `php-7.4` at `bcb91ec` (its `master`). Nothing has been changed on it.

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
- MOD-312, MOD-313: `flat\fsm` and `flat\orm\invalid_field` declared under their path names (Q57).
- MOD-306 to MOD-310: the five `class_alias` calls removed with their references fixed, and a rule against `class_alias`
  in both repositories' `AGENTS.md`. [../notes/ALIASES.md](../notes/ALIASES.md) is the record.

## Next

1. The owner reviews [../RELEASE-NOTES.md](../RELEASE-NOTES.md), the notes it links, and tags `v1.74.0` (Q52).
2. After the tag: move flat-webapp's application lock to the published release, update its `DependencyLockTest` (it
   requires `1.73.x`) and bring its packages to the newest 7.4 releases, one at a time. See
   [../notes/CONSUMER-CHECK.md](../notes/CONSUMER-CHECK.md) for what a fresh resolution gives.
3. The next phase (PHP 8.0) needs the owner's go-ahead.

## Working in a cloud session

- Docker Hub can answer 429 on pulls; pull the service images one at a time, then use `--offline`.
- Behind the cloud proxy, set `FLAT_TEST_CA_FILE=/root/.ccr/ca-bundle.crt` for builds.
- `composer update --lock` rewrites some download URLs; keep the lock diff to real changes.
- After an environment change the Docker daemon can be gone: `pkill containerd`, then start `dockerd`; the images are kept.
- `composer update --no-security-blocking` is needed to resolve a package that has advisories (see
  [../notes/DEPENDENCY-ALERTS.md](../notes/DEPENDENCY-ALERTS.md)); never commit an ignore entry.

## Waiting for the owner

Nothing. Q51 to Q58 are answered. The owner tags the release.
