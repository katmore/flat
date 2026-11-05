#!/bin/sh
# Installs Composer dependencies (into the named vendor volume) on first
# run, then executes the given command. Everything runs in-container;
# nothing is installed on the host.
set -e

if [ ! -x vendor/bin/phpunit ]; then
   composer update --no-interaction --prefer-dist
fi

exec "$@"
