# Known defects and owner questions, php-7.4 phase

KD-01 to KD-106 are all resolved; the records are
[php-7.2](../../php-7.2/active/KNOWN-DEFECTS.md) and [php-7.3](../../php-7.3/active/KNOWN-DEFECTS.md). A
defect found in this phase starts here as KD-107 and follows "How each fix lands" in the php-7.2 file.
Questions continue the same numbering.

## Defects found in this phase

None.

## Owner questions

Each has a recommendation; "agree" is enough.

51. **Should the root `composer.json` require `php >=7.4.0` (it requires `>=7.3.0`)?** A PHP 7.3 host would
    keep installing `v1.73.x`; Composer skips a release whose `php` requirement does not match. The version
    stays in major 1. Recommend yes, as decision D1 did for 7.3. **Answered 2026-11-27: yes (MOD-292).**

52. **Release numbering: `v1.74.0` and then patch releases, tagged by the owner, with flat-webapp's lock
    moved to the published release after the tag?** Recommend yes. **Answered 2026-11-27: yes.**

53. **The MongoDB extension was held at 1.19.4 because 1.20 deprecates what the archived
    `alcaeus/mongo-php-adapter` does. Fix it now?** The owner answered on 2026-11-27: fix it at its cause, with a
    corrected copy of the one class in flat's tree. **Resolved (MOD-295, MOD-296):** flat carries a corrected
    copy of the adapter's read-preference trait ([../notes/VENDORED.md](../notes/VENDORED.md)); the extension is
    1.20.1, the newest that builds on PHP 7.4 (1.21 needs 8.1); all tests pass with no deprecation.

54. **Should flat's root `composer.json` require `doctrine/annotations` (`^2.0`) for PHP below 8.0?** flat
    requires `authorizenet/authorizenet ^1.9`, which pulls in `jms/serializer` (any of `^1|^2|^3`). From
    `jms/serializer` 3.32 that package no longer requires `doctrine/annotations`, and on PHP below 8.0 it
    throws when the SDK builds its serializer (found in MOD-287: 39 errors until the test manifest required
    it). A consumer who runs a fresh `composer update` on PHP 7.4 gets the new serializer and a broken
    Authorize.Net SDK; one who keeps an existing lock (flat-webapp locks 3.29.1) does not. Recommend yes,
    in the same step as Q51, so the root `composer.json` changes once. Until then flat-webapp's application
    lock keeps its packages. **Answered 2026-11-27: yes (MOD-292).**
