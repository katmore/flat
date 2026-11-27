<?php
namespace flat\test\Support;

/**
 * The OpenSSH server of the test stack (Compose service sshd, docker/sshd): where it is, how to log in, and the files it shares with
 * the tests in /shared. The server reads /shared/authorized_keys at each login, so a test adds its own client key and removes it
 * again; the server's host key is new at every start and its public half is in /shared/host_key.pub.
 */
class SshServer
{
    const SHARED = '/shared';

    private static function env($name)
    {
        $value = getenv($name);
        if ($value === false || $value === '') {
            throw new \RuntimeException("$name is not set: run these tests with docker/bin/test (all or services)");
        }
        return $value;
    }

    /** flat\core\ssh configuration for the server; $changes replace entries (a null value removes one). */
    public static function config(array $changes = [])
    {
        $config = ['host'=>self::env('FLAT_TEST_SSH_HOST'), 'port'=>(int) self::env('FLAT_TEST_SSH_PORT'),
            'username'=>self::env('FLAT_TEST_SSH_USER'), 'password'=>self::env('FLAT_TEST_SSH_PASSWORD')];
        return array_filter(array_replace($config, $changes), function ($value) { return $value !== null; });
    }

    /** The same server's second port, which logs in but refuses every session channel (exec fails). */
    public static function noSessionsPort() { return (int) self::env('FLAT_TEST_SSH_NO_SESSIONS_PORT'); }

    /** A closed port of the server's host: connecting to it fails. */
    public static function closedPort() { return 9; }

    /** Allows logins with a key: $publicKey is a line in OpenSSH's authorized_keys format. */
    public static function authorize($publicKey)
    {
        file_put_contents(self::SHARED.'/authorized_keys', $publicKey."\n", FILE_APPEND);
    }

    /** Forgets every authorized key. */
    public static function revoke()
    {
        file_put_contents(self::SHARED.'/authorized_keys', '');
    }

    /** The fingerprint ssh2_fingerprint() reports for the server's host key: MD5 of the key, as upper-case hexadecimal. */
    public static function hostFingerprint()
    {
        $line = trim(file_get_contents(self::SHARED.'/host_key.pub'));
        return strtoupper(md5(base64_decode(explode(' ', $line)[1])));
    }

    /** An RSA private key (PEM) with a passphrase if given, and its public key as an authorized_keys line. */
    public static function newKey($passphrase = null)
    {
        $key = openssl_pkey_new(['private_key_bits'=>2048, 'private_key_type'=>OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($key, $private, $passphrase);
        $rsa = openssl_pkey_get_details($key)['rsa'];
        $string = function ($value) { return pack('N', strlen($value)).$value; };
        $mpint = function ($value) use ($string) { return $string(ord($value[0]) & 0x80 ? "\0".$value : $value); };
        $blob = $string('ssh-rsa').$mpint($rsa['e']).$mpint($rsa['n']);
        return [$private, 'ssh-rsa '.base64_encode($blob).' flat-test'];
    }
}
