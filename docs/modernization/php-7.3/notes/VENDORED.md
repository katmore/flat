# Vendored libraries

Third-party code bundled in `src/flat/vendor`. It is updated like every other dependency, from upstream, with
flat's changes kept to the lines listed here. Each change is also marked in the file.

| Library | Version | Upstream | Path |
| --- | --- | --- | --- |
| forceutf8 | 2.0.4 | neitanod/forceutf8 | `src/flat/vendor/neitanod/forceutf8` |
| Parsedown | 1.8.0 | erusev/parsedown | `src/flat/vendor/erusev/src/Parsedown` |
| ParsedownExtra | 0.9.0 | erusev/parsedown-extra | `src/flat/vendor/erusev/src/ParsedownExtra` |
| JShrink | not yet updated | tedious/JShrink | `src/flat/vendor/tedious/JShrink` |

`src/flat/vendor/azuyalabs/yasumi/dateQuery.php` is flat's own code, not a copy of a library.

## flat's changes

- forceutf8: `namespace flat\vendor\neitanod\forceutf8;` replaces `namespace ForceUTF8;`. Nothing else.
- Parsedown: the fix of `elementApplyRecursiveDepthFirst()` (upstream called the plural method for one element).
- ParsedownExtra: `libxml_use_internal_errors(true)` removed from `processTag()` so the caller's libxml error mode is kept
  (KD-90); the constructor's version guard is commented out (it compares two constants and cannot throw).
- The wrappers `src/flat/vendor/erusev/src/Parsedown.php`, `ParsedownExtra.php` and the loaders `parsedown.php` and
  `parsedown/extra.php` are flat's own.

## Upgrading one

1. Get the newest upstream release that supports the phase's PHP version.
2. Replace the file, then re-apply the changes above.
3. Compare old and new output on a set of inputs and record what differs in `active/CONSUMER-IMPACT.md`.
4. Cover the new code (the suite requires 100%), run `docker/bin/test all --coverage`, add a change-log entry.
