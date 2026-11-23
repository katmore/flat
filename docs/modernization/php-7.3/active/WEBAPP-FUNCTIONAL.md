# Tests in flat-webapp

Run `docker/bin/test` in flat-webapp. It installs the tracked application lock, starts Apache
on PHP 7.3.33, runs PHPUnit 9.6 tests and checks the server log. Docker is the only host
requirement. The installed flat version and commit must match the locked published release.

## Coverage

- 36 HTTP cases exercise the shipped application's views, API, method overrides and assets.
- Nine HTTP cases observe actual parsed JSON/form bodies and GET input through a test-only
  endpoint. The endpoint is enabled only in the test Compose stack, which has no host port.
- 21 isolated checks cover native/structured/truncated error logs, the log command's exit status,
  native fatal paths, generic error-handler responses and the installed dependency lock.

The body checks were challenged with a scratch parser returning empty input for every request.
Six new checks failed while the five existing unsupported-method body checks still passed.

## Error contracts

The server-log gate rejects native PHP messages, including catchable/recoverable fatals, and
application JSON error records. No expected errors are ignored in the application server log.

Separate PHP child processes deliberately exercise malformed JSON, unknown PATCH, handled user
fatal errors and handled exceptions. Tests assert their exit status, body and complete diagnostic
record; handled errors also assert HTTP status 500. The child diagnostics are asserted test
outputs, not discarded server messages.

Application behavior is retained: malformed JSON and unknown PATCH end in native fatal errors;
recognized methods without a handler answer the existing JSON 500. PATCH support and a correct
405 remain deferred to v2.X. Changes and validation: MOD-222 to MOD-224 in
[../CHANGELOG.md](../CHANGELOG.md).
