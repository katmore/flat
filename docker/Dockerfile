# syntax=docker/dockerfile:1
FROM composer:2.11@sha256:50ea4d33fdcde1adb0f045547295faa1548b33872e5f789dae5b02ce2fb5de93 AS dependencies
WORKDIR /opt/flat-test
COPY tests/docker/tools/composer.json tests/docker/tools/composer.lock tools/
COPY tests/docker/runtime/composer.json tests/docker/runtime/composer.lock runtime/
RUN --mount=type=secret,id=proxy_ca \
    task_ca=/etc/ssl/certs/ca-certificates.crt; \
    if [ -f /run/secrets/proxy_ca ]; then task_ca=/run/secrets/proxy_ca; fi; \
    export COMPOSER_CAFILE="$task_ca" GIT_SSL_CAINFO="$task_ca"; \
    composer install --working-dir=tools --no-interaction --no-progress --prefer-dist --no-plugins --no-scripts && \
    composer install --working-dir=runtime --ignore-platform-req=ext-sockets --no-interaction --no-progress --prefer-dist --no-plugins --no-scripts

FROM php:7.3-cli@sha256:bd6f2dc7a4e4537e927355c94a74c3022911aa504278f5f0fd94016c90dfaecc
# The exact PHP 7.3.33 runtime used by the compatibility suite. No apt repositories
# or private prebuilt images are needed. Optional session CA trust is mounted,
# never copied into an image layer.
RUN --mount=type=secret,id=proxy_ca \
    set -eu; task_ca=/etc/ssl/certs/ca-certificates.crt; \
    if [ -f /run/secrets/proxy_ca ]; then task_ca=/run/secrets/proxy_ca; fi; \
    curl --cacert "$task_ca" -fsSL https://codeload.github.com/xdebug/xdebug/tar.gz/3e09864af111d63b7eb486d88bfc6eb7d6429b85 -o /tmp/source.tar.gz; \
    mkdir /tmp/xdebug; tar -xzf /tmp/source.tar.gz -C /tmp/xdebug --strip-components=1; \
    cd /tmp/xdebug; phpize; ./configure --enable-xdebug; make -j2; make install; \
    docker-php-ext-enable xdebug; rm -rf /tmp/xdebug /tmp/source.tar.gz
RUN --mount=type=secret,id=proxy_ca \
    set -eu; task_ca=/etc/ssl/certs/ca-certificates.crt; \
    if [ -f /run/secrets/proxy_ca ]; then task_ca=/run/secrets/proxy_ca; fi; \
    curl --cacert "$task_ca" -fsSL https://codeload.github.com/ImageMagick/ImageMagick6/tar.gz/e97831dfdfc064c5246cd7ba195ff6ecd4958f34 -o /tmp/source.tar.gz; \
    mkdir /tmp/magick; tar -xzf /tmp/source.tar.gz -C /tmp/magick --strip-components=1; \
    cd /tmp/magick; ./configure --prefix=/usr/local --without-perl --without-x --disable-openmp --disable-docs; \
    make -j2; make install; ldconfig; rm -rf /tmp/magick /tmp/source.tar.gz
RUN --mount=type=secret,id=proxy_ca \
    set -eu; task_ca=/etc/ssl/certs/ca-certificates.crt; \
    if [ -f /run/secrets/proxy_ca ]; then task_ca=/run/secrets/proxy_ca; fi; \
    curl --cacert "$task_ca" -fsSL https://codeload.github.com/Imagick/imagick/tar.gz/42c07db5d0075b26471397fb6de8ffb4bd2692d8 -o /tmp/source.tar.gz; \
    mkdir /tmp/imagick; tar -xzf /tmp/source.tar.gz -C /tmp/imagick --strip-components=1; \
    cd /tmp/imagick; phpize; ./configure; make -j2; make install; \
    docker-php-ext-enable imagick; rm -rf /tmp/imagick /tmp/source.tar.gz
RUN --mount=type=secret,id=proxy_ca \
    set -eu; task_ca=/etc/ssl/certs/ca-certificates.crt; \
    if [ -f /run/secrets/proxy_ca ]; then task_ca=/run/secrets/proxy_ca; fi; \
    curl --cacert "$task_ca" -fsSL https://codeload.github.com/mongodb/mongo-php-driver/tar.gz/9d6c1ba3e761b5743ec758fe0d1904f2df86ef3e -o /tmp/mongodb.tar.gz; \
    curl --cacert "$task_ca" -fsSL https://codeload.github.com/mongodb/mongo-c-driver/tar.gz/9063a5c2c78bfb6d3344f654ad59567f62f62ddd -o /tmp/mongoc.tar.gz; \
    mkdir /tmp/mongodb; tar -xzf /tmp/mongodb.tar.gz -C /tmp/mongodb --strip-components=1; \
    mkdir -p /tmp/mongodb/src/libmongoc; tar -xzf /tmp/mongoc.tar.gz -C /tmp/mongodb/src/libmongoc --strip-components=1; \
    cd /tmp/mongodb; phpize; ./configure --with-mongodb-ssl=no; make -j2; make install; \
    docker-php-ext-enable mongodb; rm -rf /tmp/mongodb /tmp/mongodb.tar.gz /tmp/mongoc.tar.gz
RUN docker-php-ext-install sockets
# Report everything, always: PHP's own development configuration (error_reporting = E_ALL).
# Never lower this, and never switch off deprecations or warnings (AGENTS.md).
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini
COPY --from=dependencies /opt/flat-test /opt/flat-test
COPY tests/docker/bootstrap.php /opt/flat-test/bootstrap.php
RUN chmod -R a+rX /opt/flat-test
RUN php -r 'if (PHP_VERSION !== "7.3.33") { throw new RuntimeException("Unexpected PHP version"); } if (error_reporting() !== E_ALL) { throw new RuntimeException("error_reporting is not E_ALL"); } foreach (["curl","dom","imagick","mongodb","pdo_sqlite","sockets","xdebug"] as $extension) { if (!extension_loaded($extension)) { throw new RuntimeException("Missing extension: ".$extension); } } require "/opt/flat-test/tools/vendor/autoload.php"; require "/opt/flat-test/runtime/vendor/autoload.php"; echo "Verified PHP ".PHP_VERSION." and locked test dependencies\n";'
WORKDIR /app
ENTRYPOINT ["php", "/opt/flat-test/tools/vendor/bin/phpunit", "--bootstrap", "/opt/flat-test/bootstrap.php"]
