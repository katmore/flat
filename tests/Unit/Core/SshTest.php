<?php
namespace flat\test\Unit\Core;
require_once __DIR__.'/../../Support/SshBoundary.php';
require_once __DIR__.'/../../Support/PhpSubprocess.php';
use PHPUnit\Framework\TestCase;
use flat\core\ssh;
use flat\test\Support\SshBoundary as Boundary;
class DeferredSsh extends ssh { public function __construct() {} }
class LegacyOutputSsh extends ssh { protected function exec_output($command) { return '0'; } }
class SshTest extends TestCase
{
    use \flat\test\Support\PhpSubprocess;
    private static $privateKey;
    private static $encryptedKey;
    private $clients = [];
    public static function setUpBeforeClass(): void
    {
        $key = openssl_pkey_new(['private_key_bits'=>2048,'private_key_type'=>OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($key,self::$privateKey);
        openssl_pkey_export($key,self::$encryptedKey,'input-secret'); openssl_pkey_free($key);
    }
    protected function setUp(): void { Boundary::reset(); }
    protected function tearDown(): void { $this->clients = []; Boundary::close(); }
    private function config($changes = []) { return array_replace(['host'=>'local-host','port'=>22,'username'=>'local-user','password'=>'local-password'],$changes); }
    private function client($changes = [],$class = ssh::class)
    {
        $client = new $class($this->config($changes)); $this->clients[] = $client; return $client;
    }
    private function pubkey($encrypted = false)
    {
        return $this->client(['password'=>null,'pubkey'=>'local public key','privkey'=>$encrypted ? self::$encryptedKey : self::$privateKey,'passphrase'=>$encrypted ? 'input-secret' : null]);
    }
    public function testUnavailableExtensionIsReported()
    {
        Boundary::$available = false; $this->expectException(ssh\exception\syserror::class);
        $this->client();
    }
    /** @dataProvider requiredFields */
    public function testRequiredConfigurationIsChecked($field)
    {
        $config = $this->config(); unset($config[$field]);
        $this->expectException(ssh\exception\input_error\missing_param::class); new ssh($config);
    }
    public function requiredFields() { return [['host'],['port'],['username']]; }
    public function testAuthenticationConfigurationMustBePresent()
    {
        $this->expectException(ssh\exception\input_error::class); $this->client(['password'=>null]);
    }
    public function testPublicKeyConfigurationNeedsPrivateKey()
    {
        $this->expectException(ssh\exception\input_error\missing_param::class); $this->client(['password'=>null,'pubkey'=>'public']);
    }
    public function testDeferredSubclassReportsUninitializedAuthentication()
    {
        $client = new DeferredSsh; $this->clients[] = $client;
        $this->expectException(ssh\exception\auth\not_ready::class); $client->get_auth();
    }
    public function testPasswordConnectionAndExecutionUseTheirMatchingTransport()
    {
        $client = $this->client(['connect'=>true]);
        $this->assertInstanceOf(ssh\auth\password::class,$client->get_auth()); $this->assertTrue($client->is_connected());
        Boundary::$execReplies = ["remote output\n"];
        $this->assertSame("remote output\n",$client->exec('local command'));
        $this->assertNull(call_user_func(Boundary::$callbacks['disconnect'],1,'closed','en'));
    }
    public function testRepeatedConnectIsRejected()
    {
        $client = $this->client(['connect'=>true]); $this->expectException(ssh\exception\syserror::class); $client->connect();
    }
    public function testConnectionFailureIsReported()
    {
        Boundary::$connectOk = false; $client = $this->client();
        $this->expectException(ssh\exception\connection\failure::class); $client->connect();
    }
    public function testPasswordFailureUsesTheExistingFrameworkException()
    {
        Boundary::$passwordOk = false; $client = $this->client();
        $this->expectException(ssh\exception\auth\failure\password::class); $client->connect();
    }
    /** @dataProvider passphrases */
    public function testPublicKeyPathRunsRealOpenSslAndRemovesItsTemporaryFiles($encrypted)
    {
        $client = $this->pubkey($encrypted); $client->connect();
        $this->assertInstanceOf(ssh\auth\pubkey::class,$client->get_auth()); $this->assertTrue($client->is_connected());
        $this->assertTrue(Boundary::$pubkeyCalls[0]['parsed']);
        $this->assertSame(str_repeat('p',128),Boundary::$pubkeyCalls[0]['passphrase']);
        $this->assertSame('local-fingerprint',$client->get_auth()->fingerprint);
        foreach (Boundary::$keyPaths as $path) $this->assertFileNotExists($path);
    }
    public function passphrases() { return [[false],[true]]; }
    public function testProvidedFingerprintIsChecked()
    {
        $client = $this->pubkey(); $client->get_auth()->fingerprint = 'local-fingerprint'; $client->connect();
        $this->assertTrue($client->is_connected());
    }
    public function testFingerprintMismatchIsReported()
    {
        $client = $this->pubkey(); $client->get_auth()->fingerprint = 'different';
        $this->expectException(ssh\exception\syserror::class); $this->expectExceptionMessage('fingerprint mismatch'); $client->connect();
    }
    public function testPublicKeyAuthenticationFailureIsReported()
    {
        Boundary::$pubkeyOk = false; $client = $this->pubkey();
        $this->expectException(ssh\exception\auth\failure\pubkey::class); $client->connect();
    }
    /** @dataProvider cryptoFailures */
    public function testLocalCryptoFailureBoundariesAreReported($kind)
    {
        $client = $this->pubkey();
        if ($kind === 'passphrase') Boundary::$passphrase = 'short';
        elseif ($kind === 'process') Boundary::$processFailure = true;
        elseif ($kind === 'stderr') Boundary::$stderrError = true;
        else Boundary::$emptyKey = true;
        $this->expectException(ssh\exception\syserror::class); $client->connect();
    }
    public function cryptoFailures() { return [['passphrase'],['process'],['stderr'],['empty']]; }
    /** @dataProvider connectedOperations */
    public function testOperationsRequireAConnection($method,$args)
    {
        $client = $this->client(); $this->assertFalse($client->is_connected());
        $this->expectException(ssh\exception\connection\missing::class); $client->$method(...$args);
    }
    public function connectedOperations() { return [['exec',['command']],['sftp_get',['/remote','/local']],['exec_backround',['command','log']],['disconnect',[]],['kill_process',[42]],['checkpid',[42]]]; }
    public function testFailedRemoteExecutionIsReported()
    {
        $client = $this->client(['connect'=>true]); Boundary::$execReplies = [false];
        $this->expectException(ssh\exception\syserror::class); $client->exec('command');
    }
    public function testDisconnectIgnoresMissingConnectionOnlyWhenRequested()
    {
        $client = $this->client(); $this->assertFalse($client->disconnect(true)); $client->connect();
        $this->assertTrue($client->disconnect());
        // The current no-op disconnect callback leaves local state populated.
        $this->assertTrue($client->is_connected());
    }
    public function testProcessChecksPreserveZeroExitCodeSemanticsAndLegacyHooks()
    {
        $client = $this->client(['connect'=>true]); Boundary::$execReplies = ['0','1'];
        $this->assertTrue($client->checkpid('42')); $this->assertFalse($client->checkpid(42,true));
        $this->assertTrue($this->client([],LegacyOutputSsh::class)->checkpid(42));
    }
    public function testMissingRemoteProcessThrowsUnlessIgnored()
    {
        $client = $this->client(['connect'=>true]); Boundary::$execReplies = ['1'];
        $this->expectException(ssh\exception\exec\pid\not_running::class); $client->checkpid(42);
    }
    public function testInvalidPidUsesTheLegacyCompatibleParameterException()
    {
        $this->expectException(ssh\exception\input_error\bad_param::class); $this->client()->checkpid(0);
    }
    /** @dataProvider runtimes */
    public function testBackgroundExecutionReturnsPidAndChecksRequestedRuntime($runtime,$sleep)
    {
        $client = $this->client(['connect'=>true]); Boundary::$execReplies = ['42','0'];
        $this->assertSame('42',$client->exec_backround('command','logfile',$runtime));
        $this->assertSame($sleep === 0 ? [] : [$sleep],Boundary::$sleeps);
    }
    public function runtimes() { return [[0,0],[2,2],['invalid',3]]; }
    public function testBackgroundExecutionNeedsAPid()
    {
        $client = $this->client(['connect'=>true]); Boundary::$execReplies = [''];
        $this->expectException(ssh\exception\exec\pid\missing::class); $client->exec_backround('command','logfile');
    }
    /** @dataProvider invalidKillArguments */
    public function testKillArgumentsAreValidatedBeforeSendingSignals($args)
    {
        $client = $this->client(['connect'=>true]); $this->expectException(ssh\exception\input_error\bad_param::class);
        $client->kill_process(...$args);
    }
    public function invalidKillArguments() { return [[['bad']],[[42,0]],[[42,16]],[[42,1,-1]],[[42,1,61]],[[42,1,0,0]],[[42,1,0,9,-1]],[[42,1,0,9,61]]]; }
    public function testProcessCanExitAfterEitherSignal()
    {
        $client = $this->client(['connect'=>true]); Boundary::$clock = 100; Boundary::$execReplies = ['0','1'];
        $this->assertTrue($client->kill_process(42,1,0,9,0));
        Boundary::$execReplies = ['0','0','0','1'];
        $this->assertTrue($client->kill_process(42,1,0,9,0));
    }
    /** @dataProvider failedKillReplies */
    public function testSignalAndStillRunningFailuresAreReported($replies,$exception)
    {
        $client = $this->client(['connect'=>true]); Boundary::$clock = 100; Boundary::$execReplies = $replies;
        $this->expectException($exception); $client->kill_process(42,1,0,9,0);
    }
    public function failedKillReplies() { return [[['1'],ssh\exception\exec\pid\kill::class],[['0','0','1'],ssh\exception\exec\pid\kill::class],[['0','0','0','0'],ssh\exception\exec\pid\cannot_kill::class]]; }
    public function testSftpOpenFailuresAreReportedBeforeTheMissingHelper()
    {
        $client = $this->client(['connect'=>true]); Boundary::$remoteOpenFailure = true;
        $this->expectException(ssh\exception\sftp\remote::class); $client->sftp_get('/remote','/local');
    }
    public function testSftpLocalOpenFailureIsReported()
    {
        $client = $this->client(['connect'=>true]); Boundary::$localTarget = '/local'; Boundary::$localOpenFailure = true;
        $this->expectException(ssh\exception\sftp\local::class); $client->sftp_get('/remote','/local');
    }
    public function testLegacyParameterAliasAutoloadsInAFreshRuntime()
    {
        list($status,$out,$err) = $this->subprocess('$e=new \\flat\\core\\ssh\\exception\\input_error\\bad_param("pid","invalid"); echo $e->getMessage();');
        $this->assertSame(0,$status); $this->assertSame('',$err); $this->assertSame('input error: bad pid: invalid',$out);
    }
    public function testSuccessfulReadsRestartTheIdleTimeoutAcrossALongerTransfer()
    {
        $client=$this->client(['connect'=>true]); $local=tempnam(sys_get_temp_dir(),'flat-sftp-');
        $timeout=ini_get('default_socket_timeout'); ini_set('default_socket_timeout','5');
        Boundary::$remoteBody=str_repeat('x',5000); Boundary::$microtimes=[0,4,4,8,8,12,12]; $calls=0;
        try {
            $this->assertNull($client->sftp_get('/remote',$local,function () use (&$calls) { $calls++; }));
            $this->assertSame(Boundary::$remoteBody,file_get_contents($local)); $this->assertSame(4,$calls);
            $this->assertSame([],Boundary::$microtimes);
        } finally { ini_set('default_socket_timeout',$timeout); unlink($local); }
    }
    public function testAnIdleTimeoutStopsTheNextReadAndClosesBothStreams()
    {
        $client=$this->client(['connect'=>true]); $local=tempnam(sys_get_temp_dir(),'flat-sftp-');
        $timeout=ini_get('default_socket_timeout'); ini_set('default_socket_timeout','5');
        Boundary::$remoteBody=str_repeat('x',4096); Boundary::$microtimes=[0,1,1,7];
        try {
            $client->sftp_get('/remote',$local);
            $this->assertSame(str_repeat('x',2048),file_get_contents($local));
            $this->assertFalse(is_resource(Boundary::$streams[count(Boundary::$streams)-1]));
            $this->assertFalse(is_resource(Boundary::$streams[count(Boundary::$streams)-2]));
        } finally { ini_set('default_socket_timeout',$timeout); unlink($local); }
    }
    /** @dataProvider sftpBodies */
    public function testNativeReadsHandleShortEmptyAndZeroBodies($body,$failure,$size,$expected,$calls)
    {
        $client=$this->client(['connect'=>true]); $local=tempnam(sys_get_temp_dir(),'flat-sftp-');
        Boundary::$remoteBody=$body; Boundary::$readFailure=$failure; Boundary::$remoteSize=$size; $seen=0;
        try {
            $client->sftp_get('/remote',$local,function () use (&$seen) { $seen++; });
            $this->assertSame($expected,file_get_contents($local)); $this->assertSame($calls,$seen);
        } finally { unlink($local); }
    }
    public function sftpBodies()
    {
        return [['0',false,null,'0',2],['',false,null,'',1],['data',true,null,'',1],['data',false,0,'data',2],
            [str_repeat('x',2048*101),false,null,str_repeat('x',2048*101),104]];
    }
    public function testCallbackFailureStillClosesTheLocalAndRemoteStreams()
    {
        $client=$this->client(['connect'=>true]); $local=tempnam(sys_get_temp_dir(),'flat-sftp-');
        try {
            $client->sftp_get('/remote',$local,function () { throw new \RuntimeException('callback failure'); });
            $this->fail('Expected callback failure');
        } catch (\RuntimeException $error) { $this->assertSame('callback failure',$error->getMessage()); }
        finally {
            unlink($local);
            $this->assertFalse(is_resource(Boundary::$streams[count(Boundary::$streams)-1]));
            $this->assertFalse(is_resource(Boundary::$streams[count(Boundary::$streams)-2]));
        }
    }
}
