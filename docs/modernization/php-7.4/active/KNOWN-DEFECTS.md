# Known defects and owner questions, php-7.4 phase

KD-01 to KD-106 are all resolved; the records are
[php-7.2](../../php-7.2/active/KNOWN-DEFECTS.md) and [php-7.3](../../php-7.3/active/KNOWN-DEFECTS.md). A
defect found in this phase starts here as KD-107 and follows "How each fix lands" in the php-7.2 file.
Questions continue the same numbering.

## Defects found in this phase

Found on 2026-11-27 by looking into the eight classes the API tool reports as "cannot be loaded". Both were seen in
the php-7.2 phase and written down there as "untested and unusable", but never recorded as defects or put to the owner.

### KD-107 - The MongoDB SSL classes declare the wrong namespace and cannot be autoloaded

- Category: A in effect. Seven files in `src/flat/db/driver/mongo/` (six classes and the interface `ssl.php`:
  `ssl/cafile.php`, `ssl/cafile/base64.php`, `ssl/cafile/file.php`, `ssl/localcert.php`,
  `ssl/localcert/base64.php`, `ssl/localcert/file.php`) declare `namespace flat\db\mongo\ssl...`, but the path says
  `flat\db\driver\mongo\ssl...`. flat has no autoloader of its own, so Composer cannot find any of them by name
  (`Class 'flat\db\mongo\ssl\cafile' not found`). They work only when a file is included by hand, which is what
  `tests/Unit/Db/Mongo/CaFileTest.php` does (its comment calls autoload "a follow-up").
- Nothing in flat uses them: the interface `flat\db\mongo\ssl` ("indicate to controller that mongo connection is
  ssl") is not checked by the MongoDB driver (`src/flat/db/driver/mongo.php` has no reference to SSL).
- The owner decided on 2026-11-27 (Q55): fix the declarations and every reference directly, with no `class_alias`: a
  rename, not a shim.
- Fixed (MOD-303): the seven files declare `flat\db\driver\mongo\ssl...` (and the interface `flat\db\driver\mongo\ssl`),
  `CaFileTest` no longer includes files by hand, and a clean-process test checks that all seven load by name and that the
  old names are gone.
- Seen and not changed: `localcert\base64` and `localcert\file` extend the `cafile` classes, not `localcert`, so the
  abstract `_get_passphrase()` of `localcert` is never required of them.
- **Fixed.**

### KD-108 - `util\validate\collection` implements an interface that does not exist

- Category: C (unfinished stub), needs the owner. src/flat/core/util/validate/collection.php is
  `class collection extends \flat\core\collection implements \flat\core\collection\validator {}`: an empty body, and
  no `flat\core\collection\validator` anywhere. The nearest real thing is the interface
  `flat\core\collection\member\validator` (methods `member_validate`, `member_filter`, `member_success`), which the
  collection controller checks. Nothing references the class: it is not used in `src`, the tests or the docs, and the
  file's header has no purpose text. It cannot be loaded at all (fatal error).
- The owner decided on 2026-11-27 (Q56): remove it; it was an over-composed idea that was never finished.
- Fixed (MOD-302): the file is removed.
- **Fixed.**

### KD-109 - Files whose declared class name differs from their path (sixteen in all, seven are KD-107)

- Category: mixed. A check of every file in `src/flat` (outside `vendor`) against the name its path implies found sixteen
  that declare something else; each was then loaded by name under PHP 7.4. The API tool lists them as "no declaration".
  - Seven are the MongoDB SSL files of KD-107.
  - Three were `class_alias` names that worked: `subscription\canceled` (declared `cancelled`), `ssh ... bad_param` (declared
    `badParam`) and `data\orm\driver\session`. **Fixed (MOD-306 to MOD-308):** each is declared under its path's name and the
    aliases are gone (Q58).
  - Four are stray files whose real class loads from its own file, so nothing is lost:
    `api\response_header\exception`, `api\response_header\exception\missing_request_field` (declare `api\header\...`),
    `cloud\authorize\customer\payment\bankAccount` and `...\creditCard` (declare `...\payment_profile\...`). Loading
    their path name by hand while the real class is loaded is a fatal "Cannot declare class".
  - Two loaded under neither name: `src/flat/fsm.php` declared the empty abstract class `flat\fsm\fsm` and
    `src/flat/orm/invalid_field.php` declared `flat\orm\invalid_param`. **Fixed (MOD-312, MOD-313):** the owner chose the path
    names (Q57), so they are `flat\fsm` and `flat\orm\invalid_field`, with no alias.
- Still open: the four stray duplicate files. Their real classes load from their own files, so nothing is lost; the stray
  files could be deleted, which is for the owner to say (it removes files, not classes).

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

55. **Fix KD-107 (the MongoDB SSL classes' namespace) now, as a separate commit of this phase?** **Answered 2026-11-27:
    yes, by correcting the declarations and references, with no `class_alias`** (MOD-303).

56. **What was `flat\core\util\validate\collection` meant to be (KD-108)?** **Answered 2026-11-27: remove it** (MOD-302).

57. **Which names were intended for `src/flat/fsm.php` and `src/flat/orm/invalid_field.php` (KD-109)?** **Answered 2026-11-27:
    the path names** (`flat\fsm`, `flat\orm\invalid_field`), declared as such with no alias (MOD-312, MOD-313).

58. **Remove the five `class_alias` calls already in flat?** **Answered 2026-11-27: yes, all five, with no alias and every
    reference fixed** (MOD-306 to MOD-309). The names kept are `canceled`, `bad_param`, `data\orm\driver\session` (extending
    `data\orm\driver` directly) and `core\util\validate\factory\exception\not_match_interface`. `AGENTS.md` now forbids
    `class_alias` (MOD-310).
