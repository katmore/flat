<?php
namespace flat\test\Unit\Core\Util;
use flat\core\util\utf8;
use PHPUnit\Framework\TestCase;
class Utf8Test extends TestCase
{
    public function testLatinOneAndAlreadyValidUtf8()
    {
        $this->assertSame("caf\xc3\xa9", utf8::fix("caf\xe9"));
        $this->assertSame("caf\xc3\xa9", utf8::fix("caf\xc3\xa9"));
        $object = new utf8("caf\xe9");
        $this->assertSame("caf\xc3\xa9", $object->get_value());
        $this->assertSame($object->get_value(), (string) $object);
    }
    public function testEmptyValueAndArrayResultAreNotExposedAsString()
    {
        $this->assertSame('', (new utf8(''))->get_value());
        $this->assertSame('', (string) new utf8(['ascii']));
    }
}
