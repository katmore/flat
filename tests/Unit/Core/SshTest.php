<?php
namespace flat\test\Unit\Core;
use PHPUnit\Framework\TestCase;
use flat\core\ssh;

class DeferredSsh extends ssh { public function __construct() {} }
class LegacyOutputSsh extends ssh { protected function exec_output($command) { return '0'; } }

/**
 * flat\core\ssh without a server: the configuration it checks, the arguments it validates, its legacy hooks. Its behavior with a server
 * (logins, commands, processes, files) is in SshServerTest.
 */
class SshTest extends TestCase
{
    use \flat\test\Support\PhpSubprocess;
    private function config($changes = []) { return array_replace(['host'=>'local-host','port'=>22,'username'=>'local-user','password'=>'local-password'],$changes); }
    private function client($changes = [], $class = ssh::class) { return new $class($this->config($changes)); }

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
        $this->expectException(ssh\exception\auth\not_ready::class); (new DeferredSsh)->get_auth();
    }
    /** @dataProvider connectedOperations */
    public function testOperationsRequireAConnection($method,$args)
    {
        $client = $this->client(); $this->assertFalse($client->is_connected());
        $this->expectException(ssh\exception\connection\missing::class); $client->$method(...$args);
    }
    public function connectedOperations() { return [['exec',['command']],['sftp_get',['/remote','/local']],['exec_backround',['command','log']],['disconnect',[]],['kill_process',[42]],['checkpid',[42]]]; }
    public function testProcessChecksKeepTheLegacyOutputHook()
    {
        // exec_output() answers '0' (the exit status of kill -0): the process runs
        $this->assertTrue($this->client([],LegacyOutputSsh::class)->checkpid(42));
    }
    public function testInvalidPidUsesTheLegacyCompatibleParameterException()
    {
        $this->expectException(ssh\exception\input_error\bad_param::class); $this->client()->checkpid(0);
    }
    public function testTheParameterExceptionAutoloadsInAFreshRuntime()
    {
        list($status,$out,$err) = $this->subprocess('$e=new \\flat\\core\\ssh\\exception\\input_error\\bad_param("pid","invalid"); echo $e->getMessage();');
        $this->assertSame(0,$status); $this->assertSame('',$err); $this->assertSame('input error: bad pid: invalid',$out);
    }
}
