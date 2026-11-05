# flat framework - development / test image
#
# Pins the last PHP version this framework targeted:
#   composer.json requires php >=7.2.1, last commit 2020-03-09.
# PHP 7.2 is end-of-life; it is used here deliberately as the
# pre-modernization baseline. Do not "fix" the version in this file
# without updating the test baseline with it.
FROM php:7.2-cli

# Debian buster is archived; point apt at archive.debian.org.
RUN sed -i \
      -e 's|http://deb.debian.org/debian|http://archive.debian.org/debian|g' \
      -e 's|http://security.debian.org/debian-security|http://archive.debian.org/debian-security|g' \
      -e '/buster-updates/d' \
      /etc/apt/sources.list \
 && apt-get update \
 && apt-get install -y --no-install-recommends git unzip libssl-dev \
 && rm -rf /var/lib/apt/lists/*

# Extension required by the mongodb/mongodb runtime dependency.
RUN pecl install mongodb-1.5.5 \
 && docker-php-ext-enable mongodb

# Coverage driver, used only when coverage is requested, e.g.:
#   php -d zend_extension=xdebug.so vendor/bin/phpunit --coverage-text
# It is built but not enabled for normal test runs.
RUN pecl install xdebug-2.9.8

# Composer 2.2 LTS: the Composer line suited to PHP 7.2.
COPY --from=composer:2.2 /usr/bin/composer /usr/local/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer

WORKDIR /app

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["vendor/bin/phpunit", "--testsuite", "unit"]
