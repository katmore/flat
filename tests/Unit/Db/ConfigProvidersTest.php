<?php
namespace flat\test\Unit\Db;
require_once __DIR__.'/../../Support/ConfigSandbox.php';
use PHPUnit\Framework\TestCase;
class MongoConfigFixture extends \flat\db\driver\mongo\config
{
    public function connect(array $params = null) {} // Test only configuration getters.
    protected function _get_config_ns() { return 'services/mongo'; }
}
class PdoConfigFixture extends \flat\db\driver\pdo\config
{
    protected function _get_config_ns() { return 'services/pdo'; }
}
class ConfigProvidersTest extends TestCase
{
    use \flat\test\Support\ConfigSandbox;
    protected function setUp(): void
    {
        $this->openConfigSandbox(['mongo'=>['host'=>'mongo.invalid','port'=>27017,'db'=>'local-db'],
            'pdo'=>['dsn'=>'sqlite::memory:','username'=>'local-user','password'=>'local-password','options'=>[\PDO::ATTR_TIMEOUT=>3]]]);
    }
    protected function tearDown(): void { $this->closeConfigSandbox(); }
    public function testMongoConfigurationAndOptionalDefaults()
    {
        $config = new MongoConfigFixture;
        $this->assertSame('mongo.invalid', $config->get_client_host());
        $this->assertSame(27017, $config->get_client_port());
        $this->assertSame('local-db', $config->get_client_db());
        $this->assertSame([], $config->get_client_options());
        $this->assertSame([], $config->get_client_driver_options());
    }
    public function testPdoConfigurationValues()
    {
        $config = new PdoConfigFixture;
        $this->assertSame('sqlite::memory:', $config->get_pdo_dsn());
        $this->assertSame('local-user', $config->get_pdo_username());
        $this->assertSame('local-password', $config->get_pdo_password());
        $this->assertSame([\PDO::ATTR_TIMEOUT=>3], $config->get_pdo_options());
    }
}
