# Consumer impact

### MOD-343: PHP requirement

- Before: PHP 7.4 or later.
- After: PHP 8.0 or later. Consumers on older runtimes retain a PHP 7.4 release.
- Check deployment configuration and Composer platform pins for PHP 7.x.
- Search consumer code for: `PHP_VERSION_ID`, `PHP_MAJOR_VERSION`

### MOD-344: Event callbacks

- Before on PHP 7.4: handlers received positional arguments. The uncorrected
  code on PHP 8 raised unknown-named-parameter errors or exposed named variadic keys.
- After: the PHP 7.4 positional contract is preserved on PHP 8. Aggregate payload
  keys are retained. No consumer code change is needed.
- Search consumer code for: `\flat\core\event`, `\flat\event`, `set_handler`

### MOD-346: Native error handling and resource lifetime

- Before on PHP 7: invalid XPath argument types raised a warning; disabled
  proc_open raised a warning before flat's SSH syserror.
- After on PHP 8: XPath propagates native TypeError while restoring libxml state.
  SSH retains its syserror for unavailable proc_open; PHP's obsolete disabled-function
  warning is absent. Temporary cURL cookie jars are still deleted correctly.
- Search consumer code for: `simplexpath`, `proc_open`, `disable_functions`

### MOD-347: Reserved class renamed (Q63)

- Before: the empty abstract `flat\core\util\validate\match` base class existed.
- After: it is `flat\core\util\validate\match_rule`, because PHP 8 reserves match.
  External subclasses must update their parent name. No alias exists. Classes
  within the `flat\core\util\validate\match\...` namespace keep their names.
- Search consumer code for: `\flat\core\util\validate\match`
