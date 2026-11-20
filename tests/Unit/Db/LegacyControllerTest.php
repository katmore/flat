<?php
namespace flat\test\Unit\Db;
use PHPUnit\Framework\TestCase;
class LegacyPlainController extends \flat\core\controller\db {}
class LegacyDriverController extends \flat\core\controller\db implements \flat\db\driver
{
    public $received;
    public function connect(array $param=null) { $this->received=$param; }
    public function command(array $param=null) {}
}
class LegacyHookReader extends \flat\core\controller\db implements \flat\db\op\read
{
    public function read(array $param=null) { return 'interface read'; }
    public function op_read() { return 'hook read'; }
}
class LegacyInterfaceReader extends \flat\core\controller\db implements \flat\db\op\read
{
    public function read(array $param=null) { return $param; }
}
class LegacyControllerTest extends TestCase
{
    public function testDriverConstructionConnectsWithItsSuppliedParameters()
    {
        $driver=new LegacyDriverController(['local'=>'configuration']);
        $this->assertSame(['local'=>'configuration'],$driver->received);
        $this->assertInstanceOf(LegacyPlainController::class,new LegacyPlainController);
    }
    public function testStaticReadUsesTheCalledClass()
    {
        $warnings=[]; set_error_handler(function ($level,$message) use (&$warnings) { $warnings[]=$message; return true; });
        try {
            $this->assertNull(LegacyPlainController::load_read(['key'=>'local']));
            $this->assertSame('hook read',LegacyHookReader::load_read(['key'=>'local']));
            $this->assertSame(['key'=>'local'],LegacyInterfaceReader::load_read(['key'=>'local','ignored'=>true]));
            $this->assertSame([],LegacyInterfaceReader::load_read());
        } finally { restore_error_handler(); }
        $this->assertSame([],$warnings);
    }
}
