<?php
namespace flat\test\Unit\Db;
use PHPUnit\Framework\TestCase;
use flat\db\driver\rabbitmq;
use PhpAmqpLib\Message\AMQPMessage;
class RabbitFixture extends rabbitmq
{
    public static function loadChannel($params = null) { return parent::_load_channel($params); }
    public static function loadConnection($params = null) { return parent::_load_conn($params); }
}
class RabbitProvider extends RabbitFixture implements rabbitmq\connection_params
{
    public $provided;
    public function get_rabbitmq_connection_params() { return $this->provided; }
}
class RabbitMqTest extends TestCase
{
    private $driver;
    private $params;
    private $queue;
    private $saved = [];
    protected function setUp(): void
    {
        foreach (['_conn', '_chan'] as $name) {
            $property = new \ReflectionProperty(rabbitmq::class, $name); $property->setAccessible(true);
            $this->saved[$name] = $property->getValue(); $property->setValue(null, []);
        }
        $this->params = ['host'=>getenv('FLAT_TEST_RABBITMQ_HOST') ?: 'rabbitmq',
            'user'=>'flatunit','password'=>'flatunit-local-tests','connection_timeout'=>10,'read_write_timeout'=>10];
        $this->queue = 'flat_unit_'.bin2hex(random_bytes(6));
        // Access the pre-connect API without triggering the final auto-connect constructor.
        $this->driver = (new \ReflectionClass(RabbitFixture::class))->newInstanceWithoutConstructor();
    }
    protected function tearDown(): void
    {
        $channels = new \ReflectionProperty(rabbitmq::class, '_chan'); $channels->setAccessible(true);
        foreach ($channels->getValue() as $channel) {
            if ($channel->is_open()) { $channel->close(); }
        }
        $connections = new \ReflectionProperty(rabbitmq::class, '_conn'); $connections->setAccessible(true);
        foreach ($connections->getValue() as $connection) {
            if ($connection->isConnected()) {
                $cleanup = $connection->channel(); $cleanup->queue_delete($this->queue); $cleanup->close();
                $connection->close();
            }
        }
        foreach ($this->saved as $name=>$value) {
            $property = new \ReflectionProperty(rabbitmq::class, $name); $property->setAccessible(true); $property->setValue(null, $value);
        }
    }
    /**
     * @group services
     * @group rabbitmq
     */
    public function testInitialConnectionAndChannelAreReturnedAndReused()
    {
        $this->driver = new RabbitFixture($this->params);
        $connection = $this->driver->get_connection();
        $this->assertInstanceOf(\PhpAmqpLib\Connection\AMQPConnection::class, $connection);
        $this->assertSame($connection, $this->driver->get_connection());
        $channel = $this->driver->get_channel();
        $this->assertSame($channel, $this->driver->get_channel());
        $this->assertTrue($channel->is_open());
    }
    /**
     * @group services
     * @group rabbitmq
     */
    public function testChannelCanEstablishItsOwnConnection()
    {
        $channel = RabbitFixture::loadChannel($this->params);
        $this->assertTrue($channel->is_open());
        $this->assertSame($channel, RabbitFixture::loadChannel($this->params));
    }
    /**
     * @group services
     * @group rabbitmq
     */
    public function testQueueDeclarationAndPublishUseDocumentedParameters()
    {
        $this->driver->connect($this->params);
        $this->assertSame('hello', $this->driver->publish(['queue'=>$this->queue,'msg'=>'hello']));
        $message = $this->driver->get_channel()->basic_get($this->queue, true);
        $this->assertSame('hello', $message->body);
        $this->driver->get_channel()->queue_delete($this->queue);
        $this->assertSame('world', $this->driver->publish(['queue'=>['queue'=>$this->queue,'auto_delete'=>false], 'msg'=>'world',
            'exchange'=>'','mandatory'=>true,'immediate'=>false]));
        $this->assertSame('world', $this->driver->get_channel()->basic_get($this->queue, true)->body);
    }
    /**
     * @group services
     * @group rabbitmq
     */
    public function testExplicitRoutingMessageAndCommandPaths()
    {
        $this->driver->connect($this->params);
        $this->driver->get_channel()->queue_declare($this->queue, false, false, false, true);
        $this->assertSame('direct', $this->driver->publish($this->queue, 'direct'));
        $this->assertSame('direct', $this->driver->get_channel()->basic_get($this->queue, true)->body);
        $this->assertSame('object', $this->driver->command(['publish'=>['routing_key'=>$this->queue,
            'AMPQMessage'=>new AMQPMessage('object')]]));
        $this->assertSame('object', $this->driver->get_channel()->basic_get($this->queue, true)->body);
        $this->assertNull($this->driver->command([]));
    }
    /**
     * @group services
     * @group rabbitmq
     */
    public function testProviderOverridesDefaultsAndTrimsStringParameters()
    {
        $provider = (new \ReflectionClass(RabbitProvider::class))->newInstanceWithoutConstructor(); $provider->provided = $this->params;
        $provider->provided['host'] = ' '.$this->params['host'].' ';
        $this->assertTrue($provider->connect()->isConnected());
    }
    /** @dataProvider missingConnections */
    public function testMissingConnectionParametersAreRejected($provider)
    {
        $this->expectException(rabbitmq\exception\missing_connection_params::class);
        if ($provider) { $driver = (new \ReflectionClass(RabbitProvider::class))->newInstanceWithoutConstructor(); $driver->provided = 'invalid'; $driver->connect(); }
        else { $this->driver->get_connection(); }
    }
    public function missingConnections() { return [[false],[true]]; }
    public function testMissingHostIsRejectedBeforeNetworkConnection()
    {
        $this->expectException(rabbitmq\exception\missing_connection_params::class);
        RabbitFixture::loadConnection();
    }
    public function testEmptyMessageIsRejected()
    {
        $this->expectException(rabbitmq\exception\bad_param::class);
        $this->driver->publish([]);
    }
    public function testConnectionCacheKeyDependsOnTheConnectionParameters()
    {
        $hash = new \ReflectionMethod(\flat\db\driver\rabbitmq::class,'_param_to_hash'); $hash->setAccessible(true);
        $this->assertSame(md5(''),$hash->invoke(null,null));
        $this->assertSame(md5(''),$hash->invoke(null,[]));
        $first = $hash->invoke(null,['host'=>'first.example']);
        $this->assertNotSame(md5(''),$first);
        $this->assertNotSame($first,$hash->invoke(null,['host'=>'second.example']));
        $this->assertSame($first,$hash->invoke(null,['host'=>'first.example']));
    }
    /**
     * @group services
     * @group rabbitmq
     */
    public function testMissingQueueIsRejected()
    {
        $this->driver->connect($this->params);
        $this->expectException(rabbitmq\exception\bad_param::class);
        $this->driver->publish(['msg'=>'local']);
    }
    public function testConfigNamespaceSuppliesPresentKeysAndKeepsDefaults()
    {
        $saved = []; $class = new \ReflectionClass(\flat\core\config::class);
        foreach ($class->getProperties(\ReflectionProperty::IS_STATIC) as $property) {
            $property->setAccessible(true); $saved[$property->getName()] = $property->getValue();
            $property->setValue(null, $property->getName() === 'base_dir' ? null : []);
        }
        $directory = sys_get_temp_dir().'/flat-rabbit-config-'.bin2hex(random_bytes(6)); mkdir($directory);
        try {
            file_put_contents($directory.'/rabbit.php', '<?php return ["host"=>"configured-host","heartbeat"=>17];');
            \flat\core\config::set_base_dir($directory);
            $params = RabbitFixture::params_from_config_ns('rabbit');
            $this->assertSame('configured-host', $params['host']);
            $this->assertSame(17, $params['heartbeat']);
            $this->assertSame(5672, $params['port']);
        } finally {
            unlink($directory.'/rabbit.php'); rmdir($directory);
            foreach ($saved as $name=>$value) { $property = $class->getProperty($name); $property->setAccessible(true); $property->setValue(null, $value); }
        }
    }
}
