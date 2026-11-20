# Data mapping, scalar wrappers, XML, and HTML testing

## Maintainer-confirmed behavior

- Data mapping defaults must work, including `default_on_empty`.
- Nonnumeric strings must survive `strings_to_scalar(true)` unchanged.
- PHP-empty scalar-wrapper inputs use `value_on_empty`; they are not preserved
  as raw scalar values. Fallbacks, including explicit zero, must be assigned.
- XPath node lists must be constructible and iterable, including child lists.
- XML decoding must read the serializer's output. The maintainer explicitly
  approved adding metadata so zero, false, empty strings, and null can round-trip.

## Implemented fixes and compatibility effects

### Data mapping defaults

`data/mapper.php` now iterates the supplied defaults rather than undefined `$prop`
and reads flags from the rule rather than undefined `$flags`. Missing/null values
use defaults through isset; explicit empty values use defaults only with the
`default_on_empty` flag. Existing prefix mapping and repeated mapping are tested.
This enables previously ineffective behavior and is an approved behavior change.

### Scalar fallback

`data/scalar.php` now passes the computed fallback through the normal data
constructor. Previously it assigned a local variable and left the public field
null. PHP-empty inputs (including 0, false, empty string, string zero, and empty
array) now receive the fallback. Unsupported nonempty inputs retain their old
null-field behavior. Tests cover defaults, explicit fallback types, nested scalar
wrappers, rule/array forms, display encoding, and serialization.

### XPath node lists

`core/xml/dom/nodelist.php` initializes its parent iterator rather than recursively
calling its own typed constructor. Children are passed as childNodes, matching
its DOMNodeList parameter. Tests cover indexed access, rejected noninteger
indices, absent indices, traversal/rewind, text-node keys, child lists, and repeated
XPath calls on the DOM wrapper. This replaces the old constructor TypeError.

### Flat XML 0.3 and decoding

`core/xml/encode.php` emits format version **0.3** (PHP remains **7.2.34**). This is
an explicit wire-format change, separate from the PHP modernization ladder:

- Root and child `fx:php-type` attributes identify PHP value types.
- Child `fx:php-key` and `fx:php-key-type` preserve keys, including empty keys,
  mixed case, invalid XML node-name keys, and numeric indices.
- The decoder distinguishes null, empty strings, booleans, integers, doubles,
  strings, arrays, and objects. Object values become stdClass, not arbitrary
  instances of class names supplied by XML. `assoc=true` converts decoded objects
  into arrays. Structure metadata nodes are ignored as metadata.
- String zero is serialized as text rather than treated as empty. Scalar text
  whitespace is preserved; the decoder removes only the writer's root formatting
  newline, not content whitespace.
- Older documents without the new attributes can be decoded using available
  xsi/meta/key/index attributes, but values already lost by the old writer cannot
  be recovered. Legacy fallback coverage is still being expanded.
- Unrelated XML formats return null. The existing DOM loader handles XML loading;
  previously unserialize called nonexistent getAttr/dom_to_object methods.

Round-trip fixtures cover scalar roots, nested arrays/objects, zero, false, empty
string, string zero, null, escaped text, leading/trailing whitespace, newline
content, numeric indices, empty keys, and assoc mode. Other existing XML behavior,
including the constant checksum of null and resource/binary limitations, has not
been silently redesigned. No claim is made that arbitrary resources or arbitrary
class instances can round-trip faithfully.

## Other findings and tests

- DOM conversion (separate from the new Flat XML decoder) still deep-copies the
  first repeated element. An empty first string therefore becomes null. This
  behavior is characterized, not declared intentional or corrected.
- Data HTML encoding clones the data object, but a nested plain stdClass remains
  shared and may be mutated. Tests record the aliasing; no deep-clone rewrite.
- Data stringify interfaces, type/lifecycle interfaces, field inspection, prefix
  mapping, escaping, UTF-8 conversion, and string-failure recovery are tested.
- HTML encoding is verified for scalar/type markers, nested arrays and objects,
  key/index attributes, custom tags/indentation, and resource dumps. Its explicit
  feature_not_ready unserializer remains unchanged.

## Latest verification

PHP 7.2.34 / PHPUnit 8.5.56 / Xdebug 2.9.8, Docker-only:
**264 tests passed, 2,236 assertions**, no skipped or failed tests.
Framework coverage: **15.06% lines (1,715/11,389)**,
**19.11% methods (203/1,062)**, **14.80% classes (49/331)**.
HTML encoding reaches **100% lines (95/95) and methods (7/7)**.
XML encoding/DOM and data mapping remain in progress; no exclusions hide their
remaining gaps. Reports are under `/workspace/flat-environment/php72-tools`.

The local run-tests helper now reuses the labeled `flat-unit-audit-php72`
container, avoiding repeated filesystem copies during container creation. It
starts/recreates that specific container as needed. No running process is assumed
to survive an environment restart; the image and helper are local prerequisites.

## Copying-related follow-up (MOD-022)

The approved preservation of falsy values in deepcopy fixes the repeated-node
conversion issue described above: an empty first value now remains an empty
string. The DOM grouping test was updated for that explicit behavior change.
This does not expand scalar-wrapper semantics: that wrapper still applies its
separately confirmed value_on_empty contract.

## MOD-049/050/052 completion checkpoint

XML encode **295/295 lines, 16/16 methods**, DOM **140/140, 22/22**, typed loader
**22/22, 4/4**, phone **119/119, 2/2**, all 100%. XML structure metadata quote was
fixed; impossible private guards are retained as comments with explicit proofs.
Resources expose a live warning/fallback path and a pre-existing undefined checksum
input. Legacy structure metadata keeps only the last member. Phone country-code
comparisons/extension extraction remain known issues; coverage does not certify
those parsing policies. PHP 7.2's fatal magic/native initialization constraints
were respected when replacing invalid fixture approaches with supported cases.

## Data dump invariant (MOD-076)

The fallback condition assigned the nonempty literal human, so it was always
true and the implicit function-end path could not execute. It is now an else;
the original condition remains in a CONSERVATIVELY DISABLED comment. Tests verify
that unknown dump types still produce the same human dump. Base flat/data field
listing returns null as before; no missing-field or HTML policy changed.
