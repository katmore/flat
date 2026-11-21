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

## No behavior change

- MOD-189 (environment) changes no behavior a caller can see.
- One thing consumers can notice, which is not a flat change: the root `composer.json` now
  requires `php >=7.3.0` (it was `>=7.2.1`). A PHP 7.2 host keeps installing `v1.72.x`; Composer
  skips `v1.73.0` for it.
