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
