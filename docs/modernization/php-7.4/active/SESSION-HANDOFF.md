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

## Next

1. Release preparation, like php-7.3: release notes for `v1.74.0` (the owner tags; Q52), the consumer check, and a note
   on the "Ambiguous class resolution" warning that optimized autoloaders print (see MOD-295 in
   [CONSUMER-IMPACT.md](CONSUMER-IMPACT.md)).
2. End of the phase: `composer audit --locked` on both flat locks and flat-webapp's, and the dependency-alert note
   (the same four advisories are expected: their fixes need PHP 8.1).
3. After the tag: move flat-webapp's application lock to the published release and bring its packages to the newest
   7.4 releases, one at a time (its lock still holds `jms/serializer` 3.29.1 and `mongodb/mongodb` 1.16.1).

## Waiting for the owner

Nothing. Q51 to Q54 are answered. The owner tags the release.
