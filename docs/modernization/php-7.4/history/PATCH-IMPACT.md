> Archived: PHP 7.4 work has moved to the [PHP 8.0 phase](../../php-8.0/README.md). The record below describes its earlier checkpoint.

# Consumer impact, pending PHP 7.4 patch

These entries cover the changes pending v1.74.1. Changes already shipped in
v1.74.0 are in [the archived ledger](../history/CONSUMER-IMPACT.md).
Keep this document active through patch release and consumer verification.

### MOD-333 (KD-117) — PDO bound parameters retain independent values

- Before: All bindParam placeholders used the last value.
- After: Each placeholder retains its own value, including deferred and repeated execution.
- Search consumer code for: `prepare_map`, `bindParam`.
- Verified: native SQLite regression tests, including distinct parameters and adjacent decimal values.

### MOD-333 (KD-118) — PDO bindings preserve booleans and decimals

- Before: Boolean and decimal values became SQL NULL.
- After: Booleans bind as booleans, NULL as NULL, integers as integers and decimals as strings without truncation.
- Search consumer code for: `prepare_map`, `bindValue`, `bindParam`.
- Verified: native SQLite regression tests, including distinct parameters and adjacent decimal values.

### MOD-333 (KD-119) — PDO decimal criteria retain their fractional part

- Before: Decimal criteria were truncated and could select another record.
- After: Only integer criteria bind as integers; decimal comparisons retain their value.
- Search consumer code for: `criteria_hash`, `criteria`.
- Verified: native SQLite regression tests, including distinct parameters and adjacent decimal values.

### MOD-333 (KD-120) — Structured PDO joins respect their direction

- Before: Every structured join became LEFT JOIN, including invalid directions.
- After: LEFT, INNER and RIGHT produce their requested SQL; other directions raise bad_rule before preparation.
- Search consumer code for: `load_statement`, `join`.
- Verified: native SQLite regression tests, including distinct parameters and adjacent decimal values.

### MOD-333 (KD-125) — PDO pagination rejects negative bounds

- Before: Negative limits and offsets bypassed validation; SQLite LIMIT -1 was unlimited.
- After: limit must be at least 1 and skip at least 0; bad_rule names the invalid field.
- Search consumer code for: `limit`, `skip`, `statement\rules`.
- Verified: native SQLite regression tests, including distinct parameters and adjacent decimal values.

### MOD-334 (KD-121) — cURL respects explicit methods without request data

- Before: A bodyless DELETE, HEAD, PUT or POST was sent as GET.
- After: Explicit methods are independent of body presence; HEAD does not read a response body. NULL method still selects GET without a body or POST with one.
- Search consumer code for: `request_method`.
- Verified: real HTTP requests with WireMock; method, body, content type, DOM and robots assertions.

### MOD-334 (KD-122) — cURL sends zero and empty bodies

- Before: JSON 0, raw "0" and empty raw bodies were treated as absent.
- After: All non-NULL request bodies are sent, including zero and empty strings, with their content type.
- Search consumer code for: `curl\request`, `encode`.
- Verified: real HTTP requests with WireMock; method, body, content type, DOM and robots assertions.

### MOD-334 (KD-123) — cURL lazily initializes the DOM

- Before: get_dom() returned NULL for ordinary HTML unless eager DOM parsing or XPath ran first.
- After: get_dom() initializes the DOM once based on DOM state, not the truthiness of response text.
- Search consumer code for: `get_dom`.
- Verified: real HTTP requests with WireMock; method, body, content type, DOM and robots assertions.

### MOD-334 (KD-124) — Robots parsing handles empty directives and root URLs

- Before: Empty Disallow or unrelated/comment lines could raise notices or reuse another rule; root URLs could lack a path.
- After: Only Allow/Disallow rules are collected; empty values and comments are ignored, whitespace is accepted, and an absent path means /.
- Search consumer code for: `is_robots_allowed`, `use_robots`.
- Verified: real HTTP requests with WireMock; method, body, content type, DOM and robots assertions.

### MOD-335 (KD-126) — SSH disconnect closes the transport

- Before: disconnect() ran a remote exit command, left SSH/SFTP resources alive and still reported connected; commands continued to work.
- After: the SFTP handle is released and ssh2_disconnect() closes the transport; is_connected() is false, commands require reconnecting,
  and the same object may connect again. ignore_missing retains its behavior. No remote exec channel is needed for disconnect or destruction.
- Search consumer code for: `disconnect`, `is_connected`, `core\ssh`.
- Verified: real OpenSSH sessions, SFTP use, resource counts, commands after disconnect and reconnect.

### MOD-335 (KD-127, Q62) — Supplied SSH host fingerprints are verified before authentication

- Before: password authentication ignored a fingerprint assigned through get_auth(); constructor fingerprints were ignored for both modes.
- After: optional constructor `fingerprint` and `get_auth()->fingerprint` are checked before password or public-key authentication.
  Use the trusted host fingerprint in ssh2_fingerprint()'s existing format: 32 uppercase MD5 hexadecimal characters, without separators.
  A mismatch or non-string pin raises the existing syserror ("fingerprint mismatch") before credentials are sent.
  NULL or an empty string leaves the connection unpinned, as the owner chose. Existing unpinned key logins still remember their first fingerprint.
- The auth base now declares public `fingerprint` (default NULL); reflection and public-property exports can include it even for unpinned password auth.
- Search consumer code for: `fingerprint`, `get_auth`, `core\ssh`.
- Verified: both authentication modes against real OpenSSH; mismatches, malformed pins, trusted constructor pins and unpinned callers.
