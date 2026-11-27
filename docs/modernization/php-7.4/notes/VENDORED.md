# Vendored code, php-7.4 phase

The third-party libraries in `src/flat/vendor` are listed in
[../../php-7.3/notes/VENDORED.md](../../php-7.3/notes/VENDORED.md) (nothing there changed in this phase).
This note covers the one file that is not a bundled library but a corrected copy of a Composer dependency.

## The adapter's read-preference trait

- File: `src/flat/vendor/alcaeus/mongo-php-adapter/Helper/ReadPreference.php`, from
  `alcaeus/mongo-php-adapter` 1.2.5, `lib/Alcaeus/MongoDbAdapter/Helper/ReadPreference.php`.
- Why: the adapter is archived and its newest release uses what ext-mongodb 1.20 deprecates (Q53): the integer
  `ReadPreference::RP_*` constants, an integer mode in the constructor and `getMode()`. Every database object
  raised the deprecations.
- Change: string modes (the same values as `\MongoClient::RP_*`) and a new helper that reads the mode through
  `bsonSerialize()`. Each change is marked `flat (Q53)` in the file. Behavior is the same.
- How it loads: flat's root `composer.json` maps the namespace `Alcaeus\MongoDbAdapter\Helper\` to that folder.
  Composer tries the longer prefix first, so this file wins, and the adapter's other helper traits still load
  from the adapter. `tests/docker/runtime/composer.json` carries the same mapping, and
  `tests/Unit/Db/Mongo/ReadPreferenceTest.php` fails if the adapter's copy loads instead.
- Upgrading: if a newer adapter release exists, compare its trait with 1.2.5; if the deprecations are fixed
  upstream, delete this file and the two mappings.
