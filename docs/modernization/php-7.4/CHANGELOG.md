# Change log, php-7.4 phase

Every change of this phase: what, why, effect on behavior, how it was verified, and how to undo it. Numbering
continues from the php-7.3 phase.

## MOD-280 — The php-7.4 phase folder

- Kind: documentation. `docs/modernization/php-7.4/` has the same skeleton as the earlier phases; the owner's
  brief for the phase is saved there as `HANDOFF.md`. The Progress list of `docs/modernization/README.md`
  marks php-7.3 complete and php-7.4 in progress. `AGENTS.md` points to this phase: its work plan, the PHP
  version (7.4) and the branch to push to. No rule changed.
- The owner created flat's `php-7.4` branch at `673df9f` (`master`, `v1.73.2`).
- Behavior: none.
- Verified: on `673df9f` before any change, `docker/bin/test all --coverage` passed 1,624 tests and 6,954
  assertions on PHP 7.3.33; `docker/bin/check-docs`.
- Undo: revert the commit.

## MOD-281 — The test image moves to PHP 7.4.33

- Kind: environment. `docker/Dockerfile` builds on `php:7.4-cli`, pinned
  `sha256:620a6b9f4d4feef2210026172570465e9d0c1de79766418d3affd09190a7fda5`, and checks for PHP 7.4.33. The
  image is `flat-tests-php74`. `tests/Unit/PhpVersionTest.php` requires 7.4. Documents and script comments that
  name the runtime say 7.4. Extensions, locks, Composer platform pins (still 7.3.33) and the root
  `composer.json` are unchanged.
- Behavior: none (no library code changed).
- Verified: the image builds and its check passes (PHP 7.4.33, PCRE 10.35, `error_reporting` `E_ALL`).
  `docker/bin/test all`: 1,624 tests, 6,902 assertions, **3 failures**, all in `Tools/ApiDumpTest`, because
  `tests/tools/apidump.php` uses the deprecated `ReflectionType::__toString()`; not fixed yet. `php -l` on all
  754 tracked PHP files reports nothing (a sample with a nested ternary and a curly-brace offset does report).
  Coverage and `docker/bin/test-javascript` were not run on 7.4.
- Undo: revert the commit.

## MOD-282 — The API dump tool reads types without the deprecated string cast

- Kind: runtime compatibility (test tooling). `type_string()` in `tests/tools/apidump.php` cast a
  `ReflectionType` to a string, which PHP 7.4 deprecates. It now calls `ReflectionNamedType::getName()`, which
  gives the same text on PHP 7.1 and newer (the leading `?` for a nullable type is still added by the tool).
- Behavior: none for flat; the tool's output is unchanged.
- Verified: under PHP 7.4.33 the fixed tool dumped `src` and `compare` against
  `../php-7.3/notes/API-V1.73.2.json` (made on 7.3.33) reports no differences. The two files differ in one
  stored message only: the fatal error text for `flat\core\util\validate\collection`, a class that cannot
  load (already so in the 7.3 dump), which PHP 7.4 words "Uncaught Error: Interface ... not found". No
  declaration differs. `docker/bin/test all` on 7.4.33: 1,624 tests, 6,935 assertions, all pass, no notice or deprecation. (7.3.33
  counted 6,954 assertions; the 19 fewer are not explained yet and are listed in the session handoff.)
- Undo: revert the commit.
