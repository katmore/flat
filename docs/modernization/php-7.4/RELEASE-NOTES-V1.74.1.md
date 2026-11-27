# flat v1.74.1

Bug-fix release for PHP 7.4, addressing eleven defects found in the adversarial review. PHP requirements and dependency constraints are unchanged.

## Fixed

- **PDO parameter binding:** distinct named parameters retain their own values, including deferred and repeated execution. Boolean and decimal values no longer become SQL NULL.
- **PDO decimal criteria:** comparisons retain their fractional part instead of selecting records using truncated integers.
- **PDO joins and pagination:** structured LEFT, INNER and RIGHT joins use the requested direction; invalid directions are rejected. Limits must be at least 1 and offsets at least 0.
- **HTTP methods and bodies:** explicit methods work without request data. JSON `0`, raw `"0"` and empty raw bodies are transmitted correctly. HEAD requests do not consume response bodies.
- **Lazy DOM access:** `get_dom()` initializes and returns the DOM for ordinary HTML without requiring eager parsing.
- **Robots parsing:** empty directives, comments, unrelated directives, whitespace and root URLs are handled without notices or stale rules.
- **SSH disconnect:** releases SSH/SFTP resources, clears connection state and permits reconnecting. Destruction no longer requires a remote command channel.
- **SSH host verification:** supplied fingerprints are checked before password or public-key authentication, including fingerprints passed through the constructor.

## Consumer notes

SSH connections without a supplied fingerprint remain allowed. To pin a host, provide `fingerprint` in the constructor configuration or assign `get_auth()->fingerprint` before connecting. The existing format is 32 uppercase MD5 hexadecimal characters without separators. A mismatch raises the existing `syserror` before credentials are sent.

The authentication base class now declares public `fingerprint`, defaulting to NULL. This field can appear in reflection and public-property exports. No existing public class or method signature was removed.

Code relying on the incorrect PDO results, ignored HTTP methods or still-connected SSH clients after `disconnect()` should be updated.

## Validation

- PHP 7.4.33 Docker suite: **1,703 tests**, 100% class, method and line coverage; no failures or skips.
- Sample application against the patched framework: **67 tests**, including its PHP server-log check.
- JavaScript regressions: **45 programs pass**.
- Documentation and API comparisons checked.

The four known AWS SDK and MongoDB dependency advisories are unchanged; their fixed versions require PHP 8.1. This release does not advance the PHP modernization phase.
