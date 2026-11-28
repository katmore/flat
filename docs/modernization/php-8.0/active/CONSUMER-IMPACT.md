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

### MOD-362: SSH crypto tooling

- Before in the test environment: the standalone OpenSSL command came from the
  archived PHP base; SSH's isolated crypto build was OpenSSL 3.6.5.
- After: the standalone command and isolated SSH build use OpenSSL 4.1.0.
  PHP's embedded crypto is unchanged. Authentication, host pins and cleanup
  retain their contract; native OpenSSL error wording may follow the new tool.
- Search consumer code for: `\flat\core\ssh`, `openssl`

### MOD-363: Patched YAML release streams

- Before: Composer on PHP 8.0 could select unpatched Symfony YAML 6.0.19.
- After: PHP 8.0 selects patched 5.4.57; newer PHP hosts may select the allowed
  patched 6.4, 7.4 or 8.1 streams. Explicit pins to vulnerable versions must change.
- Inspect symfony/yaml constraints in application Composer manifests and locks.
- Search consumer code for: `Symfony\Component\Yaml`

### MOD-366: Literal flags and safe class names (Q65, Q66)

- Before on PHP 7: a `0` option value matched any nonnumeric string flag;
  booleans could also match flags on PHP 7 and PHP 8. This could unexpectedly
  strip HTML, silence template errors, execute statements or destroy sessions.
- After: only the exact string enables a bare named flag. An associative
  setting such as `['execute'=>true]` keeps its documented behavior; an
  unrelated setting such as `['on_empty'=>0]` does not enable `strip`.
- Search for option/flag arrays passed to `prepare_map`, `start`, `format`,
  `display`, `html`, field accessors, data `map`/`dump`, asset flag helpers and
  `curl\response`. Replace accidental coercion with the intended string flag.
- Before: make_safe_classname returned unescaped match, mixed, several earlier
  reserved names and magic constants, which could not declare PHP 8.0 classes.
- After: those names receive the existing underscore suffix, preserving case.
- Search for `make_safe_classname`, generated class names and persisted names.

### MOD-366: Failed cURL construction

- Before: a constructor failure leaked its temporary cookie jar (and, for
  saved responses, its temporary download).
- After: temporary files are removed before rethrowing the original failure;
  caller-owned cookie and download files remain caller-owned.
- Search for `curl\response`, `accept_cookies`, `save_cookies`, `save`.

### MOD-367: PHP 8.0 Composer resolution (Q64)

- Before: a fresh PHP 8.0 resolution with Composer's default security policy
  fails: every compatible AWS/MongoDB version has one of four advisories.
  An existing lock install or a dismissed GitHub alert does not solve this.
- After: the owner accepts only those four advisory IDs for this phase. The
  runtime harness applies them to resolution blocking only; audits still report
  all four. Other advisories continue to block installation.
- Consumers must add the same exception to their **root** composer.json.
  A library cannot impose its security policy on the consuming application.
- Copy the config fragment in [dependency alerts](../notes/DEPENDENCY-ALERTS.md)
  into the application's existing config; preserve its other settings. Review
  and remove these exceptions when moving to PHP 8.1 and patched dependencies.
- Search for `config.audit`, `aws/aws-sdk-php`, `mongodb/mongodb` and locked
  package versions. Existing locks must still be audited.
