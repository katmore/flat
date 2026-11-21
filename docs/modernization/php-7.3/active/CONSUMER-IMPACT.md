# Consumer impact, php-7.3 phase

Behavior changes of this phase that an application using flat may notice, in the format of
[../../php-7.2/active/CONSUMER-IMPACT.md](../../php-7.2/active/CONSUMER-IMPACT.md): what
consumers saw before, what they see now, and what to search for in their code.

## Behavior changes

### MOD-196 (KD-80) — JavaScript minifier keeps the character after a regex starting with a quote

- Before: the minifier (`\flat\vendor\tedious\JShrink\minifier`) dropped one character after a
  regex literal whose first character is a quote, for example `return /'a'/.test(s);` came out as
  `return /'a'/test(s);`, and `return /'q'/; foo();` lost its `;`. The minified JavaScript was broken.
- After: every character is kept: `return /'a'/.test(s);` and `return /'q'/;foo();`.
- Search consumer code for: `JShrink`, `minifier::minify`, and any minified JavaScript with a regex
  literal that starts with `'` or `"`; re-minify those files.
- Verified: yes (tests fail without the fix).

### MOD-202 (KD-83) — `bad_dim` message includes the reason

- Before: `flat\core\util\image\exception\bad_dim` always said `bad dimension specified: .`.
- After: `bad dimension specified: width must be numeric.` (or `height`, or `... must evaluate to an
  integer 0 or greater.`). Same class, same code, same conditions.
- Search consumer code for: `bad_dim`, code or logs that match the old message text.
- Verified: yes.

### MOD-202 (KD-85) — Mapper exception names the missing property

- Before: asking the mapper for a property that does not exist threw `cannot_get_property` with
  an empty name: `mapper cannot get property:  reason: does not exist`.
- After: `mapper cannot get property: missing reason: does not exist`. Same class and condition.
- Search consumer code for: `cannot_get_property`, `ignore_cannot_get`, code that matches the old text.
- Verified: yes.

### MOD-218 (KD-89) — JavaScript comments preserve program behavior

- Before: `var/**/result=1` became `varresult=1`, `return/**/1` became `return1`, and
  `x+/**/+x` became invalid `x++x`. Empty-comment text inside quoted strings was deleted.
- After: comments preserve needed token separators and line breaks; quoted comment text is
  unchanged. A line break inside a block comment after `return` still ends that return statement.
- Search consumer code for: `JShrink`, `minifier::minify`, and generated JavaScript containing
  block comments or quoted `/**/`. Re-minify affected assets.
- Verified: PHPUnit output regressions and Docker JavaScript execution checks fail without the fix.

## No behavior change

- MOD-189 (environment) changes no behavior a caller can see.
- One thing consumers can notice, which is not a flat change: the root `composer.json` now
  requires `php >=7.3.0` (it was `>=7.2.1`). A PHP 7.2 host keeps installing `v1.72.x`; Composer
  skips `v1.73.0` for it.
