<?php
namespace flat\test\Unit\Core;
use PHPUnit\Framework\TestCase;
class BadInterfaceTest extends TestCase
{
    public function testBothInterfaceNamesAutoloadAndRemainCompatible()
    {
        $this->assertTrue(interface_exists(\flat\core\bad_interface::class));
        $this->assertTrue(interface_exists(\flat\core\exception\bad_interface::class));
        $exception = new \flat\db\driver\mongo\db\exception\bad_interface('example','class','method','reason');
        $this->assertInstanceOf(\flat\core\bad_interface::class,$exception);
        $this->assertInstanceOf(\flat\core\exception\bad_interface::class,$exception);
        $this->assertStringContainsString('interface example is bad in class',$exception->getMessage());
    }
    /** @group known-defect */
    public function testMessageFormattingRetainsCurrentMethodAndReasonBehavior()
    {
        $plain = new \flat\core\exception\bad_interface\message('interface','class');
        $this->assertSame('interface interface is bad in class',$plain->get_string());
        $this->assertSame($plain->get_string(),(string)$plain);
        $method = new \flat\core\exception\bad_interface\message('interface','class','method','reason');
        $this->assertSame('interface interface is bad in ::method()',$method->get_string());
        $other = new \flat\core\exception\bad_interface\message('interface','class',42,42);
        $this->assertSame('interface interface is bad in class',(string)$other);
    }
}
