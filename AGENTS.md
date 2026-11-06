# Repository working requirements

## Maintainer feedback

my human was disappointed and irritated with me because I put the Docker test
harness outside this repository. That made the setup dependent on the agent's
workspace instead of reproducible from the repository. Do not repeat this.

## Required test workflow

- Keep Dockerfiles, Compose services, test dependency manifests and locks,
  bootstrap/configuration, and orchestration scripts in this repository.
- Do not add agent-specific environment detection, private host paths, or
  private container names to executable tooling; accept generic explicit inputs.
- Run PHP, Composer, unit tests and their external services through Docker.
  Do not depend on host PHP or an agent-private virtual environment.
- `./tests.sh` is the repository-owned entry point. Setup must work from a fresh
  checkout without `/workspace/flat-environment` or locally prebuilt private images.
- Do not archive agent-private harnesses, vendor trees, or scratch environments
  inside the repository, even as ignored files. Discard obsolete scratch setup.
- Keep test dependencies separate from the framework's application dependencies.
- Preserve PHP 7.2.34 until the maintainer explicitly approves a version step.
  Do not merge, publish, or change release branches on your own.
- Keep code changes, test findings, compatibility decisions, and deferred work
  documented in `docs/`. Ask the maintainer about unknown method/class purposes;
  do not guess behavior to obtain coverage.

## Commit cadence

My human requested more frequent commits for logical groups of related changes.
After the appropriate checks, commit each coherent group with its related docs
updates instead of accumulating a large undocumented working tree. Follow the
maintainer's explicit branch and push instructions; commit/push current work to
php-7.2 only and leave modernize-26/master housekeeping to the maintainer.
