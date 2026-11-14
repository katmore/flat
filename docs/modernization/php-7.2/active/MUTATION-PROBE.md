# Mutation probe, php-7.2

Record of the TS-05 mutation probe: what it found, what was fixed, and what is
left. The tool is described in [../../TESTING.md](../../TESTING.md); this file
is a record, so it is edited by hand when a follow-up closes an item.

## Result

- **First sample:** 4 runs of 100 mutants (seeds 1 to 4) over all 620 source files,
  unit suite only. 394 valid mutants (6 did not compile): **289 caught, 105 survived** (73.4% caught).
- **The probe excluded the MongoDB and RabbitMQ tests**, so survivors in code only they cover were
  partly false alarms. Re-running every mutant on the 21 affected lines with the services running
  (`docker/bin/mutate --services`) caught 24 of 34.
- The 98 distinct source lines that had a survivor were each read in context and sorted:
  **30 now pinned by tests, 14 equivalent, 54 not yet addressed at the time; 31 lines still open after the follow-up below** (TS-06 in [KNOWN-DEFECTS.md](KNOWN-DEFECTS.md)).
- After the new tests, re-running the same lines caught every mutant except the equivalent ones.

## What the probe found

- **Real defects** the characterization tests had been locking in: KD-71 (calling a UUID object ignored its arguments),
  KD-72 (`$rand()` ignores its constructed bounds, question 25), KD-73 (config keys starting with `flat/` were cut
  from the wrong end), KD-74 (the IP cache lifetime comment says 1 day, the constant is 7, question 26) and
  KD-75 (a Mongo guard that could never be true).
- **A guard whose test could not fail.** `kill_process()` validates the pid before building a shell command. The test only
  checked that *an* exception was thrown, and a later check threw the same one, so removing the guard went unnoticed even
  though the unvalidated pid had already been sent to the shell. The test now asserts nothing was executed.
- **Tests that masked the code under test:** a fixture set `session.use_cookies` itself (so the code that sets it was
  unobserved), and assertions that two calls differ held whether or not the option under test was honored.
- **Untested boundaries:** cache expiry at exactly its time to live, the shutdown weight ceiling, the first ten keys the XML
  type detection looks at, wait loops that were only ever run with a wait of 0.
- **Message formats:** several exceptions were only checked for being thrown, not for what they say.

## Fixed: pinned by a new or stronger test

| File | Line | Mutation(s) | Test |
| --- | --- | --- | --- |
| `api/response/bad_request.php` | 40 | `1` to `0` | ResponseTest trace-offset tests; two equivalent variants remain |
| `api/response/bad_request.php` | 51 | `+` to `-` | ResponseTest::testGeneratedMessagesNameTheClassThatCreatedTheResponse |
| `api/response/forbidden.php` | 39 | `0` to `1` | ResponseTest::testDefaultTraceOffsetNamesTheDirectCaller |
| `api/response/forbidden.php` | 42 | `+` to `-`, `1` to `0` | ResponseTest trace-offset tests |
| `cloud/ipinfodb.php` | 163 | `>=` to `>` | IpInfoDbTest::testCachedRecordExpiresExactlyAtItsTimeToLive |
| `core/config.php` | 422 | `0` to `1` | ConfigTest::testPathsAreNormalizedAndBaseNamesAreRemoved (KD-73) |
| `core/config.php` | 423 | `0` to `1` | ConfigTest::testPathsAreNormalizedAndBaseNamesAreRemoved (KD-73) |
| `core/controller/tmpl.php` | 80 | `true` to `false` | Template ControllerTest::testCheckingADesignNeverPrintsIt |
| `core/curl/response.php` | 115 | `1` to `0` | Curl ResponseTest::testRobotsRulesForAnotherAgentIgnoreTheUserAgentLabel |
| `core/exception.php` | 71 | `0` to `1` | ExceptionTest::testExplicitAndDerivedCodesAndMetadata |
| `core/mapper.php` | 350 | `||` to `&&` | MapperTest::testParamsWithNumericKeysAreAPositionalList |
| `core/rand/exception/bad_param.php` | 36 | `!` to `(removed)`, `!` to `(removed)` | RandTest::testBadParamMessageNamesTheParameterAndOptionalDetails |
| `core/rand/str.php` | 230 | `true` to `false` | RandStrTest::testDefaultPoolDrawsFromDigitsAndBothCases |
| `core/shutdown.php` | 171 | `>` to `>=` | ShutdownTest::testWeightCeilingAllowsExactlyTheRemainingSlots |
| `core/ssh.php` | 449 | `false` to `true` | SshTest::testKillArgumentsAreValidatedBeforeSendingSignals |
| `core/ssh.php` | 493 | `-` to `+` | SshTest::testKillWaitsForTheFirstSignalBeforeSendingTheSecond |
| `core/ssh.php` | 513 | `>=` to `>` | SshTest::testAProcessThatNeverExitsReportsTheTimeWaited |
| `core/status/exception/feature_not_ready.php` | 59 | `1` to `0` | ExceptionTest (caller class and plain-function tests); one equivalent variant remains |
| `core/xml/encode.php` | 461 | `>` to `>=` | Xml EncodeTest::testArrayTypeDetectionInspectsTheFirstTenKeys |
| `data/orm.php` | 63 | `&&` to `||` | LegacyOrmTest::testADriverNeedsBothGetAndSet |
| `data/phone.php` | 202 | `||` to `&&` | PhoneTest::testInternationalNumbersGetTheirCountryCode (more prefixes) |
| `data/phone.php` | 223 | `||` to `&&`, `||` to `&&`, `||` to `&&` | PhoneTest::testInternationalNumbersGetTheirCountryCode (more prefixes) |
| `db/driver/mongo.php` | 289 | `0` to `1` | Mongo DriverTest::testPingTimestampWithoutAnOffsetIsNow (services) |
| `db/driver/mongo.php` | 350 | `+` to `-` | Mongo DriverTest::testRefreshedPingKeepsTheConfiguredOffset (services) |
| `db/driver/mongo.php` | 704 | `!` to `(removed)` | Mongo CrudTest::testClientOptionsChooseTheServer (services); the condition was also fixed (KD-75) |
| `db/driver/pdo.php` | 226 | `false` to `true` | Pdo ConnectionTest::testConnectReturnsAnIsolatedConnectionOnlyWhenForced |
| `db/driver/pdo.php` | 227 | `true` to `false` | Pdo ConnectionTest::testConnectReturnsAnIsolatedConnectionOnlyWhenForced |
| `db/driver/pdo/statement.php` | 258 | `0` to `1` | Pdo StatementTest::testInvalidAliasedColumnNamesAreRejected |
| `event/alert.php` | 208 | `+` to `-` | AlertTest::testListenerDecodesMessagesAndRefreshesExpiryOnTimeout |
| `route/rule.php` | 61 | `true` to `false` | Route ControllerTest::testRulesIterateOverTheRouteByDefault |

## Equivalent: no observable effect

| File | Line | Mutation(s) | Why it cannot be observed |
| --- | --- | --- | --- |
| `api/status.php` | 397 | `0` to `1` | unreachable: an unknown code has no class, so the whole format is already empty before this line |
| `asset.php` | 1313 | `true` to `false` | only changes the entropy of a generated unique id |
| `cloud/authorize/AuthorizeNetCIM.php` | 37 | `true` to `false` | switches libxml error handling process-wide; no test can observe it without side effects on other tests |
| `core/collection.php` | 97 | `&&` to `||` | short-circuit evaluation makes the changed operator unreachable for every input |
| `core/mapper.php` | 16 | `false` to `true` | `ignore_cannot_set` is never read from this defaults array |
| `core/ssh.php` | 202 | `0` to `1` | `$cv` only drives a progress value that is computed and never used |
| `core/util/time_ago.php` | 121 | `1` to `0` | differs only for fractional-second timestamps; whole seconds behave the same |
| `core/uuid.php` | 206 | `true` to `false` | `(int)` makes the value an integer, so strict and loose `in_array` agree |
| `db/driver/mongo.php` | 381 | `1` to `0` | the server answers `ping: 0` and `ping: 1` alike |
| `db/driver/mongo/crud.php` | 361 | `1` to `0` | `!empty($param["limit"])` already skips 0, so `< 1` and `< 0` behave the same |
| `db/driver/pdo.php` | 174 | `false` to `true` | the persistent attribute of a SQLite memory connection cannot be read back |
| `db/driver/rabbitmq.php` | 198 | `false` to `true` | `mandatory` only matters for unroutable messages on a channel with a return handler; the tests publish to the default exchange |
| `db/driver/rabbitmq.php` | 199 | `false` to `true` | same as `mandatory`: `immediate` has no observable effect here |
| `lib/exception/missing_required_param.php` | 61 | `!` to `(removed)` | the second branch of the same condition already accepts booleans |

## Not yet addressed

Survivors that were read but neither pinned by a test nor shown to be equivalent. They are mostly formatting details, internal defaults
and option plumbing. Each needs a look at what the line is for before choosing between a test and an equivalence note.

| File | Line | Mutation(s) | Source line |
| --- | --- | --- | --- |
| `asset.php` | 493 | `!` to `(removed)` | `!empty($realpath = realpath($system_path)) && $system_path = $realpath;` |
| `asset.php` | 728 | `false` to `true` | `$filterByPosition = false;` |
| `asset.php` | 855 | `===` to `!==` | `count($asset_dependency) === 1 && $asset_dependency = [` |
| `asset.php` | 887 | `true` to `false` | `if (in_array($html_position_type, self::VALID_POSITIONS, true)) {` |
| `asset.php` | 890 | `||` to `&&` | `if (is_string($html_position_type) \\|\\| is_callable($html_position_type)) {` |
| `asset.php` | 1084 | `<` to `<=` | `for ($i = 0; $i < $level_count; $i++) {` |
| `asset.php` | 1290 | `&&` to `||` | `if (isset($depends_asset['depends']) && is_array($depends_asset['depends'])) {` |
| `asset.php` | 1355 | `!` to `(removed)`, `&&` to `||` | `if (!isset($param['hash']) && (!isset($param['hash_handle_prefix']) \\|\\| !is_string($param['hash_h` |
| `asset.php` | 1363 | `false` to `true` | `$resolved = false;` |
| `asset.php` | 1365 | `!==` to `===` | `if ($resource !== null \\|\\| !$resolved) {` |
| `cli/error.php` | 54 | `&&` to `||` | `if (!empty($data) && is_array($data)) {` |
| `cloud/authorize/subscription/schedule.php` | 113 | `!` to `(removed)` | `if (!empty($this->startDate) && is_string($this->startDate)) {` |
| `core/cli.php` | 153 | `0` to `1` | `if (!is_int($resource_idx) \\|\\| ($resource_idx<0)) $resource_idx=1;` |
| `core/cli.php` | 266 | `0` to `1` | `if (substr($p,0,1)!="-") {` |
| `core/cli.php` | 285 | `false` to `true` | `if (false !== (strpos($p,$char_alias))) $expect_char_val = true;` |
| `core/cli.php` | 394 | `1` to `0` | `if (!$shortopt \\|\\| (strlen($argsub) === 1)) {` |
| `core/cli.php` | 436 | `true` to `false` | `$flag['-'] = true;` |
| `core/config.php` | 177 | `true` to `false`, `true` to `false` | `self::$ref_searched[$basename][$ns]=true;` |
| `core/config.php` | 639 | `&&` to `||` | `if (is_file($filename) && is_readable($filename) && (pathinfo($filename,\PATHINFO_EXTENSION)=='php')` |
| `core/config.php` | 694 | `true` to `false` | `if (in_array($file,self::$loaded)) return true;` |
| `core/controller/api.php` | 92 | `true` to `false` | `$error_callback_invoked = true;` |
| `core/controller/asset/deprecated_methods_trait.php` | 29 | `&&` to `||` | `if (is_array($param) && !empty($param['flag']) && is_array($param['flag'])) {` |
| `core/controller/tmpl.php` | 396 | `&&` to `||` | `if (is_file($file) && is_readable($file)) {` |
| `core/curl/exception/xpath_error.php` | 51 | `false` to `true` | `if (!empty($query) && is_string($query) && (false===strpos($details,$query))) {` |
| `core/curl/response.php` | 284 | `false` to `true` | `$accept_cookies = false;` |
| `core/debug.php` | 147 | `false` to `true` | `private static $_suppress=false;` |
| `core/html/encode.php` | 175 | `0` to `1`, `<` to `<=` | `for ($i = 0; $i < $size * $level; $i++)` |
| `core/html/encode.php` | 229 | `&&` to `||` | `false === filter_var($key, FILTER_VALIDATE_INT) && $html .= "<span data-item-key>" . htmlspecialchar` |
| `core/util/deepcopy.php` | 37 | `false` to `true` | `$objects_to_stdClass = false;` |
| `core/util/validate/value.php` | 49 | `false` to `true` | `'exception'=>false` |
| `core/xml/dom.php` | 399 | `false` to `true` | `$doc->preserveWhiteSpace = false;` |
| `core/xml/encode.php` | 293 | `true` to `false` | `'string'=>true,` |
| `core/xml/encode.php` | 294 | `true` to `false` | `'DateTime'=>true,` |
| `core/xml/encode.php` | 327 | `1` to `0` | `if (is_numeric(substr($attr,0,1))) $attr="hash_".$attr;` |
| `core/xml/encode.php` | 506 | `true` to `false` | `$last_type = self::_get_xsi_type($v,['string'=>true]);` |
| `core/xml/encode.php` | 515 | `1` to `0` | `private static function _data_to_xml($data, $default_node, $indent_level=1, $indent_size=3, $dump_ok` |
| `core/xml/encode.php` | 649 | `false` to `true` | `if ($xml !== null && $xml !== false) return $xml;` |
| `core/xml/encode.php` | 661 | `>` to `>=` | `return "<$default_node$checksum_attr><!--unserializable--></$default_node>";` |
| `data/orm/driver/session.php` | 81 | `false` to `true` | `\flat\core\config::get("data/orm/driver/session/handler",['default'=>false]),$param` |
| `data/scalar.php` | 89 | `&&` to `||` | `is_array($value->data) &&` |
| `data/typed/loader.php` | 86 | `!` to `(removed)` | `if (isset($data['type']) && (empty($type) \\|\\| !is_string($type))) {` |
| `db/driver/mongo.php` | 743 | `===` to `!==` | `if ($options===null) $options=[];` |
| `db/driver/mongo/crud.php` | 64 | `&&` to `||` | `if (!empty($param['count']) && isset($res['n'])) {` |
| `db/driver/mongo/crud.php` | 110 | `!` to `(removed)` | `if (!empty($option['collection_ns'])) {` |
| `db/driver/mongo/crud.php` | 217 | `!` to `(removed)` | `if (!is_object($data) && !is_array($data)) {` |
| `db/driver/mongo/crud.php` | 223 | `!` to `(removed)` | `if (!empty($param['count']) \\|\\| !empty($param['not_modified_exception'])) {` |
| `db/driver/mongo/crud.php` | 340 | `&&` to `||` | `if (!empty($f) && !empty($s) && is_string($f) && is_string($s)) {` |
| `db/driver/mongo/crud.php` | 430 | `>` to `>=` | `if ($limit>1) {` |
| `db/driver/mongo/crud.php` | 438 | `==` to `!=` | `if ($type == "assoc") {` |
| `db/driver/mongo/crud.php` | 465 | `==` to `!=` | `if ($type == "dataclass") {` |
| `db/driver/mongo/crud.php` | 523 | `0` to `1` | `$skip = 0;` |
| `db/driver/mongo/crud.php` | 556 | `1` to `0` | `(count($opt['start'])!=1)` |
| `db/driver/rabbitmq.php` | 172 | `!` to `(removed)` | `} elseif (!isset($param['queue'])) {` |
| `db/driver/rabbitmq.php` | 228 | `!` to `(removed)` | `if (!$param) return md5("");` |

## Limits of the probe

- Mutations are token swaps (comparisons, logic, arithmetic, `true`/`false`, `0`/`1`, negation removal). Whole-statement
  mutations (deleting a call, changing a return value) are not generated, so a high catch rate does not mean every
  behavior is pinned.
- It samples. The first sample covered 400 of 2,732 candidate mutants; a different `--seed` gives a different sample.
- Without `--services` the MongoDB and RabbitMQ tests do not run.

## Reproduce

```sh
docker/bin/mutate --limit=100 --seed=1 > build/tests/mutate-seed1.md        # unit suite
docker/bin/mutate --services --at=src/flat/db/driver/mongo.php:350 src/flat/db   # one line, with services
```

## TS-06 follow-up

On 2026-11-14 all 54 lines of the table above were probed again (109 mutants, services running, seed 1) after the round of new tests: **68 killed, 41 still survive** on the lines below. One caution: an earlier spot check of `cli.php` used a path that matched no file, so its lines were not actually re-probed then; this run covers them. Triage so far: `core/xml/encode.php` 293 and 294 set the options `string` and `DateTime` to `true`, which is already their default (equivalent); `core/config.php` 694 (an already loaded file returns early) can only be observed if a file named `.php` exists in the config folder (equivalent in practice). The rest are open.

| File | Line | Mutation | Source line |
| --- | --- | --- | --- |
| `asset.php` | 728 | `false` to `true` | `$filterByPosition = false;` |
| `asset.php` | 855 | `&&` to `||` | `count($asset_dependency) === 1 && $asset_dependency = [` |
| `asset.php` | 855 | `===` to `!==` | `count($asset_dependency) === 1 && $asset_dependency = [` |
| `asset.php` | 855 | `1` to `0` | `count($asset_dependency) === 1 && $asset_dependency = [` |
| `asset.php` | 887 | `true` to `false` | `if (in_array($html_position_type, self::VALID_POSITIONS, true)) {` |
| `asset.php` | 1084 | `++` to `--` | `for ($i = 0; $i < $level_count; $i++) {` |
| `asset.php` | 1084 | `<` to `<=` | `for ($i = 0; $i < $level_count; $i++) {` |
| `asset.php` | 1290 | `&&` to `||` | `if (isset($depends_asset['depends']) && is_array($depends_asset['depends'])) {` |
| `asset.php` | 1355 | `&&` to `||` | `if (!isset($param['hash']) && (!isset($param['hash_handle_prefix']) \|\| !is_string($param['hash_handle_prefix']` |
| `asset.php` | 1355 | `||` to `&&` | `if (!isset($param['hash']) && (!isset($param['hash_handle_prefix']) \|\| !is_string($param['hash_handle_prefix']` |
| `asset.php` | 1355 | `!` to `(removed)` | `if (!isset($param['hash']) && (!isset($param['hash_handle_prefix']) \|\| !is_string($param['hash_handle_prefix']` |
| `asset.php` | 1355 | `!` to `(removed)` | `if (!isset($param['hash']) && (!isset($param['hash_handle_prefix']) \|\| !is_string($param['hash_handle_prefix']` |
| `asset.php` | 1363 | `false` to `true` | `$resolved = false;` |
| `asset.php` | 1365 | `||` to `&&` | `if ($resource !== null \|\| !$resolved) {` |
| `asset.php` | 1365 | `!==` to `===` | `if ($resource !== null \|\| !$resolved) {` |
| `asset.php` | 1365 | `!` to `(removed)` | `if ($resource !== null \|\| !$resolved) {` |
| `cli/error.php` | 54 | `&&` to `||` | `if (!empty($data) && is_array($data)) {` |
| `core/cli.php` | 153 | `1` to `0` | `if (!is_int($resource_idx) \|\| ($resource_idx<0)) $resource_idx=1;` |
| `core/cli.php` | 153 | `||` to `&&` | `if (!is_int($resource_idx) \|\| ($resource_idx<0)) $resource_idx=1;` |
| `core/cli.php` | 436 | `true` to `false` | `$flag['-'] = true;` |
| `core/config.php` | 177 | `true` to `false` | `self::$ref_searched[$basename][$ns]=true;` |
| `core/config.php` | 639 | `&&` to `||` | `if (is_file($filename) && is_readable($filename) && (pathinfo($filename,\PATHINFO_EXTENSION)=='php')) {` |
| `core/config.php` | 694 | `true` to `false` | `if (in_array($file,self::$loaded)) return true;` |
| `core/controller/api.php` | 92 | `true` to `false` | `$error_callback_invoked = true;` |
| `core/controller/asset/deprecated_methods_trait.php` | 29 | `&&` to `||` | `if (is_array($param) && !empty($param['flag']) && is_array($param['flag'])) {` |
| `core/controller/asset/deprecated_methods_trait.php` | 29 | `&&` to `||` | `if (is_array($param) && !empty($param['flag']) && is_array($param['flag'])) {` |
| `core/controller/tmpl.php` | 396 | `&&` to `||` | `if (is_file($file) && is_readable($file)) {` |
| `core/util/deepcopy.php` | 37 | `false` to `true` | `$objects_to_stdClass = false;` |
| `core/util/validate/value.php` | 49 | `false` to `true` | `'exception'=>false` |
| `core/xml/dom.php` | 399 | `false` to `true` | `$doc->preserveWhiteSpace = false;` |
| `core/xml/encode.php` | 293 | `true` to `false` | `'string'=>true,` |
| `core/xml/encode.php` | 294 | `true` to `false` | `'DateTime'=>true,` |
| `core/xml/encode.php` | 327 | `0` to `1` | `if (is_numeric(substr($attr,0,1))) $attr="hash_".$attr;` |
| `core/xml/encode.php` | 327 | `1` to `0` | `if (is_numeric(substr($attr,0,1))) $attr="hash_".$attr;` |
| `core/xml/encode.php` | 506 | `true` to `false` | `$last_type = self::_get_xsi_type($v,['string'=>true]);` |
| `core/xml/encode.php` | 515 | `1` to `0` | `private static function _data_to_xml($data, $default_node, $indent_level=1, $indent_size=3, $dump_ok=true,$che` |
| `core/xml/encode.php` | 515 | `true` to `false` | `private static function _data_to_xml($data, $default_node, $indent_level=1, $indent_size=3, $dump_ok=true,$che` |
| `core/xml/encode.php` | 649 | `false` to `true` | `if ($xml !== null && $xml !== false) return $xml;` |
| `core/xml/encode.php` | 661 | `>` to `>=` | `return "<$default_node$checksum_attr><!--unserializable--></$default_node>";` |
| `data/orm/driver/session.php` | 81 | `false` to `true` | `\flat\core\config::get("data/orm/driver/session/handler",['default'=>false]),$param` |
| `data/scalar.php` | 89 | `&&` to `||` | `is_array($value->data) &&` |
