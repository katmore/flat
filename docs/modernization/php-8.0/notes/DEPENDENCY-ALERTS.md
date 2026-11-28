# Dependency alerts

Both locks are audited with the pinned Composer image after dependency updates.
The inherited runtime lock contains four advisories: two each for aws/aws-sdk-php
and mongodb/mongodb. Fixed releases require PHP 8.1; PHP 8.0 cannot install them.
The prior dismissal decision is retained. No GitHub alert is changed by this work.

A fresh read of GitHub's dismissed alerts returned HTTP 403, "Resource not
accessible by integration". Composer lock audits provide the verifiable package
state; the current GitHub dismissal state cannot be independently confirmed.

## Annotations requirement

The explicit doctrine/annotations 2.0 requirement remains necessary despite its
abandoned-package status. On PHP 8.0, an offline modern Authorize.Net authentication
request serializes cleanly with the package present. Hiding only that package in
an isolated probe causes an E_WARNING in ANetSensitiveFields::fetchFromConfigFiles;
a strict error handler converts it to ErrorException. No network request is sent.
Removing the requirement would reintroduce a diagnostic, so it is retained.

## YAML security selection

The solver's highest PHP 8.0 version, 6.0.19, introduced five advisories. The
security-maintained 5.4.57 release avoids all five. MOD-363 restores that release
and constrains both manifests to patched release streams while allowing newer
streams on newer PHP hosts. The final runtime audit is back to four advisories;
the tools audit is clean. No new YAML risk was accepted.

## PHP 8.0 consumer installation (Q64)

Composer 2.11 blocks these vulnerable releases during a fresh resolution. The
owner approved the four exceptions below on 2026-11-28. Merge this fragment into
**the application's root composer.json**, keeping any other config settings:

```json
{
  "config": {
    "audit": {
      "ignore": {
        "PKSA-4t1p-xpk2-nsss": {
          "apply": "block",
          "reason": "Accepted for PHP 8.0 only; AWS fix requires PHP 8.1."
        },
        "PKSA-dxyf-6n16-t87m": {
          "apply": "block",
          "reason": "Accepted for PHP 8.0 only; AWS fix requires PHP 8.1."
        },
        "PKSA-y93m-jgy6-r9ns": {
          "apply": "block",
          "reason": "Accepted for PHP 8.0 only; MongoDB fix requires PHP 8.1."
        },
        "PKSA-61k5-cqr9-b8b4": {
          "apply": "block",
          "reason": "Accepted for PHP 8.0 only; MongoDB fix requires PHP 8.1."
        }
      }
    }
  }
}
```

`apply: block` affects installation blocking only. `composer audit --locked`
still reports these advisories and returns a nonzero result; other advisories
still block resolution. Do not disable security blocking globally. Remove the
exceptions when PHP 8.1 allows patched versions.

Run `docker/bin/check-resolution` online. It resolves a new consumer from the
local branch and the runtime harness without existing locks/vendors. It checks
that default consumer resolution fails, the same consumer resolves with only
these exceptions, the runtime resolves, and audits still report all four. The
pinned Composer image uses platform PHP 8.0.30 and MongoDB extension 1.20.1;
only its missing sockets extension is ignored (the PHP test image verifies it).
