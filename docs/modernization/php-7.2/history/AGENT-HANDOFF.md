# Handoff: `modernize-26` — flat framework test baseline

> **Historical, kept for the record:** a handoff between earlier agents working on `modernize-26`. To resume work, start at [../README.md](../README.md).

Audience: a new agent/harness continuing this work with no prior context.
Read this file first, then `docs/modernization-26.md`.

Workspace: `/home/acks/devel/flat`
Repository: `git@github.com:katmore/flat.git` (`katmore/flat`, "experimental framework for php")
Branch: `modernize-26` (checked out)

---

## 1. TL;DR — where things stand

- Goal: modernize the `flat` PHP framework without breaking apps that
  depend on it. Step one is a characterization unit-test baseline on the
  PHP version the code last targeted, running entirely in Docker.
- Done: Docker PHP 7.2 test container under `docker/`; PHPUnit 8.5
  harness; **45 Tier 1 unit tests, all passing, 1,595 assertions**; the
  six classes under test are at **100% line and method coverage**.
- Done: four unreachable/dead code blocks in `src/` commented out in
  place (conservative style, see §7). No other `src/` behavior changes.
- **Nothing is committed.** All work is uncommitted on `modernize-26`.
  Do not commit or push unless the maintainer explicitly asks.
- Stopped at the **Phase 1 gate**: Phase 2 (routing/config contract
  tests, then MongoDB/MySQL/S3/RabbitMQ service containers and
  integration tests) must not start until the maintainer approves the
  Phase 1 report. See §10 and §11.
- A running change log with per-change rationale, verification, undo
  instructions, and suggested commit messages is maintained at
  `docs/modernization-26.md` (entries `MOD-001` … `MOD-008`). **Append
  a new entry for every change you make, as you make it. Never rewrite
  past entries; corrections get a new entry.**

---

## 2. Hard constraints from the maintainer

These override convenience. Violating any of them is a defect.

1. **No local/host tooling.** No PHP, Composer, PHPUnit, database, or
   dependency is installed or run on the host OS. Everything executes
   inside Docker containers defined in this repo under `docker/`.
   The host provides Docker (and git/editor) only.
   - Previously a `flat-webapp` clone was made at `/tmp/flat-webapp`
     for inspection; it has been **deleted**. If you need flat-webapp
     again (Phase 2), clone it inside a container or discuss first.
   - Docker images cached in the daemon (`php:7.2-cli`,
     `composer:2.2`) are image cache, not host tooling. That is fine.
2. **Do not move to Tier 2 / Phase 2 until the unit (Tier 1) work is
   approved at the gate.** The maintainer explicitly sequenced it this
   way so integration failures cannot be confused with unit breakage.
3. **Conservative `src/` edits.** Unreachable code is commented out in
   place with a `CONSERVATIVELY DISABLED` marker, original code kept
   verbatim inside the comment, and the reason recorded. Do not delete
   dead code (yet), do not refactor, do not "fix" flagged quirks
   without a deliberate maintainer decision logged in
   `docs/modernization-26.md`.
4. **Tests are characterization tests.** They pin what the code does
   today on PHP 7.2, quirks included. Do not change an assertion to
   match new behavior unless the behavior change itself was decided
   and logged.
5. **Append to `docs/modernization-26.md` for every change** (what,
   why, files, behavior change or not, verification commands/results,
   undo steps, suggested commit message). Keep commit-message-ready
   wording so the maintainer can compose commits later and bisect
   problems if dependent apps break.
6. Work on branch `modernize-26`. Leave `master` alone.

---

## 3. Why PHP 7.2 — version evidence

Do not re-litigate this without new evidence:

- `composer.json` (pre-existing, runtime block unchanged):
  `"php": ">=7.2.1"`, `mongodb/mongodb ^1.0.0`,
  `alcaeus/mongo-php-adapter ^1.0`, `authorizenet/authorizenet ^1.9`,
  `aws/aws-sdk-php ^3.23`.
- Last commit on the branch point: `65fdc24` "Replace all kind of
  dashes", 2020-03-09. Framework code docblocks mostly say
  "PHP version >= 7.1" (older), superseded by the Composer floor.
- `master` and `modernize-26` both point at `65fdc24` locally and on
  `origin`; `git rev-list --left-right --count master...modernize-26`
  is `0 0` and the diff is empty. They started identical.
- Conclusion: baseline = **PHP 7.2, pinned to 7.2.34** (final 7.2
  patch, in `php:7.2-cli`). PHP 7.2 is end-of-life (Nov 2020). That is
  intentional: this is the "before" picture for modernization.
- Consumer `katmore/flat-webapp` is older (`php >=7.1.1`,
  `katmore/flat ^1.0`, last commit 2017-03-21) and is not the version
  authority. Details in §11.

There are 632 PHP files under `src/flat/` and, before this work, zero
tests anywhere in the repo.
