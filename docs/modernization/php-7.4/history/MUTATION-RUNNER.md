> Completed KD-128 (MOD-339). Replaced as an active item by the [current defect index](../active/KNOWN-DEFECTS.md); current next actions are in the [handoff](../active/SESSION-HANDOFF.md).

# Mutation runner repair

## Resolution

The copied checkout now includes bin and docker, so the coding-rules tests see
the same required directories. Service mode uses the MongoDB, RabbitMQ,
WireMock, OpenSSH and S3Mock Compose services and their environment/volume
inputs. The host UID/GID is used; root hosts run PHP as 1000:1000. Startup
failures print service diagnostics, and cleanup failures propagate as errors.
Framework behavior and the mutation algorithm are unchanged.

## Docker regression checks

```sh
docker/bin/mutate --limit=1 --seed=74 --at=src/flat/core/rand.php:80
docker/bin/mutate --services --limit=1 --seed=74 --at=src/flat/core/rand.php:80
```

Both commands exited 0: their unmodified baselines passed, and each caught one
valid planted mutation (the removed minimum-value type guard). Neither probe
timed out or reported a survivor. The service run included all five fixtures;
the unit-only run started no service containers. These are smoke checks, not
an exhaustive assessment of mutation coverage.

An invalid-option run exited 2 after cleanup. No temporary mutation copies
remained; the working production source was unchanged. Shell syntax was also
checked. No PHP warning, test or coding-rule check was suppressed or skipped.

## Original open-work snapshot

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
