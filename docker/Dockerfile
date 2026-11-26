# syntax=docker/dockerfile:1
FROM composer:2.11@sha256:0049ce90b432795bf413a738b073963ada1a689e1a8708104684baafe94586ec AS dependencies
WORKDIR /opt/flat-test
COPY tests/docker/tools/composer.json tests/docker/tools/composer.lock tools/
COPY tests/docker/runtime/composer.json tests/docker/runtime/composer.lock runtime/
RUN --mount=type=secret,id=proxy_ca \
    task_ca=/etc/ssl/certs/ca-certificates.crt; \
    if [ -f /run/secrets/proxy_ca ]; then task_ca=/run/secrets/proxy_ca; fi; \
    export COMPOSER_CAFILE="$task_ca" GIT_SSL_CAINFO="$task_ca"; \
    composer install --working-dir=tools --no-interaction --no-progress --prefer-dist --no-plugins --no-scripts && \
    composer install --working-dir=runtime --ignore-platform-req=ext-sockets --no-interaction --no-progress --prefer-dist --no-plugins --no-scripts

FROM php:7.4-cli@sha256:620a6b9f4d4feef2210026172570465e9d0c1de79766418d3affd09190a7fda5
# The exact PHP 7.4.33 runtime used by the compatibility suite. No apt repositories
# or private prebuilt images are needed. Optional session CA trust is mounted,
# never copied into an image layer.
RUN --mount=type=secret,id=proxy_ca \
    set -eu; task_ca=/etc/ssl/certs/ca-certificates.crt; \
    if [ -f /run/secrets/proxy_ca ]; then task_ca=/run/secrets/proxy_ca; fi; \
    curl --cacert "$task_ca" -fsSL https://codeload.github.com/xdebug/xdebug/tar.gz/78ef02f7374db83e152751b4567b5a1bc9d9e86a -o /tmp/source.tar.gz; \
    mkdir /tmp/xdebug; tar -xzf /tmp/source.tar.gz -C /tmp/xdebug --strip-components=1; \
    cd /tmp/xdebug; phpize; ./configure --enable-xdebug; make -j2; make install; \
    docker-php-ext-enable xdebug; \
    echo 'xdebug.mode=develop,coverage' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    rm -rf /tmp/xdebug /tmp/source.tar.gz
RUN --mount=type=secret,id=proxy_ca \
    set -eu; task_ca=/etc/ssl/certs/ca-certificates.crt; \
    if [ -f /run/secrets/proxy_ca ]; then task_ca=/run/secrets/proxy_ca; fi; \
    curl --cacert "$task_ca" -fsSL https://codeload.github.com/ImageMagick/ImageMagick/tar.gz/7289289c644dc34e1e53b7b483a1b6f21758eac8 -o /tmp/source.tar.gz; \
    mkdir /tmp/magick; tar -xzf /tmp/source.tar.gz -C /tmp/magick --strip-components=1; \
    cd /tmp/magick; ./configure --prefix=/usr/local --without-perl --without-x --disable-openmp --disable-docs; \
    make -j2; make install; ldconfig; rm -rf /tmp/magick /tmp/source.tar.gz
RUN --mount=type=secret,id=proxy_ca \
    set -eu; task_ca=/etc/ssl/certs/ca-certificates.crt; \
    if [ -f /run/secrets/proxy_ca ]; then task_ca=/run/secrets/proxy_ca; fi; \
    curl --cacert "$task_ca" -fsSL https://codeload.github.com/Imagick/imagick/tar.gz/70087bab33eab913e99ac77d64d04d1a2fd0b7b0 -o /tmp/source.tar.gz; \
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
RUN php -r 'if (PHP_VERSION !== "7.4.33") { throw new RuntimeException("Unexpected PHP version"); } if (error_reporting() !== E_ALL) { throw new RuntimeException("error_reporting is not E_ALL"); } foreach (["curl","dom","imagick","mongodb","pdo_sqlite","sockets","xdebug"] as $extension) { if (!extension_loaded($extension)) { throw new RuntimeException("Missing extension: ".$extension); } } require "/opt/flat-test/tools/vendor/autoload.php"; require "/opt/flat-test/runtime/vendor/autoload.php"; echo "Verified PHP ".PHP_VERSION." and locked test dependencies\n";'
WORKDIR /app
ENTRYPOINT ["php", "/opt/flat-test/tools/vendor/bin/phpunit", "--bootstrap", "/opt/flat-test/bootstrap.php"]
