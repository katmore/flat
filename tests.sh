#!/bin/sh
set -eu
repo_dir=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
cd "$repo_dir"
export FLAT_TEST_UID="$(id -u)" FLAT_TEST_GID="$(id -g)"
export COMPOSE_PROJECT_NAME="${COMPOSE_PROJECT_NAME:-flat-tests}"
compose() {
    if [ -n "${FLAT_TEST_CA_FILE:-}" ]; then
        docker compose -f docker/docker-compose.yml -f tests/docker/compose.ca.yaml "$@"
    else
        docker compose -f docker/docker-compose.yml "$@"
    fi
}
case "${1:-}" in
    --down) compose down --volumes --remove-orphans; exit ;;
    --build) compose build php; exit ;;
    -h|--help)
        echo 'Usage: ./tests.sh [all|unit|services|mongo|rabbitmq] [--coverage] [PHPUnit arguments]'
        echo '  all (default): run everything, with MongoDB and RabbitMQ'
        echo '  unit:          run tests that need no external service containers'
        echo '  services:      run service-dependent tests with MongoDB and RabbitMQ'
        echo '  mongo:         run MongoDB-dependent tests, starting only MongoDB'
        echo '  rabbitmq:      run RabbitMQ-dependent tests, starting only RabbitMQ'
        echo '  -h, --help:    show this usage'
        echo '  --coverage:    write reports under build/tests/<mode>/'
        echo '  --build:       build the test image; --down: remove this test stack'
        echo 'Examples: ./tests.sh unit --filter SshTest; ./tests.sh all --coverage'
        exit ;;
esac
test_mode=all
case "${1:-}" in
    all|unit|services|mongo|rabbitmq) test_mode=$1; shift ;;
esac
coverage=false
if [ "${1:-}" = --coverage ]; then coverage=true; shift; fi
compose build php
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
case "$test_mode" in
    unit) set -- --exclude-group services "$@" ;;
    services) compose up -d --wait mongo rabbitmq; set -- --group services "$@" ;;
    mongo) compose up -d --wait mongo; set -- --group mongo "$@" ;;
    rabbitmq) compose up -d --wait rabbitmq; set -- --group rabbitmq "$@" ;;
    all) compose up -d --wait mongo rabbitmq ;;
esac
if "$coverage"; then
    compose run --rm --no-deps php --testsuite unit \
        --coverage-text="/app/$report_dir/coverage.txt" \
        --coverage-clover="/app/$report_dir/coverage.xml" \
        --coverage-php="/app/$report_dir/coverage.php" \
        --log-junit="/app/$report_dir/junit.xml" "$@"
else
    compose run --rm --no-deps php --testsuite unit "$@"
fi
