# Consumer impact, php-7.4 phase

Behavior changes of this phase that an application using flat may notice, in the format of
[../../php-7.2/active/CONSUMER-IMPACT.md](../../php-7.2/active/CONSUMER-IMPACT.md): what consumers saw
before, what they see now, and what to search for in their code.

## Behavior changes

### MOD-292 (Q51) — flat requires PHP 7.4 or newer

- Before: the root `composer.json` required `php >=7.3.0`.
- After: it requires `php >=7.4.0`. A PHP 7.3 host keeps installing `v1.73.x`; Composer skips `v1.74.0` for it.
- Search consumer code for: `katmore/flat`, the `php` constraint and `config.platform.php` of your `composer.json`.
- Verified: a resolution under a simulated PHP 7.4.33 host succeeds.

### MOD-292 (Q54) — flat requires `doctrine/annotations`

- Before: flat did not require it. `jms/serializer` 3.29.1 (pulled in by the Authorize.Net SDK) required it, so
  a lock made earlier contains it. `jms/serializer` 3.32.9 does not require it, and on PHP below 8.0 it throws
  as soon as the SDK builds its serializer ("either ... the doctrine/annotations package installed, or use
  PHP 8.0 or later"): a fresh install on PHP 7.4 had a broken Authorize.Net SDK.
- After: `composer.json` requires `doctrine/annotations ^2.0`, so a fresh install works on 7.4. The package is
  marked abandoned on Packagist; Composer says so on install. On PHP 8 it is installed but not needed; the
  php-8.0 phase can drop the requirement.
- Search consumer code for: `doctrine/annotations`, `jms/serializer`, `authorizenet/authorizenet`.
- Verified: yes. A fresh resolution under a simulated PHP 7.4.33 host locks `doctrine/annotations` 2.0.2 with
  `jms/serializer` 3.32.9.

### MOD-295 (Q53) — flat carries a corrected copy of the Mongo adapter's read-preference trait

- Before: `alcaeus/mongo-php-adapter` 1.2.5 (archived) built every `MongoClient`, `MongoDB` and `MongoCollection` with
  `ReadPreference` integer constants, an integer mode and `getMode()`. With ext-mongodb 1.20 or newer each one raised
  a PHP deprecation ("Passing an integer mode to ... is deprecated", "Function ...::getMode() is deprecated").
- After: flat's copy of that one trait (`src/flat/vendor/alcaeus/mongo-php-adapter/Helper/ReadPreference.php`) uses
  string modes, so no deprecation is raised on any extension version. Behavior is the same. flat's `composer.json`
  maps the namespace `Alcaeus\MongoDbAdapter\Helper\` to it, so flat's copy loads instead of the adapter's.
- One visible side effect: an optimized autoloader (`composer dump-autoload -o`, `-a`, or `--optimize-autoloader`)
  prints "Ambiguous class resolution, Alcaeus\MongoDbAdapter\Helper\ReadPreference was found in both ... the first
  will be used". flat's copy is the one used. To silence the warning, add to your application's `composer.json`:
  `"autoload": {"exclude-from-classmap": ["vendor/alcaeus/mongo-php-adapter/lib/Alcaeus/MongoDbAdapter/Helper/ReadPreference.php"]}`.
- Search consumer code for: `Alcaeus\MongoDbAdapter\Helper\ReadPreference`, `MongoClient::RP_`, `getReadPreference`.
- Verified: yes. A Composer install of flat with the adapter loads flat's copy under the normal, optimized and
  authoritative autoloaders (the last two with the warning above). 13 new tests; any PHP message fails them.

### MOD-302 (KD-108) — `flat\core\util\validate\collection` is removed

- Before: the file src/flat/core/util/validate/collection.php existed, with an empty class that implemented
  `flat\core\collection\validator`, which does not exist. Loading the class was a fatal error ("Interface ... not found"),
  so no code could have used it.
- After: the file is gone. `class_exists('flat\\core\\util\\validate\\collection')` is `false` instead of a fatal error.
- Search consumer code for: `util\validate\collection`.
- Verified: yes. Nothing in flat's code or tests referenced it.

### MOD-303 (KD-107) — The MongoDB SSL classes are renamed to match their paths

- Before: the seven files under `src/flat/db/driver/mongo/ssl*` declared `flat\db\mongo\ssl...`. Composer could not find
  any of them by name; they worked only when a file was included by hand and the old name used.
- After: they declare `flat\db\driver\mongo\ssl...` (`ssl`, `ssl\cafile`, `ssl\cafile\base64`, `ssl\cafile\file`,
  `ssl\localcert`, `ssl\localcert\base64`, `ssl\localcert\file`) and load by name. **The old names no longer exist; no alias
  is kept.** Code that extended or checked the old names (`extends \flat\db\mongo\ssl\cafile\base64`, `instanceof
  \flat\db\mongo\ssl`) must use the new names and no longer needs to include the files by hand. flat itself used none of them.
- Search consumer code for: `db\mongo\ssl`, `mongo\ssl\cafile`, `mongo\ssl\localcert`, `get_cafile`, `get_localcert`.
- Verified: yes. A clean PHP process loads all seven by name and finds none of the old names.

### MOD-306 (Q58) — `subscription\cancelled` is now `subscription\canceled`

- Before: `src/flat/cloud/authorize/subscription/canceled.php` declared the class `cancelled`, with a `class_alias` for `canceled`
  and a forwarding file `cancelled.php`. Both names worked.
- After: the class is `flat\cloud\authorize\subscription\canceled`, the file's own spelling. The alias and the forwarding file are
  gone, so `flat\cloud\authorize\subscription\cancelled` no longer exists. The message text ("ARB is cancelled (subscriptionId: ...)")
  is unchanged. The class is deprecated since v1.73.1 and goes in v2.X.
- Search consumer code for: `subscription\cancelled`, `catch (` blocks naming `cancelled`.
- Verified: yes (the suite uses the one name).

### MOD-307 (Q58) — `ssh\exception\input_error\badParam` is now `bad_param`

- Before: the file `bad_param.php` declared the class `badParam` (camel case, against flat's style), with a `class_alias` for
  `bad_param`, the name `core/ssh.php` throws. Both names worked.
- After: the class is `flat\core\ssh\exception\input_error\bad_param`. The alias is gone, so `badParam` no longer exists. The
  message format ("input error: bad pid: invalid") is unchanged.
- Search consumer code for: `input_error\badParam`, `badParam`.
- Verified: yes. flat itself only ever threw `bad_param`.

### MOD-308 (Q58) — The session driver is `data\orm\driver\session` only

- Before: `src/flat/data/orm/driver/session.php` declared `flat\data\session\driver\session` extending `flat\data\session\driver`, which
  was a `class_alias` of the real base class `flat\data\orm\driver`; a second `class_alias` made the class also `flat\data\orm\driver\session`.
  Forwarding files `data/session/driver.php` and `data/session/driver/session.php` loaded it under the old names.
- After: the class is declared `flat\data\orm\driver\session` and extends `flat\data\orm\driver` directly. Both aliases and both
  forwarding files are gone: `flat\data\session\driver` and `flat\data\session\driver\session` no longer exist. `data/orm.php` and the
  handler classes already used the `orm` name.
- Search consumer code for: `data\session\driver`, `session\driver\session`.
- Verified: yes (the suite and a real native session across separate processes use the one name).

### MOD-309 (Q58) — `core\util\factory\exception\not_match_interface` is removed

- Before: the class lives in `flat\core\util\validate\factory\exception`; a `class_alias` and a forwarding file made the same class
  also answer to `flat\core\util\factory\exception\not_match_interface` (an older namespace without `validate`).
- After: only `flat\core\util\validate\factory\exception\not_match_interface` exists, which also matches its file. The alias and the
  forwarding file are gone. Nothing in flat used the old name.
- Search consumer code for: `util\factory\exception`, `not_match_interface`.
- Verified: yes (the fresh-process test loads the one name).
