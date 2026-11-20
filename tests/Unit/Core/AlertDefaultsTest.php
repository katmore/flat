<?php
namespace FlatUnit\Alert {
    class DefaultProbe extends \flat\event\alert
    {
        public static function database() { return parent::_get_crud(); }
        public static function broker() { return parent::_get_driver(); }
    }
}
namespace flat\test\Unit\Core {
    require_once __DIR__.'/../../Support/ConfigSandbox.php';
    use PHPUnit\Framework\TestCase;
    use flat\db\driver\rabbitmq;
    class AlertDefaultsTest extends TestCase
    {
        use \flat\test\Support\ConfigSandbox;
        private $saved=[];
        protected function setUp(): void
        {
            $this->openConfigSandbox([]); $root=$this->configSandboxDirectory();
            mkdir($root.'/FlatUnit/Alert/DefaultProbe',0700,true);
            $params=['host'=>getenv('FLAT_TEST_RABBITMQ_HOST') ?: 'rabbitmq','user'=>'flatunit','password'=>'flatunit-local-tests','connection_timeout'=>10,'read_write_timeout'=>10];
            file_put_contents($root.'/FlatUnit/Alert/DefaultProbe/alert.php','<?php return '.var_export($params,true).';');
            foreach (['_conn','_chan'] as $name) {
                $p=new \ReflectionProperty(rabbitmq::class,$name); $p->setAccessible(true); $this->saved[$name]=$p->getValue(); $p->setValue(null,[]);
            }
        }
        protected function tearDown(): void
        {
            foreach (['_chan','_conn'] as $name) {
                $p=new \ReflectionProperty(rabbitmq::class,$name); $p->setAccessible(true);
                foreach ($p->getValue() as $connection) $connection->close();
                $p->setValue(null,$this->saved[$name]);
            }
            $root=$this->configSandboxDirectory(); unlink($root.'/FlatUnit/Alert/DefaultProbe/alert.php');
            foreach (['FlatUnit/Alert/DefaultProbe','FlatUnit/Alert','FlatUnit'] as $path) rmdir($root.'/'.$path);
            $this->closeConfigSandbox();
        }
        /**
     * @group services
     * @group rabbitmq
     */
    public function testDefaultBrokerLoadsItsConfigurationAndUsesTheRealLocalRabbitService()
        {
            $driver=\FlatUnit\Alert\DefaultProbe::broker();
            $this->assertInstanceOf(rabbitmq::class,$driver); $this->assertTrue($driver->get_channel()->is_open());
        }
        public function testSubscriptionStorageRequiresAnExplicitProvider()
        {
            $this->expectException(\LogicException::class); $this->expectExceptionMessage('mongo_crud_provider');
            \FlatUnit\Alert\DefaultProbe::database();
        }
    }
}
