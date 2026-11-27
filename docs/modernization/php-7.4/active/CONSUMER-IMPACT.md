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

### MOD-312 (Q57) — `flat\fsm` is declared as `flat\fsm`

- Before: `src/flat/fsm.php` (the "finite state machine definition", 0.1.0-alpha, an empty abstract class implementing
  `flat\core\app` and `flat\core\routable`) declared `flat\fsm\fsm`. The autoloader looks for `flat\fsm` in that file, so neither
  name could be loaded.
- After: the class is `flat\fsm`, the name its file and its own header say. No alias.
- Search consumer code for: `flat\fsm`, `fsm\fsm`.
- Verified: yes. Nothing in flat used it.

### MOD-313 (Q57) — `flat\orm\invalid_param` is now `flat\orm\invalid_field`

- Before: `src/flat/orm/invalid_field.php` declared `flat\orm\invalid_param`, so `flat\orm\invalid_field` did not exist and
  `invalid_param` loaded only when the file was included by hand.
- After: the class is `flat\orm\invalid_field`, and it loads by name. `invalid_param` no longer exists. The message
  ("invalid field: name, reason: ...") and the `get_field_key()` and `get_reason()` methods are unchanged. No alias.
- Search consumer code for: `orm\invalid_param`, `invalid_param`, `orm\invalid_field`.
- Verified: yes. Nothing in flat used either name.

### MOD-314 (KD-109) — The API header exceptions are `api\response_header\exception`

- Before: the code of `flat\api\response_header\exception` and `...\exception\missing_request_field` lived under `api/response_header/`
  but declared `flat\api\header\exception` and `flat\api\header\exception\missing_request_field`; forwarding files under `api/header/`
  made those names load. `response_header.php` threw `header\exception\missing_request_field` when the field name is empty.
- After: the classes are `flat\api\response_header\exception` and `flat\api\response_header\exception\missing_request_field`, next to
  `etag.php`. The forwarding files are gone; `flat\api\header\...` no longer exists. Nothing else changed: the message is still
  "missing request field".
- Search consumer code for: `api\header\exception`, `missing_request_field`, `catch` blocks naming `header\exception`.
- Verified: yes (`tests/Unit/Api/ResponseTest.php` expects the new name).

### MOD-314 (KD-109) — The customer payment classes are `customer\payment\creditCard` and `bankAccount`

- Before: the code of the two classes lived in `customer/payment/` and extended `customer\payment`, but declared
  `flat\cloud\authorize\customer\payment_profile\creditCard` and `...\bankAccount`; forwarding files under `customer/payment_profile/`
  made those names load. A docblock in `customer.php` named the `payment_profile` classes.
- After: they are `flat\cloud\authorize\customer\payment\creditCard` and `...\bankAccount`. The forwarding files are gone and
  `...\customer\payment_profile\creditCard` and `...\bankAccount` no longer exist (`customer\payment_profile`, the profile class
  itself, is unchanged). The classes are deprecated since v1.73.1 and go in v2.X with the other Authorize.Net wrappers.
- Search consumer code for: `payment_profile\creditCard`, `payment_profile\bankAccount`.
- Verified: yes (the payment-method and transaction tests use the new names).

### MOD-315 — Seven files that declared nothing are deleted

- Before: src/flat/cloud/disqus.php and five files under src/flat/cloud/pci/ were 0 bytes, and src/flat/orm/missing_required_field.php
  held only `<?php`. No code, test or document used them.
- After: they are gone. Code that included one by path would get a missing-file error instead of nothing.
- Search consumer code for: `cloud/disqus`, `cloud/pci`, `cloud\pci`, `missing_required_field`.
- Verified: yes (nothing in flat referenced them).

### MOD-317 — src/flat/event/alert/listener.php is deleted

- Before: the file held the license header, a `namespace flat\event\alert;` line and the old pthread `listener` class as comments, kept
  by the owner's decision of MOD-107 "for historical reference". It declared no class (a test asserts `flat\event\alert\listener` does not exist).
- After: the file is gone; git history has the old code. `flat\event\alert\listener` still does not exist. `alert::add_listener` (the
  supported, synchronous API) is unchanged.
- Search consumer code for: `alert\listener`, `event/alert/listener`.
- Verified: yes (the same test passes).

### MOD-321 (KD-110) — A cURL request with data now sends its body

- Before: `new flat\core\curl\response` with a request that has data (`new request($url, ['field'=>'value'])`) sent the method, `Content-Type` and
  `Content-Length: N` but no body, so the server waited for N bytes (a hang until its own timeout). No application could have relied on that.
- After: the body (`$request->data`) is sent.
- Search consumer code for: `curl\request(`, `curl::exec(` with data, `request_method`.
- Verified: yes (a real server receives `value=1&text=a+b` with a matching `Content-Length`; JSON data with `application/json`).

### MOD-321 (KD-111) — `response->filetime` is the server's Last-Modified time

- Before: `filetime` was always -1.
- After: it is the `Last-Modified` time of the response as a Unix timestamp, or -1 when the server sends none.
- Search consumer code for: `->filetime`.
- Verified: yes (a real server's `Last-Modified` gives its timestamp).

### MOD-321 (KD-112) — `is_robots_allowed()` keeps the port

- Before: for `http://host:8080/page` it fetched `http://host/robots.txt`.
- After: it fetches `http://host:8080/robots.txt`.
- Search consumer code for: `is_robots_allowed`, `use_robots`.
- Verified: yes (a real server on a non-standard port receives the robots request).

### MOD-322 — `ipinfodb` has an overridable base URL

- Before: the API URL was only the constant `ipinfodb::api_base_url`.
- After: the protected method `_get_api_base_url()` returns that constant by default; a subclass may override it (the tests point it at a test server).
  Nothing changes for code that does not override it.
- Search consumer code for: `ipinfodb`.
- Verified: yes.

### MOD-323 (Q59) — `flat\core\curl\exception\missing_curlinfo` is removed

- Before: `response.php` threw `missing_curlinfo` ("curl_getinfo returned no transfer information") when `curl_getinfo()` returned `false` for a
  finished transfer. With a real transfer it never does, so nothing could have seen this exception.
- After: the check and the class are gone; `$response->info` is what `curl_getinfo()` returns.
- Search consumer code for: `missing_curlinfo`.
- Verified: yes (nothing in flat referenced it; the suite passes at 100% coverage).
