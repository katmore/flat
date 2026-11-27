<?php
namespace flat\test\Unit\Core;
use flat\core\ssh;
use flat\test\Support\SshServer;
use PHPUnit\Framework\TestCase;

/** Records every command that reaches the remote shell, with the time it was sent. */
class RecordingSsh extends ssh
{
    public $commands = [];
    protected function exec_output($command) { $this->commands[] = [$command, microtime(true)]; return $this->exec($command); }
}
/** Answers the commands of the process checks and signals from a list instead of asking the remote shell (the exec_output() hook). */
class ScriptedSsh extends ssh
{
    public $replies = [];
    protected function exec_output($command) { return array_shift($this->replies); }
}
/** The remote shell answers nothing at all. */
class SilentSsh extends ssh
{
    protected function exec_output($command) { return ''; }
}

/**
 * flat\core\ssh against a real OpenSSH server (docker/sshd) with the real ssh2 extension: real logins, real keys, real processes,
 * real files. Remote processes are real too: they are started with `sleep` and killed by the test that started them.
 *
 * @group services
 * @group ssh
 */
class SshServerTest extends TestCase
{
    use \flat\test\Support\PhpSubprocess;
    private $clients = [];
    private $pids = [];
    private $keyFilesBefore;

    protected function setUp(): void
    {
        SshServer::revoke();
        $this->keyFilesBefore = $this->keyFiles();
    }
    protected function tearDown(): void
    {
        if ($this->pids) {
            $cleaner = new ssh(SshServer::config(['connect'=>true]));
            foreach ($this->pids as $pid) $cleaner->exec('kill -9 '.(int) $pid.' 2>/dev/null; echo');
        }
        $this->clients = []; $this->pids = [];
        SshServer::revoke();
    }
    private function client(array $changes = [], $class = ssh::class)
    {
        $client = new $class(SshServer::config($changes)); $this->clients[] = $client; return $client;
    }
    private function connected($class = ssh::class)
    {
        return $this->client(['connect'=>true], $class);
    }
    /** The temporary files flat\core\ssh writes for a key login (sfnpub, sfnpriv, sfnout). */
    private function keyFiles() { return glob(sys_get_temp_dir().'/sfn*') ?: []; }
    /** A client that logs in with a new key: [client, private key, public key line]. */
    private function keyClient($passphrase = null, array $changes = [])
    {
        list($private, $public) = SshServer::newKey($passphrase);
        SshServer::authorize($public);
        $client = $this->client(['password'=>null,'pubkey'=>$public,'privkey'=>$private,'passphrase'=>$passphrase] + $changes);
        return $client;
    }
    /** Runs $call and expects it to throw $exception; returns the PHP warnings it raised (ssh2 reports failures as warnings) and the exception. */
    private function failsWith($exception, callable $call)
    {
        $warnings = [];
        set_error_handler(function ($level, $message) use (&$warnings) { $warnings[] = $message; return true; });
        try { $call(); }
        catch (\Throwable $e) { restore_error_handler(); $this->assertInstanceOf($exception, $e, $e->getMessage()); return [$warnings, $e]; }
        restore_error_handler();
        $this->fail("expected $exception");
    }
    /** Starts a remote process that lives for a minute; $command defaults to a plain sleep. Returns its pid. */
    private function remote($command = 'sleep 60')
    {
        $pid = (int) trim($this->connected()->exec($command.' > /dev/null 2>&1 & echo $!'));
        $this->assertGreaterThan(1, $pid);
        $this->pids[] = $pid;
        return $pid;
    }

    public function testPasswordConnectionAndExecution()
    {
        $client = $this->connected();
        $this->assertInstanceOf(ssh\auth\password::class, $client->get_auth());
        $this->assertTrue($client->is_connected());
        $this->assertSame("remote output\n", $client->exec('echo remote output'));
        $this->assertSame("flat\n", $client->exec('id -un'));
    }
    public function testDroppingAConnectedClientClosesItsSession()
    {
        $before = count(get_resources('SSH2 Session'));
        $client = new ssh(SshServer::config(['connect'=>true]));
        $this->assertCount($before + 1, get_resources('SSH2 Session'));
        unset($client);
        $this->assertCount($before, get_resources('SSH2 Session'));
    }
    public function testRepeatedConnectIsRejected()
    {
        $client = $this->connected();
        $this->expectException(ssh\exception\syserror::class);
        $client->connect();
    }
    public function testConnectionFailureIsReported()
    {
        $client = $this->client(['port'=>SshServer::closedPort()]);
        list($warnings) = $this->failsWith(ssh\exception\connection\failure::class, function () use ($client) { $client->connect(); });
        $this->assertNotEmpty($warnings);
        $this->assertStringContainsString('Unable to connect', $warnings[0]);
        $this->assertFalse($client->is_connected());
    }
    public function testPasswordFailureUsesTheExistingFrameworkException()
    {
        $client = $this->client(['password'=>'wrong']);
        list($warnings) = $this->failsWith(ssh\exception\auth\failure\password::class, function () use ($client) { $client->connect(); });
        $this->assertStringContainsString('Authentication failed', $warnings[0]);
    }
    public function passphrases() { return [[null], ['input-secret']]; }
    /** @dataProvider passphrases */
    public function testPublicKeyLoginWithARealKeyRemovesItsTemporaryFiles($passphrase)
    {
        $client = $this->keyClient($passphrase);
        $client->connect();
        $this->assertInstanceOf(ssh\auth\pubkey::class, $client->get_auth());
        $this->assertTrue($client->is_connected());
        $this->assertSame(SshServer::hostFingerprint(), $client->get_auth()->fingerprint);
        $this->assertSame("flat\n", $client->exec('id -un'));
        $this->assertSame($this->keyFilesBefore, $this->keyFiles());
    }
    public function testProvidedFingerprintIsChecked()
    {
        $client = $this->keyClient();
        $client->get_auth()->fingerprint = SshServer::hostFingerprint();
        $client->connect();
        $this->assertTrue($client->is_connected());
    }
    public function testFingerprintMismatchIsReported()
    {
        $client = $this->keyClient();
        $client->get_auth()->fingerprint = 'different';
        list(, $error) = $this->failsWith(ssh\exception\syserror::class, function () use ($client) { $client->connect(); });
        $this->assertSame('system error: fingerprint mismatch', $error->getMessage());
        $this->assertSame($this->keyFilesBefore, $this->keyFiles());
        $this->assertFalse($client->is_connected());
    }
    public function testPublicKeyTheServerDoesNotKnowIsRefused()
    {
        list($private, $public) = SshServer::newKey();
        $client = $this->client(['password'=>null,'pubkey'=>$public,'privkey'=>$private]);
        list($warnings) = $this->failsWith(ssh\exception\auth\failure\pubkey::class, function () use ($client) { $client->connect(); });
        $this->assertStringContainsString('Authentication failed', $warnings[0]);
        $this->assertSame($this->keyFilesBefore, $this->keyFiles());
    }
    public function testAKeyThatOpenSslCannotReadIsReportedAndLeavesNoFile()
    {
        $client = $this->client(['password'=>null,'pubkey'=>'ssh-rsa AAAA','privkey'=>'this is not a key']);
        list(, $error) = $this->failsWith(ssh\exception\syserror::class, function () use ($client) { $client->connect(); });
        $this->assertStringContainsString('could not encrypt plain private key with openssl', $error->getMessage());
        $this->assertSame($this->keyFilesBefore, $this->keyFiles());
    }
    /** Runs a key login in a fresh PHP process; returns [stdout, stderr]. */
    private function keyLoginInAFreshProcess(array $ini, array $env)
    {
        list($private, $public) = SshServer::newKey();
        SshServer::authorize($public);
        $body = '$config = \flat\test\Support\SshServer::config(["password"=>null, "pubkey"=>'.var_export($public, true).', "privkey"=>'.var_export($private, true).']);'
            .' set_error_handler(function ($level, $message) { echo "[warning: $message] "; return true; });'
            .' try { (new \flat\core\ssh($config))->connect(); echo "connected"; }'
            .' catch (\Throwable $e) { echo get_class($e), ": ", $e->getMessage(); }';
        list($status, $out, $err) = $this->subprocess($body, $ini, $env);
        $this->assertSame(0, $status, $out.$err);
        return [$out, $err];
    }
    public function testAKeyLoginPrintsNothingToStandardError()
    {
        list($out, $err) = $this->keyLoginInAFreshProcess([], getenv());
        $this->assertSame('connected', $out);
        $this->assertSame('', $err);
    }
    public function testTheTemporaryPassphraseMustBeGenerated()
    {
        // Without tr and head on the PATH the shell command that makes the passphrase prints nothing.
        list($out) = $this->keyLoginInAFreshProcess([], ['PATH'=>'/nonexistent'] + getenv());
        $this->assertSame('flat\core\ssh\exception\syserror: system error: could not generate temp passphrase for plain private key', $out);
        $this->assertSame($this->keyFilesBefore, $this->keyFiles());
    }
    public function testOpenSslMustBeUsable()
    {
        list($out) = $this->keyLoginInAFreshProcess(['disable_functions'=>'proc_open'], getenv());
        $this->assertSame('flat\core\ssh\exception\syserror: system error: could not use openssl', $out);
        $this->assertSame($this->keyFilesBefore, $this->keyFiles());
    }
    public function testOperationsNeedAConnectionAndAnUnavailableExtensionIsReported()
    {
        // The whole extension missing: the function is disabled in a fresh process, so function_exists() is false.
        $body = 'try { new \flat\core\ssh(\flat\test\Support\SshServer::config()); echo "no exception"; }'
            .' catch (\flat\core\ssh\exception\syserror $e) { echo get_class($e), ": ", $e->getMessage(); }';
        list($status, $out, $err) = $this->subprocess($body, ['disable_functions'=>'ssh2_connect']);
        $this->assertSame(0, $status, $err); $this->assertSame('', $err);
        $this->assertSame('flat\core\ssh\exception\syserror: system error: ssh2_* functions not available', $out);
    }
    public function testFailedRemoteExecutionIsReported()
    {
        // This port logs in but refuses every session channel, so the exec request fails.
        $client = $this->client(['port'=>SshServer::noSessionsPort(), 'connect'=>true]);
        list($warnings) = $this->failsWith(ssh\exception\syserror::class, function () use ($client) { $client->exec('echo hello'); });
        $this->assertNotEmpty($warnings);
        // Destruction must not open another command channel on this server.
        $before = count(get_resources('SSH2 Session'));
        $this->clients = []; unset($client);
        $this->assertCount($before - 1, get_resources('SSH2 Session'));
    }
    public function testDisconnectIgnoresMissingConnectionOnlyWhenRequested()
    {
        $before = count(get_resources('SSH2 Session'));
        $client = $this->client();
        $this->assertFalse($client->disconnect(true));
        $client->connect();
        $local = tempnam(sys_get_temp_dir(), 'flat-disconnect-');
        try {
            $client->exec('printf data > /tmp/flat-disconnect; echo');
            $client->sftp_get('/tmp/flat-disconnect', $local);
            $this->assertSame('data', file_get_contents($local));
        } finally { unlink($local); }
        $this->assertTrue($client->disconnect());
        $this->assertFalse($client->is_connected());
        $this->assertCount($before, get_resources('SSH2 Session'));
        try { $client->exec('echo must-not-run'); $this->fail('Expected missing connection'); }
        catch (ssh\exception\connection\missing $error) { $this->assertFalse($client->is_connected()); }
        $client->connect();
        $this->assertSame("reconnected\n", $client->exec('echo reconnected'));
        $this->assertTrue($client->disconnect());
    }
    /** @dataProvider invalidFingerprints */
    public function testPasswordFingerprintIsVerifiedBeforeAuthentication($fingerprint)
    {
        // Wrong credentials would emit a native warning if authentication ran first.
        $client = $this->client(['password'=>'wrong-password']);
        $client->get_auth()->fingerprint = $fingerprint;
        $this->expectException(ssh\exception\syserror::class);
        $this->expectExceptionMessage('fingerprint mismatch');
        $client->connect();
    }
    public function invalidFingerprints() { return [['wrong-host'], [0], ['0'], [false]]; }
    public function testAnEmptyFingerprintKeepsPasswordAuthenticationUnpinned()
    {
        $client = $this->client(['fingerprint'=>'', 'connect'=>true]);
        $this->assertTrue($client->is_connected());
        $this->assertSame('', $client->get_auth()->fingerprint);
    }
    public function testConstructorFingerprintWorksForPasswordAuthentication()
    {
        $client = $this->client(['fingerprint'=>SshServer::hostFingerprint(), 'connect'=>true]);
        $this->assertTrue($client->is_connected());
        $this->assertSame(SshServer::hostFingerprint(), $client->get_auth()->fingerprint);
    }
    public function testConstructorFingerprintRejectsTheWrongHostForBothAuthenticationModes()
    {
        list($private, $public) = SshServer::newKey();
        SshServer::authorize($public);
        foreach ([[], ['password'=>null, 'pubkey'=>$public, 'privkey'=>$private]] as $auth) {
            $client = $this->client($auth + ['fingerprint'=>'wrong-host']);
            try { $client->connect(); $this->fail('Expected fingerprint mismatch'); }
            catch (ssh\exception\syserror $error) {
                $this->assertSame('system error: fingerprint mismatch', $error->getMessage());
                $this->assertFalse($client->is_connected());
            }
        }
        $this->assertSame($this->keyFilesBefore, $this->keyFiles());
    }
    public function testProcessChecksSeeRealProcesses()
    {
        $client = $this->connected();
        $pid = $this->remote();
        $this->assertTrue($client->checkpid((string) $pid));
        $this->assertTrue($client->checkpid($pid, true));
        // pid_max is far below this number, so no such process can exist
        $this->assertFalse($client->checkpid(99999999, true));
    }
    public function testMissingRemoteProcessThrowsUnlessIgnored()
    {
        $client = $this->connected();
        $this->expectException(ssh\exception\exec\pid\not_running::class);
        $client->checkpid(99999999);
    }
    public function testBackgroundExecutionReturnsThePidOfARunningProcess()
    {
        $client = $this->connected();
        $pid = $client->exec_backround('sleep 60', '/tmp/flat-ssh-test.log', 0);
        $this->pids[] = (int) trim($pid);
        $this->assertMatchesRegularExpression('/^\d+\s*$/', $pid);
        $this->assertTrue($client->checkpid($pid));
    }
    public function testBackgroundExecutionWaitsTheRequestedRuntimeBeforeChecking()
    {
        $client = $this->connected();
        $start = microtime(true);
        $pid = $client->exec_backround('sleep 60', '/tmp/flat-ssh-test.log', 2);
        $this->pids[] = (int) trim($pid);
        $this->assertGreaterThanOrEqual(2.0, microtime(true) - $start);
        $this->assertTrue($client->checkpid($pid));
    }
    public function testBackgroundExecutionOfAProcessThatEndsEarlyFailsTheCheck()
    {
        $client = $this->connected();
        $this->expectException(ssh\exception\exec\pid\not_running::class);
        $client->exec_backround('true', '/tmp/flat-ssh-test.log', 1);
    }
    public function testAnInvalidRuntimeFallsBackToThreeSeconds()
    {
        $client = $this->connected();
        $start = microtime(true);
        $pid = $client->exec_backround('sleep 60', '/tmp/flat-ssh-test.log', 'invalid');
        $this->pids[] = (int) trim($pid);
        $this->assertGreaterThanOrEqual(3.0, microtime(true) - $start);
    }
    public function testBackgroundExecutionNeedsAPid()
    {
        $client = $this->connected(SilentSsh::class);
        $this->expectException(ssh\exception\exec\pid\missing::class);
        $client->exec_backround('command', 'logfile');
    }
    public function invalidKillArguments() { return [[['bad']],[[0]],[[-3]],[['42; id']],[[42,0]],[[42,16]],[[42,1,-1]],[[42,1,61]],[[42,1,0,0]],[[42,1,0,9,-1]],[[42,1,0,9,61]]]; }
    /** @dataProvider invalidKillArguments */
    public function testKillArgumentsAreValidatedBeforeSendingSignals($args)
    {
        $client = $this->connected(RecordingSsh::class);
        $this->failsWith(ssh\exception\input_error\bad_param::class, function () use ($client, $args) { $client->kill_process(...$args); });
        // Nothing may reach the shell before every argument has been validated.
        $this->assertSame([], $client->commands);
    }
    public function testAProcessThatDiesOfTheFirstSignalIsKilledAtOnce()
    {
        $client = $this->connected();
        $pid = $this->remote();
        $this->assertTrue($client->kill_process($pid, 1, 5, 9, 5));
        $this->assertFalse($client->checkpid($pid, true));
    }
    public function testAProcessThatIgnoresTheFirstSignalIsKilledByTheSecond()
    {
        $client = $this->connected();
        $pid = $this->remote('nohup sleep 60'); // nohup makes the process ignore SIGHUP
        $this->assertTrue($client->kill_process($pid, 1, 0, 9, 5));
        $this->assertFalse($client->checkpid($pid, true));
    }
    public function testKillWaitsForTheFirstSignalBeforeSendingTheSecond()
    {
        $client = $this->connected(RecordingSsh::class);
        $pid = $this->remote('nohup sleep 60');
        $start = microtime(true);
        $this->assertTrue($client->kill_process($pid, 1, 2, 9, 6));
        $commands = array_column($client->commands, 0);
        $this->assertStringContainsString('kill -1 '.$pid, $commands[0]);
        $nine = array_keys(array_filter($commands, function ($command) use ($pid) { return strpos($command, 'kill -9 '.$pid) !== false; }));
        $this->assertCount(1, $nine);
        // The first wait ran, polling the process, before the second signal went out. kill_process counts the wait in whole seconds of the
        // clock (time()), so a wait of 2 can end after just over 1 second of real time.
        $this->assertGreaterThanOrEqual(1.0, $client->commands[$nine[0]][1] - $start);
        $this->assertGreaterThan(2, $nine[0], 'the process is polled with kill -0 during the first wait');
        foreach (array_slice($commands, 1, $nine[0] - 1) as $poll) $this->assertStringContainsString('kill -0 '.$pid, $poll);
    }
    /** A zombie keeps its pid and answers `kill -0`, whatever signal it gets: the one real way a process "never exits". */
    private function zombie()
    {
        // The child waits until its parent has exec'd sleep before exiting, so the shell cannot reap it first.
        $client = $this->connected();
        $file = '/tmp/flat-zombie-'.bin2hex(random_bytes(6));
        $child = 'while [ "$(cat /proc/$1/comm)" != sleep ]; do sleep 0.01; done; exit 0';
        $parentCommand = 'sh -c '.escapeshellarg($child).' flat-child $$ & echo $! $$ > '.$file.'; exec sleep 100';
        $out = $client->exec('sh -c '.escapeshellarg($parentCommand).' > /dev/null 2>&1 & '
            .'i=0; while [ ! -s '.$file.' ] && [ "$i" -lt 500 ]; do i=$((i+1)); sleep 0.01; done; cat '.$file);
        list($zombie, $parent) = array_map('intval', preg_split('/\\s+/', trim($out)));
        $this->pids[] = $parent;
        $this->assertGreaterThan(1, $zombie);
        $deadline = microtime(true) + 5;
        do {
            $state = trim($client->exec("awk '{print $3}' /proc/".$zombie.'/stat'));
            if ($state === 'Z') break;
            usleep(10000);
        } while (microtime(true) < $deadline);
        $this->assertSame('Z', $state, 'the real child must be a zombie before signal testing');
        return $zombie;
    }
    public function testAProcessThatNeverExitsReportsTheTimeWaited()
    {
        $client = $this->connected();
        $zombie = $this->zombie();
        $this->assertTrue($client->checkpid($zombie));
        list(, $error) = $this->failsWith(ssh\exception\exec\pid\cannot_kill::class, function () use ($client, $zombie) { $client->kill_process($zombie, 1, 1, 9, 3); });
        $this->assertStringContainsString('after 3 seconds', $error->getMessage());
    }
    public function testASignalThatCannotBeSentIsReported()
    {
        $client = $this->connected();
        $this->expectException(ssh\exception\exec\pid\kill::class);
        $client->kill_process(99999999, 1, 0, 9, 0);
    }
    public function testASecondSignalThatCannotBeSentIsReported()
    {
        // The process exits between the check and the second signal: kill -9 then fails, which cannot be arranged with a real process.
        $client = $this->connected(ScriptedSsh::class);
        $client->replies = ['0', '0', '1']; // kill -1 sent; still running; kill -9 fails
        $this->expectException(ssh\exception\exec\pid\kill::class);
        $client->kill_process(42, 1, 0, 9, 0);
    }
    public function testSftpOpenFailuresAreReportedBeforeTheMissingHelper()
    {
        $client = $this->connected();
        list($warnings) = $this->failsWith(ssh\exception\sftp\remote::class, function () use ($client) { $client->sftp_get('/no/such/remote/file', '/tmp/flat-ssh-local'); });
        $this->assertNotEmpty($warnings);
    }
    public function testSftpLocalOpenFailureIsReported()
    {
        $client = $this->connected();
        list($warnings) = $this->failsWith(ssh\exception\sftp\local::class, function () use ($client) { $client->sftp_get('/etc/hostname', '/no/such/local/dir/file'); });
        $this->assertNotEmpty($warnings);
    }
    /** Makes a remote file of $size bytes ('x' repeated) and returns its path; the server's /tmp is cleaned with the container. */
    private function remoteFile($size)
    {
        $path = '/tmp/flat-ssh-'.bin2hex(random_bytes(4));
        $this->connected()->exec("head -c $size /dev/zero | tr '\\0' x > $path; echo");
        return $path;
    }
    public function testSftpDownloadsARealFileAndReportsProgress()
    {
        $client = $this->connected(); $remote = $this->remoteFile(5000); $local = tempnam(sys_get_temp_dir(), 'flat-sftp-'); $calls = 0;
        try {
            $this->assertNull($client->sftp_get($remote, $local, function () use (&$calls) { $calls++; }));
            $this->assertSame(str_repeat('x', 5000), file_get_contents($local));
            $this->assertGreaterThanOrEqual(3, $calls);
        } finally { unlink($local); }
    }
    public function sftpBodies()
    {
        return [['0', '/tmp/flat-ssh-zero', "printf 0"], ['', '/tmp/flat-ssh-empty', ": "], ['x data', '/tmp/flat-ssh-data', "printf 'x data'"]];
    }
    /** @dataProvider sftpBodies */
    public function testShortAndEmptyFilesAreDownloadedWhole($body, $remote, $command)
    {
        $client = $this->connected(); $local = tempnam(sys_get_temp_dir(), 'flat-sftp-'); $seen = 0;
        $client->exec("$command > $remote; echo");
        try {
            $client->sftp_get($remote, $local, function () use (&$seen) { $seen++; });
            $this->assertSame($body, file_get_contents($local));
            $this->assertGreaterThanOrEqual(1, $seen);
        } finally { unlink($local); }
    }
    public function testAFileThatReportsSizeZeroIsStillDownloaded()
    {
        // /proc files have size 0 and content
        $client = $this->connected(); $local = tempnam(sys_get_temp_dir(), 'flat-sftp-');
        try {
            $client->sftp_get('/proc/version', $local);
            $this->assertStringContainsString('Linux', file_get_contents($local));
        } finally { unlink($local); }
    }
    public function testALargeFileIsDownloadedInManyReads()
    {
        $client = $this->connected(); $remote = $this->remoteFile(2048 * 101); $local = tempnam(sys_get_temp_dir(), 'flat-sftp-'); $calls = 0;
        try {
            $client->sftp_get($remote, $local, function () use (&$calls) { $calls++; });
            $this->assertSame(str_repeat('x', 2048 * 101), file_get_contents($local));
            $this->assertGreaterThanOrEqual(101, $calls);
        } finally { unlink($local); }
    }
    public function testCallbackFailureStillClosesTheLocalAndRemoteStreams()
    {
        $client = $this->connected(); $remote = $this->remoteFile(100); $local = tempnam(sys_get_temp_dir(), 'flat-sftp-');
        $client->sftp_get($remote, $local); // opens the SFTP subsystem, which stays with the client
        $before = count(get_resources('stream'));
        try {
            $client->sftp_get($remote, $local, function () { throw new \RuntimeException('callback failure'); });
            $this->fail('Expected callback failure');
        } catch (\RuntimeException $error) { $this->assertSame('callback failure', $error->getMessage()); }
        finally { unlink($local); }
        $this->assertSame($before, count(get_resources('stream')));
    }
}
