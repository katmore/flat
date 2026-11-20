<?php
namespace flat\test\Support {
    class SshBoundary
    {
        public static $active = false;
        public static $available;
        public static $connectOk;
        public static $passwordOk;
        public static $pubkeyOk;
        public static $execReplies;
        public static $execCalls;
        public static $connections;
        public static $callbacks;
        public static $streams;
        public static $processes;
        public static $pipes;
        public static $lastStderr;
        public static $processFailure;
        public static $stderrError;
        public static $emptyKey;
        public static $passphrase;
        public static $fingerprint;
        public static $pubkeyCalls;
        public static $keyPaths;
        public static $sleeps;
        public static $clock;
        public static $remoteOpenFailure;
        public static $localOpenFailure;
        public static $localTarget;
        public static $remoteBody;
        public static $microtimes;
        public static $readFailure;
        public static $remoteSize;
        public static function reset()
        {
            self::$active = self::$available = self::$connectOk = self::$passwordOk = self::$pubkeyOk = true;
            self::$execReplies = self::$execCalls = self::$streams = self::$processes = self::$pipes = self::$pubkeyCalls = self::$keyPaths = self::$sleeps = [];
            self::$connections = 0; self::$callbacks = []; self::$lastStderr = self::$clock = self::$localTarget = null;
            self::$processFailure = self::$stderrError = self::$emptyKey = self::$remoteOpenFailure = self::$localOpenFailure = false;
            self::$passphrase = str_repeat('p',128); self::$fingerprint = 'local-fingerprint'; self::$remoteBody = 'remote body'; self::$microtimes=[]; self::$readFailure=false; self::$remoteSize=null;
        }
        public static function stream($contents = '')
        {
            $stream = \fopen('php://temp','w+'); \fwrite($stream,$contents); \rewind($stream);
            self::$streams[] = $stream; return $stream;
        }
        public static function close()
        {
            // Closures created in instance methods retain their SSH instance.
            // Release them while the transport boundary can still service the
            // destructor, before closing its streams and restoring native calls.
            self::$callbacks = [];
            \gc_collect_cycles();
            foreach (self::$pipes as $pipe) if (is_resource($pipe)) \fclose($pipe);
            foreach (self::$processes as $process) if (is_resource($process)) \proc_close($process);
            foreach (self::$streams as $stream) if (is_resource($stream)) \fclose($stream);
            foreach (self::$keyPaths as $path) if (is_file($path)) \unlink($path);
            self::$active = false;
        }
    }
}
namespace flat\core {
    use flat\test\Support\SshBoundary as Boundary;
    function function_exists($name)
    {
        if (Boundary::$active && $name === 'ssh2_connect') return Boundary::$available;
        return \function_exists($name);
    }
    function ssh2_connect($host,$port,$methods = [],$callbacks = [])
    {
        if (!Boundary::$active) return \ssh2_connect($host,$port,$methods,$callbacks);
        Boundary::$connections++; Boundary::$callbacks = $callbacks;
        return Boundary::$connectOk ? Boundary::stream() : false;
    }
    function ssh2_auth_password($connection,$username,$password)
    {
        return Boundary::$active ? Boundary::$passwordOk : \ssh2_auth_password($connection,$username,$password);
    }
    function ssh2_auth_pubkey_file($connection,$username,$public,$private,$passphrase = null)
    {
        if (!Boundary::$active) return \ssh2_auth_pubkey_file($connection,$username,$public,$private,$passphrase);
        $pem = \file_get_contents($private);
        $key = \openssl_pkey_get_private($pem,$passphrase);
        Boundary::$pubkeyCalls[] = ['public'=>$public,'private'=>$private,'parsed'=>is_resource($key),'passphrase'=>$passphrase,
            // An encrypted PEM (traditional or PKCS#8) says so in its header.
            'encrypted'=>strpos($pem,'ENCRYPTED') !== false];
        if (is_resource($key)) \openssl_pkey_free($key);
        return Boundary::$pubkeyOk;
    }
    function ssh2_fingerprint($connection)
    {
        return Boundary::$active ? Boundary::$fingerprint : \ssh2_fingerprint($connection);
    }
    function ssh2_exec($connection,$command)
    {
        if (!Boundary::$active) return \ssh2_exec($connection,$command);
        Boundary::$execCalls[] = $command;
        $reply = count(Boundary::$execReplies) ? array_shift(Boundary::$execReplies) : '';
        return $reply === false ? false : Boundary::stream($reply);
    }
    function ssh2_sftp($connection)
    {
        return Boundary::$active ? Boundary::stream() : \ssh2_sftp($connection);
    }
    function exec($command,&$output = null,&$returnCode = null)
    {
        if (Boundary::$active && strpos($command,'/dev/urandom') !== false) return Boundary::$passphrase;
        return \exec($command,$output,$returnCode);
    }
    function proc_open($command,$descriptors,&$pipes,$cwd = null,$environment = null,$options = [])
    {
        if (!Boundary::$active) return \proc_open($command,$descriptors,$pipes,$cwd,$environment,$options);
        if (Boundary::$processFailure) return false;
        if (Boundary::$stderrError || Boundary::$emptyKey) $command = 'cat >/dev/null';
        $process = \proc_open($command,$descriptors,$pipes,$cwd,$environment,$options);
        if (is_resource($process)) {
            Boundary::$processes[] = $process;
            foreach ($pipes as $pipe) Boundary::$pipes[] = $pipe;
            Boundary::$lastStderr = $pipes[2];
        }
        return $process;
    }
    function stream_get_contents($stream,$length = -1,$offset = -1)
    {
        if (Boundary::$active && Boundary::$stderrError && $stream === Boundary::$lastStderr) return 'controlled openssl error';
        return \stream_get_contents($stream,$length,$offset);
    }
    function tempnam($directory,$prefix)
    {
        $path = \tempnam($directory,$prefix);
        if (Boundary::$active && in_array($prefix,['sfnpub','sfnpriv','sfnout'],true)) Boundary::$keyPaths[] = $path;
        return $path;
    }
    function sleep($seconds)
    {
        if (Boundary::$active) { Boundary::$sleeps[] = $seconds; return 0; }
        return \sleep($seconds);
    }
    function time()
    {
        if (Boundary::$active && Boundary::$clock !== null) return Boundary::$clock++;
        return \time();
    }
    function fopen($path,$mode,...$options)
    {
        if (Boundary::$active && strpos($path,'ssh2.sftp://') === 0) return Boundary::$remoteOpenFailure ? false : Boundary::stream(Boundary::$remoteBody);
        if (Boundary::$active && Boundary::$localOpenFailure && $path === Boundary::$localTarget) return false;
        $stream = \fopen($path,$mode,...$options);
        if (Boundary::$active && is_resource($stream)) Boundary::$streams[] = $stream;
        return $stream;
    }
    function microtime($asFloat = false)
    {
        if (Boundary::$active && count(Boundary::$microtimes)) return array_shift(Boundary::$microtimes);
        return \microtime($asFloat);
    }
    function fread($stream,$length)
    {
        if (Boundary::$active && Boundary::$readFailure) return false;
        return \fread($stream,$length);
    }
    function filesize($path)
    {
        if (Boundary::$active && strpos($path,'ssh2.sftp://') === 0) return Boundary::$remoteSize ?? strlen(Boundary::$remoteBody);
        return \filesize($path);
    }
}
