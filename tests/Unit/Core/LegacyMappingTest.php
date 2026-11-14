<?php
namespace flat\test\Unit\Core;
use PHPUnit\Framework\TestCase;
class LegacyDataFixture extends \flat\core\data { public $name; }
class LegacyListFixture extends \flat\core\util\map\from_list { public $name; public $zero; }
class LegacyObjectFixture extends \flat\core\util\map\from_object { public $name; }
class PlainDataControllerFixture extends \flat\core\controller\data { public $name; }
class DynamicCoreDataFixture extends \flat\core\data implements \flat\core\data\create_properties {}
class LegacyMappingTest extends TestCase
{
    public function testDeclaredCoreDataFieldsAreMapped()
    {
        $this->assertSame('mapped',(new LegacyDataFixture(['name'=>'mapped']))->name);
    }
    public function testCoreDataMarkerOptsIntoCreatingProperties()
    {
        $mapped = new DynamicCoreDataFixture(['created'=>'value']);
        $this->assertSame(['created'=>'value'],get_object_vars($mapped));
    }
    public function testCoreDataDoesNotCreateUndeclaredPropertiesByDefault()
    {
        $this->expectException(\flat\core\mappable\exception\cannot_set_property::class);
        new LegacyDataFixture(['unknown'=>'value']);
    }
    public function testListKeysAndIndexZeroDefaultFollowCurrentMappingRules()
    {
        $mapped = new LegacyListFixture(['name'=>'value',0=>'zero'],'default');
        $this->assertSame('value',$mapped->name);
        $this->assertSame('default',$mapped->zero);
    }
    public function testNonObjectInputHasNothingToMap()
    {
        $this->assertSame([],get_object_vars(new \flat\core\util\map\from_object(42)));
    }
    public function testObjectFieldsMapOntoDeclaredProperties()
    {
        $this->assertSame('value',(new LegacyObjectFixture((object)['name'=>'value']))->name);
        $this->assertSame('array value',(new LegacyObjectFixture(['name'=>'array value']))->name);
        $this->expectException(\flat\core\mappable\exception\cannot_set_property::class);
        new LegacyObjectFixture((object)['undeclared'=>'value']);
    }
    public function testControllerExportsAreLimitedToFlatDataObjects()
    {
        $controller = new PlainDataControllerFixture(['name'=>'value']);
        $this->assertNull($controller->get_as_assoc());
        $this->assertNull($controller->get_as_stdClass());
    }
}
