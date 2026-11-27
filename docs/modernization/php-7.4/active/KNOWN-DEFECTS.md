# Open defects, php-7.4

Resolved KD-107 to KD-127 and answered Q51 to Q62 are recorded in
[the archived registry](../history/KNOWN-DEFECTS.md). The next new defect is
KD-129; the next owner question is Q63.

## KD-128 — Mutation runner lacks required checkout files and service fixtures

- Category: A (clear test-tool bug), **open; not fixed**.
- `docker/bin/mutate --limit=1 --seed=74 src/flat/core/mapper.php` fails its
  unmutated baseline: CodingRulesTest scans /app/bin, but the mutation checkout
  does not copy bin or docker (296 tests reached, one error).
- Its service mode also starts only MongoDB/RabbitMQ and omits the current
  HTTP, SSH and S3 services and environment inputs (confirmed by inspection).
- Required fix: complete the copied checkout and service setup; prove both
  baselines pass and run a small mutation probe. Keep all coding-rule and
  service tests enabled. Reproduction context: [exit review](../notes/EXIT-REVIEW.md).

## How the fix lands

Fix the runner and add meaningful regression coverage, run the relevant Docker
checks, regenerate the inventory if tests change, and add a change-log entry.
Archive this entry when resolved. Record any consumer-visible behavior change
in [consumer impact](CONSUMER-IMPACT.md); test-tool repairs alone do not change
framework consumers.

## Owner questions

None open. The decisions and original questions are in the archived registry.
