# Current work

The PHP 8.0 implementation, compatible dependency refresh and ordinary Docker
suite are validated. Current results and commands: [phase overview](../README.md).
All findings KD-130 to KD-133 are resolved with the approved Q64–Q66 decisions
(MOD-366/367); [review evidence](../history/ADVERSARIAL-REVIEW.md). PHP 8.0
consumers must configure the four advisory exceptions in their root manifest;
see [consumer impact](CONSUMER-IMPACT.md#mod-367-php-80-composer-resolution-q64).
The packages remain vulnerable until PHP 8.1 permits patched dependencies.

## Project follow-ups

- The owner declares the phase complete, chooses the release version in light
  of the approved public base-class rename, and handles releases and master.
- v1.74.1 is published and inherited. The sample-app release-lock follow-up from
  the [archived exit review](../../php-7.4/history/EXIT-REVIEW.md) remains.
- Public sample-app HTTP integration follows its PHP 8 runtime update. This
  phase's consumer review is a static API-impact scan, not that runtime test.
- The private consumer scan and live Authorize.Net approval/decline checks need
  the owner's environment. The ordinary suite uses the documented local mock.
- Triage the eight older [PHP 7.3 mutation survivors](../../php-7.3/history/MUTATION-PROBE.md).
  The new CLI sample's assertion gap is closed in [its record](../history/MUTATION-CHECK.md).
- Revisit the four AWS/MongoDB advisories on PHP 8.1 and remove the approved
  AWS minimum-PHP deprecation exception then. Do not advance runtimes on your own.

Work and push only on php-8.0. Next identifiers: KD-134 and Q67.
