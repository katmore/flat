# Repository-owned Docker test workflow

Run from a fresh checkout with Docker Engine and Docker Compose v2.20 or newer.
No host PHP, Composer, private virtual environment, sibling checkout or private
prebuilt image is required. The only host script is POSIX shell orchestration.

```sh
./tests.sh                      # all tests, with both required services
./tests.sh unit                 # no external service containers
./tests.sh services             # service-dependent cases, both services
./tests.sh mongo                # only MongoDB-dependent cases + MongoDB
./tests.sh rabbitmq             # only RabbitMQ-dependent cases + RabbitMQ
./tests.sh all --coverage       # full line/method/class coverage
./tests.sh unit --filter SshTest
./tests.sh --build              # build only
./tests.sh --down               # explicit cleanup after an interrupted host
```

Every normal run builds/reuses the repository-defined image, waits for the
selected services to be healthy, runs PHPUnit, and tears down its Compose
containers, network and anonymous test-data volumes. Failure and interrupt paths
also clean up. PHPUnit failures propagate as a nonzero wrapper exit status.
Docker image/build cache remains available for subsequent runs.

Reports go to `build/tests/<mode>/`, which Git ignores. Only the `all` coverage
report represents whole-suite coverage; filtered runs cover their selected cases.
Subscription guard tests use an owner-approved subclass limit of 2 while
asserting that the production default remains 100,000. They exercise the real
guard without the former brute-force 100-million-detail scan.

## What runs where

| Component | Runtime |
| --- | --- |
| PHP/PHPUnit | PHP 7.2.34 container, pinned official base digest |
| MongoDB integration | Separate pinned MongoDB 4.0 Compose service |
| RabbitMQ integration | Separate pinned RabbitMQ 3.8.34 Compose service |
| PDO | Native SQLite inside the PHP container; no MySQL service is currently used |
| SSH/SFTP unit cases | Explicit transport/clock double with native local streams; no SFTP server is claimed |
| Native HTTP/session/process fixtures | Test-owned child PHP processes inside the PHP container |
| Mongo outage fixture | Test-owned TCP proxy child inside PHP, forwarding real bytes to MongoDB |

`unit` means no external service container dependencies. Some unit fixtures use
local subprocesses/loopback sockets within PHP's container. Native Mongo/Rabbit
cases are marked with PHPUnit `services` and `mongo`/`rabbitmq` groups. Pure
metadata checks use no-connect fixtures instead of accidentally requiring Mongo.
No database/broker ports are published on the host. A separate Compose project
keeps this stack isolated; set COMPOSE_PROJECT_NAME if running parallel checkouts.
Test-only broker credentials are declared in Compose and do not belong to a
production system. Broker integration fixtures allow a 10-second handshake/read
timeout so coverage instrumentation and slower CI machines do not cause a false
2-second timeout.

## Files that make the setup reproducible

- `tests.sh`: lifecycle and test-type selection.
- `docker/Dockerfile`: complete build from pinned public PHP/native sources;
  no FROM line refers to an agent-private image.
- `docker/docker-compose.yml`: PHP plus health-checked MongoDB/RabbitMQ services.
- `tests/docker/tools/composer.json` and its checked-in lock: PHPUnit and framework
  test autoload paths inside the container.
- `tests/docker/runtime/composer.json` and its checked-in lock: PHP-7.2-compatible
  SDK dependencies used by the suite, separate from application dependencies.
- `tests/docker/bootstrap.php`: container-local test bootstrap.
- `tests/docker/compose.ca.yaml`: optional build CA trust input.

The Composer-only archive-fetch stage uses the official Composer image and the
locked PHP-7.2 platform constraints; it does not execute framework/tests. The
actual PHP runtime is pinned and validated as 7.2.34, with required extensions
built/verified there. ext-sockets is ignored only by the archive-fetch stage and
is actually compiled/enabled in the PHP 7.2 test runtime. GitHub archives use
codeload URLs with the same locked source commits. Application composer.json and
its dependency policy are unchanged by this isolated test setup.

For a corporate/cloud proxy CA, set FLAT_TEST_CA_FILE to a readable CA bundle.
The CA is a BuildKit secret mount, never copied into an image. Ordinary local builds
need no CA override; Docker's standard proxy configuration is respected.

## Maintainer expectation

my human was disappointed and irritated with me because the previous harness
lived outside the repository. This setup replaces that mistake: operational test
instructions must refer to repository-owned files and `./tests.sh`, not a private
workspace path. AGENTS.md makes that requirement explicit for later agents.
