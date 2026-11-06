<?php
namespace flat\test\Unit\Db\Pdo;
use PHPUnit\Framework\TestCase;
use flat\db\driver\pdo;
class DriverFixture extends pdo {}
class SuppliedDriver extends DriverFixture implements pdo\connection_params
{
    public function get_pdo_dsn() { return 'sqlite::memory:'; }
    public function get_pdo_username() { return null; }
    public function get_pdo_password() { return null; }
    public function get_pdo_options() { return []; }
}
class ConnectionTest extends TestCase
{
    private $saved;
    protected function setUp(): void
    {
        $p = new \ReflectionProperty(pdo::class, '_pdo'); $p->setAccessible(true);
        $this->saved = $p->getValue(); $p->setValue(null, []);
    }
    protected function tearDown(): void
    {
        $p = new \ReflectionProperty(pdo::class, '_pdo'); $p->setAccessible(true); $p->setValue(null, $this->saved);
    }
    public function testEquivalentParametersReuseTheConnectionAndForceNewIsIsolated()
    {
        $first = new DriverFixture(['dsn'=>' sqlite::memory: ','username'=>' user ','password'=>' secret ','options'=>[]]);
        $second = new DriverFixture(['dsn'=>'sqlite::memory:','username'=>'user','password'=>'secret','options'=>[]]);
        $this->assertSame($first->get_pdo(), $second->get_pdo());
        $this->assertSame(\PDO::ERRMODE_EXCEPTION, $first->get_pdo()->getAttribute(\PDO::ATTR_ERRMODE));
        $first->get_pdo()->exec('CREATE TABLE cached (value TEXT)');
        $this->assertSame('cached', $second->get_pdo()->query("SELECT name FROM sqlite_master WHERE name='cached'")->fetchColumn());
        $fresh = $first->get_pdo(true);
        $this->assertNotSame($fresh, $first->get_pdo());
        $this->assertFalse($fresh->query("SELECT name FROM sqlite_master WHERE name='cached'")->fetchColumn());
        $forced = new DriverFixture(['dsn'=>'sqlite::memory:','force_new'=>true]);
        $this->assertInstanceOf(pdo\pdo::class, $forced->get_pdo());
        $this->assertNotSame($forced->get_pdo(), $forced->get_pdo(true));
        $this->assertNull($first->command()); // The declared command hook is currently a stub.
    }
    public function testInterfaceSuppliesConnectionParameters()
    {
        $driver = new SuppliedDriver;
        $this->assertInstanceOf(pdo\pdo::class, $driver->get_pdo());
        $this->assertSame('42', $driver->get_pdo()->query('SELECT 42')->fetchColumn());
    }
    public function testInvalidDriverDsnIsWrappedAsConnectError()
    {
        $this->expectException(pdo\exception\connect_error::class);
        $this->expectExceptionMessage('connection error');
        new DriverFixture(['dsn'=>'flat_missing_driver:']);
    }
    public function testMissingConnectionParametersAreReported()
    {
        $driver = (new \ReflectionClass(DriverFixture::class))->newInstanceWithoutConstructor();
        $this->expectException(pdo\exception\missing_connection_params::class);
        $driver->get_pdo();
    }
    public function testLoadStatementMapsRuleArrays()
    {
        $driver = new DriverFixture(['dsn'=>'sqlite::memory:']);
        $driver->get_pdo()->exec('CREATE TABLE items (id INTEGER, name TEXT)');
        $statement = $driver->load_statement(['criteria'=>['id'=>1],'criteria_hash'=>['id'=>'i.id'],'table_name'=>'items','table_alias'=>'i','column_hash'=>['i'=>['id','name']]]);
        $this->assertInstanceOf(pdo\statement::class, $statement);
        $this->assertSame(0, $statement->count());
    }
    public function testIncompleteMultipleArgumentRulesAreRejected()
    {
        $driver = new DriverFixture(['dsn'=>'sqlite::memory:']);
        $this->expectException(pdo\exception\bad_rule::class);
        $driver->load_statement(['criteria'=>['id'=>1]],['criteria_hash'=>['id'=>'i.id']]);
    }
}
