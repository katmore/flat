#!/bin/sh
set -eu
repo_dir=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
cd "$repo_dir"
export FLAT_TEST_UID="$(id -u)" FLAT_TEST_GID="$(id -g)"
export COMPOSE_PROJECT_NAME="${COMPOSE_PROJECT_NAME:-flat-tests}"
compose() {
    if [ "${offline:-false}" != true ] && [ -n "${FLAT_TEST_CA_FILE:-}" ]; then
        docker compose -f docker/docker-compose.yml -f tests/docker/compose.ca.yaml "$@"
    else
        docker compose -f docker/docker-compose.yml "$@"
    fi
}
usage() {
    echo 'Usage: ./tests.sh [all|unit|services|mongo|rabbitmq] [--offline] [--coverage] [PHPUnit arguments]'
    echo '  all (default): run everything, with MongoDB and RabbitMQ'
    echo '  unit:          run tests that need no external service containers'
    echo '  services:      run service-dependent tests with MongoDB and RabbitMQ'
    echo '  mongo:         run MongoDB-dependent tests, starting only MongoDB'
    echo '  rabbitmq:      run RabbitMQ-dependent tests, starting only RabbitMQ'
    echo '  --offline:     use existing images only; never build or pull'
    echo '  --coverage:    write reports under build/tests/<mode>/'
    echo '  --build:       build the test image; --down: remove this test stack'
    echo '  -h, --help:    show this usage'
    echo 'Examples: ./tests.sh unit --filter SshTest; ./tests.sh all --offline --coverage'
}
test_mode=all
coverage=false
offline=false
action=run
while [ "$#" -gt 0 ]; do
    case "$1" in
        all|unit|services|mongo|rabbitmq) test_mode=$1 ;;
        --coverage) coverage=true ;;
        --offline) offline=true ;;
        --build) action=build ;;
        --down) action=down ;;
        -h|--help) usage; exit ;;
        --) shift; break ;;
        -*) break ;;
        *) printf 'Unknown test type: %s\n' "$1" >&2; usage >&2; exit 2 ;;
    esac
    shift
done
if [ "$action" = down ]; then compose down --volumes --remove-orphans; exit; fi
if [ "$action" = build ] && "$offline"; then
    echo '--build and --offline cannot be combined' >&2; exit 2
fi
service_names=
case "$test_mode" in
    all|services) service_names='mongo rabbitmq' ;;
    mongo) service_names=mongo ;;
    rabbitmq) service_names=rabbitmq ;;
esac
if "$offline"; then
    # Names come only from the fixed test-mode values above. No host parser or
    # private cache is required; Compose supplies the exact pinned image refs.
    image_refs=$(compose config --images php $service_names)
    for image_ref in $image_refs; do
        if ! docker image inspect "$image_ref" >/dev/null 2>&1; then
            printf 'Offline image missing: %s\nRun ./tests.sh all online once to prepare the images.\n' "$image_ref" >&2
            exit 1
        fi
    done
else
    compose build php
fi
if [ "$action" = build ]; then exit; fi
report_dir="build/tests/$test_mode"
mkdir -p "$report_dir"
cleanup() {
    test_status=$?
    trap - EXIT INT TERM
    compose down --volumes --remove-orphans || test_status=1
    exit "$test_status"
}
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM
if [ -n "$service_names" ]; then
    if "$offline"; then compose up -d --wait --pull never $service_names;
    else compose up -d --wait $service_names; fi
fi
case "$test_mode" in
    unit) set -- --exclude-group services "$@" ;;
    services) set -- --group services "$@" ;;
    mongo) set -- --group mongo "$@" ;;
    rabbitmq) set -- --group rabbitmq "$@" ;;
esac
if "$coverage"; then
    compose run --rm --no-deps --pull never php --testsuite unit \
        --coverage-text="/app/$report_dir/coverage.txt" \
        --coverage-clover="/app/$report_dir/coverage.xml" \
        --coverage-php="/app/$report_dir/coverage.php" \
        --log-junit="/app/$report_dir/junit.xml" "$@"
else
    compose run --rm --no-deps --pull never php --testsuite unit "$@"
fi
