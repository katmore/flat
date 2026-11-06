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
class LegacyControllerTest extends TestCase
{
    public function testDriverConstructionConnectsWithItsSuppliedParameters()
    {
        $driver=new LegacyDriverController(['local'=>'configuration']);
        $this->assertSame(['local'=>'configuration'],$driver->received);
        $this->assertInstanceOf(LegacyPlainController::class,new LegacyPlainController);
    }
    public function testStaticReadCurrentlyWarnsAboutItsLiteralSelfAndReturnsNull()
    {
        $warnings=[]; set_error_handler(function ($level,$message) use (&$warnings) { $warnings[]=$message; return true; });
        try { $result=LegacyPlainController::load_read(['key'=>'local']); } finally { restore_error_handler(); }
        $this->assertNull($result); $this->assertCount(1,$warnings);
        $this->assertStringContainsString('undefined constant self',$warnings[0]);
    }
}
