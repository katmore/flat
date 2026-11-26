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
    stays in major 1. Recommend yes, as decision D1 did for 7.3. **Open.**

52. **Release numbering: `v1.74.0` and then patch releases, tagged by the owner, with flat-webapp's lock
    moved to the published release after the tag?** Recommend yes. **Open.**

53. **The MongoDB extension stays at 1.19.4 (not 1.20.1, the newest that runs on PHP 7.4). Agree?** From
    1.20.0 the extension deprecates `ReadPreference` with an integer mode and `ReadPreference::getMode()`.
    The third-party `alcaeus/mongo-php-adapter` 1.2.5 (its newest release, and archived) does both for
    every database object, so 47 tests fail on a deprecation that flat cannot fix and must not hide. flat's
    legacy `Mongo` driver (`src/flat/db/driver/mongo.php`) is built on that adapter. Recommend: keep 1.19.4
    until the adapter is replaced or patched, and decide that with the legacy driver's future (v2.X). In
    flat-webapp the question does not arise: its image has no MongoDB extension. Whichever way, a consumer on PHP 7.4
    with extension 1.20 or newer sees these deprecations from the adapter. **Open.**
