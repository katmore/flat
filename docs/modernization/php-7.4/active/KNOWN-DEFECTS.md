# Known defects and owner questions, php-7.4 phase

Numbering continues from the [previous phase](../../php-7.3/active/KNOWN-DEFECTS.md): defects start at KD-107, questions at Q51.

## Defects found in this phase

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

### KD-109 - Files whose declared class name differs from their path (sixteen in all)

- Category: A in effect, and **fixed**. A check of every file in `src/flat` (outside `vendor`) against the name its path implies
  found sixteen that declared something else. Each was then loaded by name under PHP 7.4.
  - Seven are the MongoDB SSL files of KD-107 (MOD-303).
  - Three were `class_alias` names: `subscription\canceled`, `ssh ... bad_param` and `data\orm\driver\session` (MOD-306 to MOD-308, Q58).
  - Two loaded under neither name: `flat\fsm` (declared `flat\fsm\fsm`) and `flat\orm\invalid_field` (declared `invalid_param`)
    (MOD-312, MOD-313, Q57).
  - Four were the real code of `api\response_header\exception`, `...\exception\missing_request_field` (declared `flat\api\header\...`)
    and `cloud\authorize\customer\payment\bankAccount` and `creditCard` (declared `...\payment_profile\...`), each with a
    one-line `require_once` file at the path of its declared name, which made both names work. **Fixed (MOD-314):** the owner's
    rule is that the path decides, so they are declared as their own path says, the forwarding files are deleted, and the
    references (`api/response_header.php`, three tests, a docblock) follow. No alias.
- A mistake of mine, corrected: the entries MOD-301, MOD-312 and MOD-313 of the change log, and the notes of the same days, called these
  four "stray duplicates whose real classes load from their own files" and offered to delete them. That was backwards: the files at
  the declared names were the stubs and the code lived in the oddly named files. Nothing was deleted on that basis.

### KD-110 - A cURL request with data never sent its body

- Category: A, **fixed (MOD-321)**. Found by real-HTTP tests (MOD-320). `flat\core\curl\response` set the request method and the headers
  `Content-Type` and `Content-Length: N` for a request with data, but never `CURLOPT_POSTFIELDS`. A real server therefore waited for
  N bytes that never came: the request hung until the server gave up (30 seconds against WireMock; the configured timeout is 300).
  The old test only looked at the options the fake recorded, so it passed. Fix: the body is set.

### KD-111 - The `filetime` of a cURL response was always -1

- Category: A, **fixed (MOD-321)**. The class read `CURLINFO_FILETIME` into its public `filetime` but never set `CURLOPT_FILETIME`, so
  libcurl answered -1 whatever the server sent. The old fake returned an invented 123. Fix: the option is set; `filetime` is the
  server's `Last-Modified` time, or -1 when the server sends none.

### KD-112 - The robots.txt of a server on another port was fetched from the wrong place

- Category: A, **fixed (MOD-321)**. `is_robots_allowed()` fetched `scheme://host/robots.txt` and dropped the port, so a site served
  on a port other than 80 or 443 had its robots rules read from a different server (or none). Fix: the port is kept.

### KD-113 - Every key login printed a shell error to standard error

- Category: A, **fixed (MOD-326)**. Found by the real SSH tests (MOD-325). `flat\core\ssh` makes the temporary passphrase of a key login with
  `tr ... | head -c128`. PHP ignores `SIGPIPE`, so when `head` closes the pipe `tr` reports "write error: Broken pipe" instead of dying quietly,
  and the message went to the standard error of every key login. Fix: `tr`'s own standard error is discarded in that one command; the passphrase
  is unchanged.

### KD-114 - A connected SSH client was never destroyed before the script ended

- Category: A, **fixed (MOD-326)**. Found by the real SSH tests. `connect()` gave the extension a disconnect callback written as a closure, which
  binds `$this`; the extension keeps the callback as long as the session, so the object and its connection kept each other alive, and
  `__destruct()` (which disconnects) did not run until the PHP process ended. A long-running script that opened many connections never closed them.
  Fix: the callback is `static`.

## Owner questions

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

59. **`missing_curlinfo` (curl_getinfo returning false) cannot happen with a real transfer. Remove it?** `response.php` threw
    `flat\core\curl\exception\missing_curlinfo` when `curl_getinfo($ch)` returned `false`, but libcurl answers an array for any finished
    transfer, so the branch and the exception class were unreachable in practice, and testing them needed a PHP function override
    of a cURL function. **Answered 2026-11-27: remove them** (MOD-323).
