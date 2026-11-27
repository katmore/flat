# PHP 8.0 migration review

Authority: PHP's [incompatible changes](https://www.php.net/manual/en/migration80.incompatible.php)
and [deprecations](https://www.php.net/manual/en/migration80.deprecated.php).
The upstream documentation source was read during this review.

## Findings

- Event handler arguments use associative keys in call_user_func_array. PHP 8
  interprets those as named arguments. A PHP 8.0.30 probe confirms an unknown
  named parameter error for a handler whose parameter is named payload.
  Preserve positional delivery with array_values; retain keys inside aggregate data.
- Other call_user_func_array sites construct numeric argument lists. Their
  positional behavior does not need a migration workaround.
- cURL handles become CurlHandle objects. flat keeps its handles untyped and
  does not test them with is_resource. Process and stream resource checks remain valid.
- PHP 8.0 lint of source and tests found no parse errors. The full runtime suite
  remains necessary for inheritance checks and stricter internal argument handling.
- Source searches found no removed each/create_function calls, curly string offsets,
  libxml_disable_entity_loader or deprecated OpenSSL free calls.
- Nullable array parameters preceding required arguments use the documented
  Type $arg = null exception and are not deprecated on PHP 8.0. Preserve their
  public signatures rather than introduce an unrelated API change.

## Modernization boundaries

Use string predicates for boolean checks; retain strpos where the offset is used.
Use match where the input is already an integer, preserving existing messages.
Remove unused catch bindings. Use object ::class where the value is known to be
an object. Promote private constructor storage without changing parameter names.
Do not add restrictive public types or change numeric-string comparison behavior
just to use new syntax. Preserve approved error-reporting exceptions.
