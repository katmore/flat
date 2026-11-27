# The SSH server of the tests (the ssh tests in tests/Unit/Core/SshTest.php). For local testing only. Its user and password are fake and
# only this server knows them. Alpine's packages are signed and checked by apk itself, so plain HTTP mirrors are safe here (and need
# no proxy CA on build hosts that intercept TLS).
FROM alpine:3.24@sha256:f59abe656b692d901e184be3445e084786602108599d2a51d229c0f5a48c71bf
RUN printf 'http://dl-cdn.alpinelinux.org/alpine/v3.24/main\nhttp://dl-cdn.alpinelinux.org/alpine/v3.24/community\n' > /etc/apk/repositories \
    && apk add --no-cache openssh-server openssh-sftp-server \
    && adduser -D -s /bin/sh flat && echo 'flat:flat-ssh-test-password' | chpasswd
COPY sshd_config sshd_config_no_sessions /etc/ssh/
COPY entrypoint.sh /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
