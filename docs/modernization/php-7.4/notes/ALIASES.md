# class_alias in flat

The five `class_alias` calls that `src/flat` (outside `vendor`) had on 2026-11-27, kept as a record. **All five were removed**
(MOD-306 to MOD-309, Q58); `AGENTS.md` now forbids `class_alias`. The paths below are as they were and several no longer exist. All five are written up in the php-7.2 change log, made in that phase by AI
sessions while making the classes loadable and tested; whether the owner approved the pattern then is not recorded. The
owner dislikes it (2026-11-27); the question is Q58 in [../active/KNOWN-DEFECTS.md](../active/KNOWN-DEFECTS.md). No
alias was added in this phase, and the MongoDB SSL classes were renamed without one (MOD-303).

## 1. `subscription\canceled`

- Where: `src/flat/cloud/authorize/subscription/canceled.php`, line 49:
  `class_alias(cancelled::class, __NAMESPACE__.'\\canceled')`.
- What: the file `canceled.php` declares the class `cancelled` (two l's, the spelling in its own error message). The
  alias adds `canceled`. `cancelled.php` is a forwarding file: it only requires `canceled.php`.
- Used: `canceled` only in `tests/Unit/Cloud/AuthorizeDataTest.php` (an `assertInstanceOf`); nothing in `src`.
- Written up: MOD-093. The class is deprecated since v1.73.1 and goes in v2.X with the other Authorize.Net wrappers.
- Removal: pick one spelling, rename the class or the file, update the test. It is in the part that goes in v2.X anyway.

## 2. `ssh\exception\input_error\bad_param`

- Where: `src/flat/core/ssh/exception/input_error/bad_param.php`, line 43.
- What: the file `bad_param.php` declares the class `badParam`; the alias adds `bad_param`.
- Used: `core/ssh.php` throws `bad_param` in four places; `tests/Unit/Core/SshTest.php` expects it. `badParam` is used only
  by one test, `tests/Unit/Core/LegacyErrorMessagesTest.php`.
- Written up: MOD-091 ("preserve legacy `badParam` while supplying the `bad_param` name used by callers").
- Removal: rename the class to `bad_param` (the file name and the callers already say so). Anyone who caught `badParam`
  would break; nothing in flat does.

## 3. `data\orm\driver\session`

- Where: `src/flat/data/orm/driver/session.php`, line 112: `class_alias(session::class, 'flat\data\orm\driver\session')`.
- What: that file declares `flat\data\session\driver\session`, while its path, its `handler` and `exception` subfolder
  (five more files) and `data/orm.php` (line 42, the fallback driver) all say `flat\data\orm\driver\session`.
  src/flat/data/session/driver/session.php is a forwarding file that requires it.
- Used: both names; `data/orm.php` and the subfolder use the `orm` name, the test `SessionDriverTest` checks both.
- Written up: MOD-092 ("restore both legacy session-driver names").
- Removal: the `orm` name is the one the path and the code use; declare it, delete the forwarding file.
  `flat\data\session\driver\session` would stop existing.

## 4. `data\session\driver`

- Where: src/flat/data/session/driver.php, line 3: `class_alias(\flat\data\orm\driver::class, 'flat\data\session\driver')`.
- What: a file with only that line. The class in item 3 extends `flat\data\session\driver`, which did not exist; this makes
  it an alias of the real base class `flat\data\orm\driver` (an abstract class).
- Used: only by item 3 and its test.
- Written up: MOD-092.
- Removal: goes with item 3: once the class is declared as `flat\data\orm\driver\session` extending `flat\data\orm\driver`,
  this file has no reason to exist.

## 5. `core\util\factory\exception\not_match_interface`

- Where: `src/flat/core/util/validate/factory/exception/not_match_interface.php`, line 52.
- What: the class lives in `flat\core\util\validate\factory\exception`. The alias adds the same name without `validate`,
  an older namespace; src/flat/core/util/factory/exception/not_match_interface.php is a forwarding file for it.
- Used: `tests/Unit/Core/Util/ValidationTest.php` (both names); nothing in `src`.
- Written up: MOD-097 ("retain the historical declared name via alias/forwarding file").
- Removal: delete the alias and the forwarding file; update the test. Code that used the old namespace would break.

## Notes

- Items 3 and 4 belong together, and item 1 goes with the Authorize.Net removal in v2.X. Items 2 and 5 are independent.
- The php-7.2 log also records a deliberate choice not to alias `flat\orm\invalid_param` (declared in `orm/invalid_field.php`):
  "No class aliases or fake classes are used". That file is part of KD-109.
