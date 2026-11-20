# Debug, template, text, and helper coverage

## Debug termination and platform formatting

`tests/Unit/Core/DebugTest.php` tests suppression/config gates, events, caller
information, supported error levels, formatting, and actual process termination.
Kill tests launch the same PHP_BINARY with the current Composer bootstrap. Child
shutdown writes genuine Xdebug execution data; PHPUnit's coverage collector
appends that raw data through its normal API. No hit counts are invented. Tests
also run without coverage; child collection is conditional on Xdebug availability.
Temporary scripts/reports are removed. A child-local namespaced PHP_SAPI constant
exercises web-format selection; that is a unit platform fixture, not an HTTP test.

Debug reaches **152/152 lines, 13/13 methods (100%)**. Three original unreachable
fallback statements are retained in CONSERVATIVELY DISABLED comments: generic
label fallback (every caller supplies a string label/default), non-object
backtrace object handling (native backtrace entries are objects or absent), and
an empty non-scalar/non-array/non-object value other than null (no such PHP 7.2
value exists). Formatting and kill-option issues remain deferred as documented;
coverage is line/method coverage and does not prove every boolean branch.

## Templates and failures

Template tests include real temporary PHP/HTML/HTM/Markdown/generated-Markdown
files, large chunked output, class designs, flags/replacements, logging,
constructor/prepared interfaces and absolute filenames. Controlled namespaced
I/O primitives return failures for open/read/close/content reads; successful paths
use native functions. Test-owned handles are closed and buffers restored.

Corrections found by these tests: absolute PHP path resolution used null $file
instead of $design; display-class resolution referenced the wrong namespace;
flat/tmpl/display declared a class extending an interface instead of implementing
it. The fixes retain existing interfaces and PHP 7.2. Template controller is
**186/191 lines (97.38%)**, so further coverage remains.

## UTF-8, Markdown, and forms

The UTF-8 vendor converter is **85/85 lines, 11/11 methods (100%)**. Tests use
actual byte sequences (valid 2/3/4-byte UTF-8, malformed prefixes, Windows-1252,
Latin-1), arrays, BOMs, encoding labels, and the actual iconv extension.

Markdown grammar tests cover headers/attributes, code, nested/loose lists, quotes,
rules, HTML, definitions, abbreviations, repeated footnotes, references/images,
URLs/email, emphasis/escaping, aligned tables, parser options and repeated use.
Parser source is not upgraded; the prior dynamic-dispatch correction is MOD-032.

Form helper is **36/36 lines, 4/4 methods (100%)**. Tests check escaped attributes
and values, textareas, nested type maps, unsupported types, and unsupported decode.
Its serialize method currently discards the constructed form; raw labels/debug
comments and broader HTML safety require later review. Coverage is not approval
of those behaviors.

## Library parameters and exception metadata

Library parameter helpers reach **45/45 lines, 7/7 methods (100%)**. Nonempty-list
recursion previously passed the entire key list back into itself; it now checks
each key. Single missing-class dependency failures now pass the normalized class
array to the existing typed exception. Both defects failed tests before repair.
Falsy scalar inputs retain existing nonempty-required behavior.

Exception metadata getter now reads its stored property instead of an undefined
local. Tests cover nested associative/list payloads and supplied/derived codes.
Core exception is **11/11 lines, 3/3 methods (100%)**. Feature-not-ready context
reporting is **22/22 lines (100%)** across explicit/object/class/method/function
and caller contexts.

## Checkpoint

Docker PHP 7.2.34, PHPUnit 8.5.56, Xdebug 2.9.8: **649 tests / 3,371 assertions**,
all passing. Framework lines **55.35% (5,204/9,402)**, methods **54.14%
(575/1,062)**, classes **38.37% (127/331)**. No exclusions were added. Denominator
changes include replacement of static estimates by loaded-runtime counts.

Template completion (MOD-042): configured/fallback design roots and controlled
Markdown read failure bring the controller to **191/191 lines, 16/16 methods**.
