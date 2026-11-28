> Archived: verification of the review fixes at 772b5ba (2026-11-28). Current
> status: [phase overview](../README.md). Findings: [review](ADVERSARIAL-REVIEW.md).

# Review fix verification

The fixes for KD-130 to KD-133 (MOD-366 to MOD-369) were checked on a fresh
image build. The checks were independent: the change log was not taken on trust.

## Confirmed

- Full suite: 1,728 tests pass with 100% configured coverage (332 classes,
  1,105 methods, 8,874 lines). All eight build-source checksums pass.
- `docker/bin/check-resolution`: default resolution is blocked, approved
  consumer and runtime resolution pass, and the audit still reports all four
  accepted advisories. The exceptions use `apply: block` only.
- Strict flags keep keyed settings (`['execute'=>true]`, `['strip'=>…]`). A bare
  `true` in a flag list no longer enables every flag. That matched on PHP 8 too,
  and consumer impact records it.
- After a failed request, no temporary cookie jar remains when the server set
  a cookie and HTML parsing then failed. This holds while the exception is
  still held and after it is released, with Xdebug off and in develop mode.

## Findings

- The failure tests in MOD-366 used only an unreachable host, where libcurl
  never writes a jar. MOD-370 adds the cookie-setting case. It fails when the
  failure-path cleanup is removed.
- The `CURLOPT_COOKIEJAR` reset in `curl\response`'s failure path has no
  observable effect. Removing it changes no test result and no probe result,
  including under Xdebug develop mode. No captured frame takes the handle as an
  argument, so its comment overstates the risk. It is harmless; keep or remove
  it at the owner's choice.
- `docker/bin/test unit` excludes the services group, which holds the cURL
  tests. Filtered runs of them need `all` or `services`.
- Host `XDEBUG_MODE` does not reach the test container, because Compose sets
  coverage mode. Develop-mode checks need a direct `docker run`.
