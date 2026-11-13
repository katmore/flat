# Repository-owned Docker test workflow

Two entry points take the same arguments. `bin/test` holds all test logic (modes,
PHPUnit arguments, coverage reports, the inventory) and runs natively where PHP
7.2 and PHPUnit are installed. `docker/bin/test` only handles Docker: it builds or
checks the image, starts the services a mode needs, runs `bin/test` inside the PHP
container with the same arguments, and removes the containers afterwards. The
rest of this page uses `docker/bin/test`, the supported way to run the suite.

For a native run, install the Composer dev dependencies, provide MongoDB and
RabbitMQ for service tests (`FLAT_TEST_MONGODB_URI`, `FLAT_TEST_RABBITMQ_HOST`) and
run e.g. `bin/test unit`. `FLAT_TEST_PHP`, `FLAT_TEST_PHPUNIT` and
`FLAT_TEST_BOOTSTRAP` override the PHP binary, PHPUnit and its bootstrap; the
Compose file sets the last two for the container's own PHPUnit install.

Run from a fresh checkout with Docker Engine and Docker Compose v2.20 or newer.
No host PHP, Composer, private virtual environment, sibling checkout or private
prebuilt image is required. The only host script is POSIX shell orchestration.

```sh
docker/bin/test                      # all tests, with both required services
docker/bin/test unit                 # no external service containers
docker/bin/test services             # service-dependent cases, both services
docker/bin/test mongo                # only MongoDB-dependent cases + MongoDB
docker/bin/test rabbitmq             # only RabbitMQ-dependent cases + RabbitMQ
docker/bin/test all --coverage       # full line/method/class coverage
docker/bin/test unit --filter SshTest
docker/bin/test --build              # build only
docker/bin/test --down               # explicit cleanup after an interrupted host
```

`docker/bin/test inventory` prints a Markdown inventory of every test to stdout,
built from PHPUnit's `--list-tests-xml` by `tests/tools/inventory.php`. It needs
no service containers. Pass a known-defects file to label `@group known-defect`
tests with their KD IDs, and redirect the output where it belongs:

```sh
tmp=$(mktemp) && docker/bin/test inventory docs/modernization/php-7.2/active/KNOWN-DEFECTS.md > "$tmp" && mv "$tmp" docs/modernization/php-7.2/TEST-INVENTORY.md
```

A redirect straight onto the inventory would empty it if the command fails (for
example when Docker is not running); writing to a temporary file first avoids that.

`docker/bin/mutate` is a mutation probe: it plants one small bug at a time (a
flipped comparison, a negation removed, a `true`/`false` swap and similar) in a
throwaway copy of the repository and checks that the unit suite notices. A
mutant the suite does not notice "survives" and marks behavior that no test pins
down, unless the change is unobservable (an equivalent mutant), which needs
triage. It never touches the working tree: it copies the repository, mounts the
copy as `/app` and refuses to run anywhere without the copy's marker file. It is
slow (the unit suite runs once per mutant, about 6 seconds), so it samples:

```sh
docker/bin/mutate --list                          # candidate mutants per file
docker/bin/mutate --limit=200 --seed=1 src/flat/core > build/tests/mutate-core.md
```

Every mutant that is not caught is listed with its file, line and change. Use
a different `--seed` for a different sample; the same seed gives the same one.

Every normal run builds/reuses the repository-defined image, waits for the
selected services to be healthy, runs PHPUnit, and tears down its Compose
containers, network and anonymous test-data volumes. Failure and interrupt paths
also clean up. PHPUnit failures propagate as a nonzero wrapper exit status.
Docker image/build cache remains available for subsequent runs.

PHP runs as the host user's UID/GID, except when the host user is root (common
in CI): root ignores file permissions, so the unreadable-path tests could not
fail. A root host therefore runs PHP as 1000:1000 and the report directory is
made writable for it. RabbitMQ counts as healthy only once its listener ports
accept connections (`check_port_connectivity`); its node `ping` succeeds a few
seconds earlier, which previously made `docker/bin/test rabbitmq` fail every time.

Reports go to `build/tests/<mode>/`, which Git ignores. Only the `all` coverage
report represents whole-suite coverage; filtered runs cover their selected cases.
Subscription guard tests use an owner-approved subclass limit of 2 while
asserting that the production default remains 100,000. They exercise the real
guard without the former brute-force 100-million-detail scan.

## Offline operation

Prepare the images online once with `docker/bin/test all` (or build the PHP image and
pull the selected service images separately). Then, with those images cached:

```sh
docker/bin/test unit --offline
docker/bin/test all --offline --coverage
```

Offline mode checks that the selected local image refs exist, skips builds,
and passes explicit no-pull flags. A missing image produces a clear error; it is not downloaded automatically. Cached base images or Composer
archives alone are not sufficient: the built flat-tests-php72 image must exist.
Rebuild that image online after changing Dockerfile/test dependency inputs.
The optional build CA input is not used by offline execution.

The test network is internal-only for normal and offline runs. Runtime containers
can reach the local services but have no Internet route. Docker health checks,
loopback fixtures and the Mongo transport-failure proxy stay local. The wrapper
owns startup/readiness by selected test type; PHP does not unconditionally depend
on services in Compose, so unit mode needs only the PHP image.

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

- `bin/test`: test modes, PHPUnit arguments, coverage reports and the inventory;
  runs natively or inside the container.
- `docker/bin/test`: Docker lifecycle (image, services, cleanup); runs `bin/test`
  in the PHP container.
- `docker/Dockerfile`: complete build from pinned public PHP/native sources;
  no FROM line refers to an agent-private image.
- `docker/docker-compose.yml`: PHP plus health-checked MongoDB/RabbitMQ services.
- `tests/docker/tools/composer.json` and its checked-in lock: PHPUnit and framework
  test autoload paths inside the container.
- `tests/docker/runtime/composer.json` and its checked-in lock: PHP-7.2-compatible
  SDK dependencies used by the suite, separate from application dependencies.
- `tests/docker/bootstrap.php`: container-local test bootstrap.
- `tests/docker/compose.ca.yaml`: optional build CA trust input.
- `tests/tools/mutate.php` and `docker/bin/mutate`: the mutation probe above
  (logic in PHP; the wrapper creates the throwaway copy).
- `tests/tools/inventory.php`: converts PHPUnit's `--list-tests-xml` output into
  the Markdown test inventory (`bin/test inventory`).

The Composer-only archive-fetch stage uses the official Composer image and the
locked PHP-7.2 platform constraints; it does not execute framework/tests. The
actual PHP runtime is pinned and validated as 7.2.34, with required extensions
built/verified there. ext-sockets is ignored only by the archive-fetch stage and
is actually compiled/enabled in the PHP 7.2 test runtime. GitHub archives use
codeload URLs with the same locked source commits. Application composer.json and
its dependency policy are unchanged by this isolated test setup.

For a corporate/cloud proxy CA, set FLAT_TEST_CA_FILE to a readable CA bundle.
The CA is a BuildKit secret mount, never copied into an image. Newer Compose
builds through Bake, which refuses a secret read from outside the build context;
docker/bin/test sets BUILDX_BAKE_ENTITLEMENTS_FS=0 for CA builds only (an explicit value
in the environment wins). Ordinary local builds
need no CA override; Docker's standard proxy configuration is respected.

## Maintainer expectation

my human was disappointed and irritated with me because the previous harness
lived outside the repository. This setup replaces that mistake: operational test
instructions must refer to repository-owned files and `docker/bin/test`, not a private
workspace path. AGENTS.md makes that requirement explicit for later agents.
