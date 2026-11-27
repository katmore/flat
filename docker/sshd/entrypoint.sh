#!/bin/sh
# One host key, new at every start; its public half goes to /shared so that the tests can work out its fingerprint. The key file the
# tests add their client keys to is writable by the tests' user (the server reads it at each login, so no restart is needed).
set -eu
ssh-keygen -q -t ed25519 -N '' -f /etc/ssh/ssh_host_ed25519_key
cp /etc/ssh/ssh_host_ed25519_key.pub /shared/host_key.pub
chmod 644 /shared/host_key.pub
: > /shared/authorized_keys
chmod 666 /shared/authorized_keys
/usr/sbin/sshd -e -f /etc/ssh/sshd_config_no_sessions
exec /usr/sbin/sshd -D -e -f /etc/ssh/sshd_config
