<?php
namespace flat\test\Unit\Db\Mongo;
use PHPUnit\Framework\TestCase;
use flat\db\driver\mongo;

/**
 * @group services
 * @group mongo
 */
class CachedClientFailureTest extends TestCase
{
    private $saved = [];
    private $directory;
    private $process;
    private $pipes = [];
    protected function setUp(): void
    {
        foreach (['_client','_client_ping'] as $name) {
            $p = new \ReflectionProperty(mongo::class,$name); $p->setAccessible(true);
            $this->saved[$name] = $p->getValue(); $p->setValue(null,[]);
        }
        $this->directory = sys_get_temp_dir().'/flat-mongo-proxy-'.bin2hex(random_bytes(6)); mkdir($this->directory,0700);
        file_put_contents($this->directory.'/control','up');
    }
    protected function tearDown(): void
    {
        file_put_contents($this->directory.'/control','stop');
        if (is_resource($this->process)) { proc_terminate($this->process); proc_close($this->process); }
        foreach ($this->pipes as $pipe) if (is_resource($pipe)) fclose($pipe);
        unlink($this->directory.'/control'); rmdir($this->directory);
        foreach ($this->saved as $name => $value) {
            $p = new \ReflectionProperty(mongo::class,$name); $p->setAccessible(true); $p->setValue(null,$value);
        }
    }
    public function testFailedCachedPingEvictsTheActualClientAndRetriesAfterATransportOutage()
    {
        $backend = parse_url(getenv('FLAT_TEST_MONGODB_URI') ?: 'mongodb://mongo:27017');
        $target = $backend['host'].':'.($backend['port'] ?? 27017);
        $script = __DIR__.'/../../../Support/mongo-fault-proxy.php';
        $command = escapeshellarg(PHP_BINARY).' '.escapeshellarg($script).' '.escapeshellarg($target).' '.escapeshellarg($this->directory.'/control');
        $this->process = proc_open($command,[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$this->pipes);
        $this->assertIsResource($this->process); fclose($this->pipes[0]);
        stream_set_timeout($this->pipes[1],2);
        $address = trim(fgets($this->pipes[1])); $this->assertNotSame('',$address);
        $options = ['connect_attempts'=>2,'client_options'=>['connect'=>true,'serverSelectionTimeoutMS'=>100,'connectTimeoutMS'=>100,'socketTimeoutMS'=>100]];
        $uri = 'mongodb://'.$address;
        $client = mongo::load_client($uri,$options);
        $this->assertSame(1.0,(float)$client->selectDB('test')->command(['ping'=>1])['ok']);
        $this->assertSame($client,mongo::load_client($uri,$options));
        // Expire only the framework's TTL; the cached client/socket are real.
        $p = new \ReflectionProperty(mongo::class,'_client_ping'); $p->setAccessible(true);
        $timestamps = $p->getValue(); foreach ($timestamps as &$timestamp) $timestamp = time()-mongo::client_ping_expire-1; unset($timestamp);
        $p->setValue(null,$timestamps);
        file_put_contents($this->directory.'/control','down');
        try { mongo::load_client($uri,$options); $this->fail('Expected cached verification and reconnection to fail'); }
        catch (mongo\db\exception\connect_failure $error) { $this->assertStringContainsString('failed after 2 attempts',$error->getMessage()); }
        foreach (['_client','_client_ping'] as $name) {
            $p = new \ReflectionProperty(mongo::class,$name); $p->setAccessible(true); $this->assertSame([],$p->getValue());
        }
    }
}
