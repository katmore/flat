# Functional tests in flat-webapp

The owner asked for these on 2026-11-21 and chose to keep them in flat-webapp, which settles
the question the old plan left open ([../../php-7.2/active/WEBAPP-TESTS.md](../../php-7.2/active/WEBAPP-TESTS.md),
written when they were to live in flat). They are on flat-webapp's `php-7.3` branch.

## How they run

`docker/bin/test [PHPUnit arguments]` in flat-webapp: Apache with the application (the dev image,
working tree mounted) and a PHPUnit 8.5 container on PHP 7.3.33, linked by Compose. The tests
(in flat-webapp's tests folder, Functional) send real HTTP requests with curl through a small helper class. The run
fails on a test failure, and also when the server log shows any PHP warning, notice,
deprecation or fatal error. Test dependencies have their own lock, kept with the tests.

## What is covered (36 tests, shipped application only)

- View: home on seven path spellings, unknown views (404), POST and HEAD, the stylesheet.
- API: the `example` resource (GET, query string as input, HEAD, a GET body ignored), the
  forbidden catch-all (403), methods without a handler (500), the GET method override.
- Shown able to fail: with the greeting and the home template changed, 11 tests fail.

## Not covered, and why

- Request bodies cannot be observed: `example` implements GET only, so POST, PUT and DELETE
  answer 500 whatever they carry. Covering input parsing, a real POST, PUT or DELETE and the
  fatal-error page needs test-only resources and templates mounted while testing (not added; the
  owner chose the shipped application only).
- `PATCH`: support is deferred to v2.X (owner); the fatal error it causes is Q43.

## Known behaviors of the application

flat-webapp's `app/` and `web/` model what the one known consumer does: they change only for a
literal bug (owner, 2026-11-21). Two behaviors the tests found are kept as they are:

- Q43: `PATCH` and other unknown methods end in a PHP fatal error and a 500. A 500 is fine; flat
  has no 501 response. `PATCH` itself waits for v2.X. Not in the tests (the server-log check would
  fail, and an allowlist is not allowed).
- Q44: malformed JSON is silently treated as empty input (pinned by a test). A literal bug, but its
  obvious fix ends in a PHP fatal error, so it waits for the owner.
