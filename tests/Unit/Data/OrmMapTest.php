<?php
namespace flat\test\Unit\Data;
use PHPUnit\Framework\TestCase;
use flat\data\orm\map;
class OrmMapRow extends \flat\data { public $name; }
class OrmMapTest extends TestCase
{
    public function testObjectIsPreservedAndTakesPrecedence()
    {
        $object = new \stdClass; $object->name = 'original';
        $map = new map(['object'=>$object,'data'=>['name'=>'ignored'],'type'=>OrmMapRow::class]);
        $this->assertSame($object, $map->get_data());
        $this->assertSame(\stdClass::class, $map->type);
    }
    public function testDataAndTypeConstructAMappedObject()
    {
        $map = new map(['object'=>'invalid','data'=>['name'=>'mapped'],'type'=>OrmMapRow::class]);
        $this->assertInstanceOf(OrmMapRow::class, $map->get_data());
        $this->assertSame('mapped', $map->get_data()->name);
        $this->assertSame(OrmMapRow::class, $map->type);
    }
    /** @dataProvider invalidInputs */
    public function testIncompleteSpecificationsAreRejected($params, $message)
    {
        $this->expectException(\flat\data\orm\exception\bad_param::class);
        $this->expectExceptionMessage($message);
        new map($params);
    }
    public function invalidInputs()
    {
        return [[[], "must have 'object' or 'data' param"], [['object'=>42], "must have 'object' or 'data' param"],
            [['data'=>[]], "missing 'type' param"], [['data'=>[],'type'=>'NoSuchOrmMapRow'], 'does not exist']];
    }
}
