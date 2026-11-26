# flat v1.73.2

A patch release of the PHP 7.3 line. It fixes the bundled Markdown and JavaScript libraries, which were years out of
date, and makes the Markdown converter safe by default.

## Requirements

PHP 7.3 or newer. PHP 7.2 hosts keep installing v1.72.2.

## Markdown

- **Raw HTML in Markdown is now escaped by default.** `convert::string_to_html()`, `new convert()` and `convert\file`
  treat the text as untrusted: `<script>` and other HTML show as text, `javascript:` and `data:` addresses are removed,
  and `on*` attributes are dropped. For text you wrote yourself, pass `['safe' => false]` to let HTML through. flat's own
  template files (`.md`, `.txt`, `.md.php`) are not affected.
- **Parsedown 1.8.0 and ParsedownExtra 0.9.0** (were 1.5.0 and 0.6): an image address containing a quote can no
  longer inject an attribute. Markdown follows the usual rules more closely: an ordered list keeps its first number,
  a different bullet starts a new list, and a blank line between quotes makes two quotes. Typed entities such as
  `&copy;` are shown literally in safe mode.

## JavaScript minifier

- **JShrink 1.8.1** (was 1.1.0). Modern JavaScript is no longer corrupted: template literals, arrow functions, `?.`, `??`
  and classes now minify correctly, and `a++ + ++b` keeps its meaning. The minified text differs a little from before.

## Other

- `fixUTF8()` of a number, boolean or null now returns it unchanged (forceutf8 2.0.4).
- Test environment: `symfony/yaml` 5.4.57, Composer 2.11.3, and a fix for the RabbitMQ startup failure.

Each behavior change has a "before, after, what to search for" entry in
`docs/modernization/php-7.3/active/CONSUMER-IMPACT.md`.
