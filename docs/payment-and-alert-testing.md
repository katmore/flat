# Payment and alert testing

## Authorize.Net baseline

The diagnostic environment installs the existing locked SDK version 1.9.9 in its
separate PHP 7.2.34 runtime dependency directory. No root Composer change, SDK
upgrade, credentials, or payment endpoint calls are involved. Real SDK DTOs verify
merchant credentials and response parsing; explicit controller/CIM doubles record
calls at the network boundary.

`AuthorizeTest` covers base success, rejection, missing response and exception
behavior, plus parameter and response-error accessors. The legacy response_error
constructor ignores its second argument and copies its first argument into both
code and text; this is labeled current behavior.

`AuthorizeCustomerTest` covers customer profile mapping and ID aliases, creation
validation modes, payment object forwarding, malformed arguments, profile updates,
payment deletion and error propagation. Unimplemented transaction conveniences
are characterized as no-ops, not claimed as working payment features.

Discovered blockers awaiting the maintainer's support/retirement decision:

- profile_Customer uses isset on null SDK fields and drops supplied email/details.
- Payment arrays try to construct an abstract payment_profile class.
- create_paymethod/update_paymethod reference IDs that were never assigned.
- Transaction array normalization never sets customer_transaction_type.
- The subscription declaration refers to nonexistent interfaces and leaves
  abstract credential methods unimplemented in a nonabstract class.
- Legacy customer client setup calls a removed preparation helper.

The modern SDK spells its response DTO ANetApiResponseType; tests use that exact
case so Composer classmap autoload works before the class has been loaded.

## Alerts and queue transport

Alert provider interfaces make persistence and channel delivery boundaries
explicit. Tests cover TTLs, queue names, native SDK message decoding, timeout
refresh and consumer termination without an unbounded wait. Trigger remains
unfinished (undefined meta/driver), as do subscription log/listener methods.
Resolver data storage is currently a no-op; handler and listener sharing is tested.

RabbitMqTest separately uses a real isolated broker. It caught missing returns
in queue mapping and initial connection setup. Fixes restore the documented
values; actual publish/read-back and cache reuse validate them. This does not
establish production reliability, retries, TLS or cross-process delivery behavior.

## Payment data adapters

AuthorizeDataTest uses real legacy subscription and AIM response DTOs offline.
Schedule defaults/scalar coercion, native date handling and invalid timezone
recovery are characterized. Valid conversion currently fails on the unqualified
DateTimeZone name. Response subclasses legally provide status_int because the
base response references that field without declaring it; these tests establish
base-method behavior with that extension, not working transaction factory logic.
Only SDK file-load deprecation notices are intercepted; other errors retain the
previous PHPUnit handler. Gateway operations remain entirely offline.

## Factory and listener declaration repairs

The listener factory's parent exists under core/event/factory/loader, not
core/event/loader. The corrected declaration is covered using the actual shared
factory; it does not automatically call _add_listeners. Hookable still drops data
through undefined event_data, explicitly characterized for later intent-guided
repair. The validation factory's helper preserves positional validator/data pairs
when a subclass supplies their class constraint; the default constraint remains
an unanswered purpose question.


Payment continuation (MOD-089/090): real pinned SDK transaction/response objects now cover typed declarations, generic type normalization, payment profile IDs and native CIM caching through a legal subclass hook. No payment API is contacted. Null-initialized SDK fields still fail the legacy isset-based copy; default request preparation and subscription contracts remain unresolved.


Subscription continuation (MOD-093/096): native timezone conversion, cache/errors, payment forms and existing subscription operations now have tests. The credential-dependent wrapper is abstract; applications must supply credentials, missing transaction methods and the historical preparation hook. SDK DTOs and native construction are real, client requests are local doubles. create still returns null despite its string docblock; SDK isset filtering remains. get_info now passes its SDK list and guards total index zero, but pagination offset advancement remains unresolved and is not advertised as working.


Subscription circuit-breaker test (MOD-098) executes 100,000 actual loop ticks with 1,000 SimpleXML details per page; it takes roughly 40 seconds and proves the existing runaway-search bound. It does not repair offset advancement. Alert native broker fallback is tested (MOD-103); default CRUD and publishing remain unfinished, with failures characterized rather than invented.


Owner-approved alert retirement (MOD-107): trigger retains its signature but raises BadMethodCallException before callbacks or publication; the unfinished pthread class is no longer declared. Storage without a provider raises LogicException instead of invoking the absent default CRUD implementation. Tested subscription/expiry/provider APIs and synchronous listeners, including message decoding/expiry refresh, remain supported. Original retired code stays in comments for reference.
