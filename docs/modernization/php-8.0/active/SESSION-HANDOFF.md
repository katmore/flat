# Current work

- Establish the inherited PHP 7.4 baseline, then run the complete suite on PHP 8.0.30.
- Update tools and dependencies to their newest PHP 8.0 compatible releases.
- Review PHP 8.0 migration changes, including resource-to-object transitions,
  stricter internal arguments, inheritance signatures and changed comparisons.
- Modernize with PHP 8.0 syntax where behavior and the public API remain compatible.
- Compare the public API and regenerate the test inventory after test changes.
- Audit both dependency locks and revisit the dismissed dependency advisories.
- Carry forward the eight older mutation survivors for triage from the
  [PHP 7.3 probe](../../php-7.3/history/MUTATION-PROBE.md).
- Private consumer and live Authorize.Net checks need the owner's environment.

The owner handles releases and master. Work only on php-8.0.
