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
