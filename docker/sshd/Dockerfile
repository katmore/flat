# The SSH server of the tests (the ssh tests in tests/Unit/Core/SshTest.php). For local testing only. Its user and password are fake and
# only this server knows them. Use HTTPS mirrors with an optional build-time proxy CA.
FROM alpine:3.24@sha256:f59abe656b692d901e184be3445e084786602108599d2a51d229c0f5a48c71bf
RUN --mount=type=secret,id=proxy_ca \
    set -eu; task_ca=/etc/ssl/certs/ca-certificates.crt; \
    if [ -f /run/secrets/proxy_ca ]; then task_ca=/run/secrets/proxy_ca; fi; \
    printf 'https://dl-cdn.alpinelinux.org/alpine/v3.24/main\nhttps://dl-cdn.alpinelinux.org/alpine/v3.24/community\n' > /etc/apk/repositories \
    && SSL_CERT_FILE="$task_ca" apk add --no-cache openssh-server openssh-sftp-server \
    && adduser -D -s /bin/sh flat && echo 'flat:flat-ssh-test-password' | chpasswd
COPY sshd_config sshd_config_no_sessions /etc/ssh/
COPY entrypoint.sh /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
