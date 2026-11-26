# Session handoff, php-7.4

Updated 2026-11-26, to continue the phase in another session. The plan and rules are in
[../HANDOFF.md](../HANDOFF.md); this page only says where things stand. When it is used up, move it to
`history/`.

## Branches

- flat: `php-7.4` (created at `673df9f`, `master`, `v1.73.2`). All work so far is pushed there.
- flat-webapp: `php-7.4` at `bcb91ec` (its `master`). Nothing has been changed on it.

## Done

- MOD-280 to MOD-281: the phase folder; the test image is PHP 7.4.33.
- MOD-282: `tests/tools/apidump.php` no longer uses the deprecated `ReflectionType` string cast; its output
  matches the 7.3 dump of `v1.73.2`.
- MOD-283: both Composer platform pins are 7.4.33; no package moved.
- MOD-285: ext-mongodb 1.19.4. The brief's 1.21.1 needs PHP 8.1; 1.20.1 is the newest that builds, but it
  deprecates what `alcaeus/mongo-php-adapter` does (Q53).
- MOD-284: full suite with coverage on 7.4.33 (1,624 tests, 6,954 assertions, 100%), the JavaScript tests,
  and the source review against the 7.4 manual: nothing to change in `src`.

## Next

1. Bring tools and dependencies up to the newest release 7.4 allows, one at a time (full suite, one commit and
   one change-log entry each). The list is "What the 7.4 step unlocks" in [../HANDOFF.md](../HANDOFF.md):
   the `mongodb/mongodb`, `jms/serializer`, `azuyalabs/yasumi`, `doctrine/deprecations` and
   `phpstan/phpdoc-parser` locks and `nikic/php-parser` in the tools lock. Check each against the installed
   extension (1.19.4): the library must accept it, and a newer one may deprecate more. Also check the other
   pinned tools, services and base images for newer releases.
2. Write `notes/ENVIRONMENT.md`: what is at its newest and, for the rest, why not.
3. flat-webapp: the same, then its tests and Docker image on 7.4.
4. At the end: `composer audit --locked` on both locks, the dependency-alert note, the release preparation.

## Working in a cloud session

- Docker Hub can answer 429 on pulls; pull the service images one at a time, then use `--offline`.
- Behind the cloud proxy, set `FLAT_TEST_CA_FILE=/root/.ccr/ca-bundle.crt` for builds.
- `composer update --lock` rewrites some download URLs; keep the lock diff to real changes.

## Waiting for the owner

Q51 (the `php` requirement), Q52 (release numbering) and Q53 (MongoDB extension held at 1.19.4) in [KNOWN-DEFECTS.md](KNOWN-DEFECTS.md). Neither
blocks the next steps. Leave the root `composer.json` alone until Q51 is answered.
