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

### MOD-219 (KD-90) — HTML parsing preserves libxml reporting

- Before: cURL DOM parsing and Markdown HTML blocks enabled libxml error capture globally.
  Their HTML diagnostics and errors from later, unrelated XML operations could go unreported.
- After: both HTML parsers respect the caller's reporting mode and existing error buffer.
  With normal PHP reporting enabled, HTML parser warnings reach the caller. This includes
  libxml's warning for HTML5 tags such as `section`; rendered Markdown remains the same unless
  the caller's warning handler interrupts it. cURL invalid XPath still throws `xpath_error`
  with its diagnostic, and restores the caller's reporting mode on success or failure.
- Search consumer code for: `use_dom`, `get_dom`, `simplexpath`, Markdown HTML blocks,
  `libxml_use_internal_errors`, and warning handlers around those operations.
- Verified: state/buffer and warning regressions fail before the fix; output assertions retained.

## No behavior change

- MOD-189 (environment) changes no behavior a caller can see.
- One thing consumers can notice, which is not a flat change: the root `composer.json` now
  requires `php >=7.3.0` (it was `>=7.2.1`). A PHP 7.2 host keeps installing `v1.72.x`; Composer
  skips `v1.73.0` for it.
