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
- MOD-283: both Composer platform pins are 7.4.33.
- MOD-284: full suite with coverage on 7.4.33, the JavaScript tests, and the source review against the 7.4
  manual: nothing to change in `src`.
- MOD-285: ext-mongodb 1.19.4. The brief's 1.21.1 needs PHP 8.1; 1.20.1 is the newest that builds, but it
  deprecates what `alcaeus/mongo-php-adapter` does (Q53).
- MOD-286 to MOD-289: `mongodb/mongodb` 1.19.1, `jms/serializer` 3.32.9 (with its dependencies; it needs
  `doctrine/annotations` required explicitly), `azuyalabs/yasumi` 2.6.0, `nikic/php-parser` 5.9.0.
- MOD-290: [../notes/ENVIRONMENT.md](../notes/ENVIRONMENT.md). flat's own tools and dependencies are at their
  newest 7.4-compatible releases.

## Next

1. flat-webapp (`php-7.4` branch, untouched): move its Docker image and test setup to 7.4.33, bring its tools
   and dependencies to the newest 7.4 releases one at a time, and run its tests (67 at the start). Its lock
   still pins flat `v1.73.2`; the release of flat 7.4 comes later. Follow its `AGENTS.md`.
2. At the end of the phase: `composer audit --locked` on both flat locks and flat-webapp's, the
   dependency-alert note, and the release preparation (Q51 and Q52 first).

## Working in a cloud session

- Docker Hub can answer 429 on pulls; pull the service images one at a time, then use `--offline`.
- Behind the cloud proxy, set `FLAT_TEST_CA_FILE=/root/.ccr/ca-bundle.crt` for builds.
- `composer update --lock` rewrites some download URLs; keep the lock diff to real changes.

## Waiting for the owner

Q51 (the `php` requirement), Q52 (release numbering) and Q53 (MongoDB extension held at 1.19.4) in [KNOWN-DEFECTS.md](KNOWN-DEFECTS.md). Neither
blocks the next steps. Leave the root `composer.json` alone until Q51 is answered.
