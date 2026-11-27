# syntax=docker/dockerfile:1
FROM composer:2.11@sha256:5b2137f112f939a92788ceb5e55f54859d9dbf467a44037cf7760bdda949f365 AS dependencies
WORKDIR /opt/flat-test
COPY tests/docker/tools/composer.json tests/docker/tools/composer.lock tools/
COPY tests/docker/runtime/composer.json tests/docker/runtime/composer.lock runtime/
RUN --mount=type=secret,id=proxy_ca \
    task_ca=/etc/ssl/certs/ca-certificates.crt; \
    if [ -f /run/secrets/proxy_ca ]; then task_ca=/run/secrets/proxy_ca; fi; \
    export COMPOSER_CAFILE="$task_ca" GIT_SSL_CAINFO="$task_ca"; \
    composer install --working-dir=tools --no-interaction --no-progress --prefer-dist --no-plugins --no-scripts && \
    composer install --working-dir=runtime --ignore-platform-req=ext-sockets --no-interaction --no-progress --prefer-dist --no-plugins --no-scripts

FROM php:8.0.30-cli@sha256:0569e384b9064c04dec55dc6e41be41b494a878dfbb6577a7d76bd50cfd5bc00
# The exact PHP 8.0.30 runtime used by the compatibility suite. No apt repositories
# or private prebuilt images are needed. Optional session CA trust is mounted,
# never copied into an image layer.
RUN --mount=type=secret,id=proxy_ca \
    set -eu; task_ca=/etc/ssl/certs/ca-certificates.crt; \
    if [ -f /run/secrets/proxy_ca ]; then task_ca=/run/secrets/proxy_ca; fi; \
    curl --cacert "$task_ca" -fsSL https://codeload.github.com/xdebug/xdebug/tar.gz/127bbcb980400752221cfaa54bdc1420e6ef3c12 -o /tmp/source.tar.gz; \
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
    curl --cacert "$task_ca" -fsSL https://codeload.github.com/mongodb/mongo-php-driver/tar.gz/7dc124cb54ca21cd9987bc9cd08a73dd52e60f97 -o /tmp/mongodb.tar.gz; \
    curl --cacert "$task_ca" -fsSL https://codeload.github.com/mongodb/mongo-c-driver/tar.gz/97f166d8d784d6096d48ba288f98b48028cdfe8b -o /tmp/mongoc.tar.gz; \
    mkdir /tmp/mongodb; tar -xzf /tmp/mongodb.tar.gz -C /tmp/mongodb --strip-components=1; \
    mkdir -p /tmp/mongodb/src/libmongoc; tar -xzf /tmp/mongoc.tar.gz -C /tmp/mongodb/src/libmongoc --strip-components=1; \
    cd /tmp/mongodb; phpize; ./configure --with-mongodb-ssl=no; make -j2; make install; \
    docker-php-ext-enable mongodb; rm -rf /tmp/mongodb /tmp/mongodb.tar.gz /tmp/mongoc.tar.gz
# SSH: OpenSSL, libssh2 and the PHP ssh2 extension (flat\core\ssh uses ssh2_connect, ssh2_auth_password, ssh2_auth_pubkey_file,
# ssh2_fingerprint, ssh2_exec and ssh2_sftp), all from pinned sources: the base image's Debian 11 is end of life, so no apt. OpenSSL is
# built static and position-independent, and so is libssh2, so that ssh2.so carries its own copy of both and the OpenSSL that PHP itself
# uses stays untouched.
RUN --mount=type=secret,id=proxy_ca \
    set -eu; task_ca=/etc/ssl/certs/ca-certificates.crt; \
    if [ -f /run/secrets/proxy_ca ]; then task_ca=/run/secrets/proxy_ca; fi; \
    curl --cacert "$task_ca" -fsSL https://github.com/openssl/openssl/releases/download/openssl-3.6.5/openssl-3.6.5.tar.gz -o /tmp/openssl.tar.gz; \
    echo "a2157c2830efdec3788939b00c9b0638306d3f0bbb76dc4832ee503bb397df98  /tmp/openssl.tar.gz" | sha256sum -c -; \
    mkdir /tmp/openssl; tar -xzf /tmp/openssl.tar.gz -C /tmp/openssl --strip-components=1; \
    cd /tmp/openssl; ./config no-shared no-tests -fPIC --prefix=/opt/openssl --openssldir=/opt/openssl --libdir=lib; \
    make -j2; make install_sw; rm -rf /tmp/openssl /tmp/openssl.tar.gz
RUN --mount=type=secret,id=proxy_ca \
    set -eu; task_ca=/etc/ssl/certs/ca-certificates.crt; \
    if [ -f /run/secrets/proxy_ca ]; then task_ca=/run/secrets/proxy_ca; fi; \
    curl --cacert "$task_ca" -fsSL https://github.com/libssh2/libssh2/releases/download/libssh2-1.11.1/libssh2-1.11.1.tar.gz -o /tmp/libssh2.tar.gz; \
    echo "d9ec76cbe34db98eec3539fe2c899d26b0c837cb3eb466a56b0f109cabf658f7  /tmp/libssh2.tar.gz" | sha256sum -c -; \
    mkdir /tmp/libssh2; tar -xzf /tmp/libssh2.tar.gz -C /tmp/libssh2 --strip-components=1; \
    cd /tmp/libssh2; ./configure --prefix=/usr/local --disable-shared --enable-static --with-pic --with-crypto=openssl --with-libssl-prefix=/opt/openssl \
        --without-libz --disable-examples-build --disable-docker-tests; \
    make -j2; make install; rm -rf /tmp/libssh2 /tmp/libssh2.tar.gz
RUN --mount=type=secret,id=proxy_ca \
    set -eu; task_ca=/etc/ssl/certs/ca-certificates.crt; \
    if [ -f /run/secrets/proxy_ca ]; then task_ca=/run/secrets/proxy_ca; fi; \
    curl --cacert "$task_ca" -fsSL https://codeload.github.com/php/pecl-networking-ssh2/tar.gz/65c0da1b18373e2286d1569a176101583225a2c0 -o /tmp/ssh2.tar.gz; \
    mkdir /tmp/ssh2; tar -xzf /tmp/ssh2.tar.gz -C /tmp/ssh2 --strip-components=1; \
    cd /tmp/ssh2; phpize; \
    LIBS="-L/opt/openssl/lib -lssl -lcrypto -ldl -lpthread" ./configure --with-ssh2=/usr/local; \
    make -j2 SSH2_SHARED_LIBADD="-L/usr/local/lib -lssh2 -L/opt/openssl/lib -lssl -lcrypto -ldl -lpthread"; make install; \
    docker-php-ext-enable ssh2; rm -rf /tmp/ssh2 /tmp/ssh2.tar.gz
RUN docker-php-ext-install sockets
# Report everything, always: PHP's own development configuration (error_reporting = E_ALL).
# Never lower this, and never switch off deprecations or warnings (AGENTS.md).
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini
COPY --from=dependencies /opt/flat-test /opt/flat-test
COPY tests/docker/bootstrap.php /opt/flat-test/bootstrap.php
RUN chmod -R a+rX /opt/flat-test
RUN php -r 'if (PHP_VERSION !== "8.0.30") { throw new RuntimeException("Unexpected PHP version"); } if (error_reporting() !== E_ALL) { throw new RuntimeException("error_reporting is not E_ALL"); } foreach (["curl","dom","imagick","mongodb","pdo_sqlite","sockets","ssh2","xdebug"] as $extension) { if (!extension_loaded($extension)) { throw new RuntimeException("Missing extension: ".$extension); } } require "/opt/flat-test/tools/vendor/autoload.php"; require "/opt/flat-test/runtime/vendor/autoload.php"; echo "Verified PHP ".PHP_VERSION." and locked test dependencies\n";'
WORKDIR /app
ENTRYPOINT ["php", "/opt/flat-test/tools/vendor/bin/phpunit", "--bootstrap", "/opt/flat-test/bootstrap.php"]
