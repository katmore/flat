<?php
namespace flat\test\Unit\Tmpl;
use PHPUnit\Framework\TestCase;
use flat\tmpl\data;
class DeclaredData extends data { public $title = 'default'; protected $value = 'initial'; }
class DataTest extends TestCase
{
    public function testArrayAndObjectMappingIncludeDeclaredAndDynamicValues()
    {
        foreach ([['title'=>'mapped','value'=>0,'extra'=>false], (object) ['title'=>'mapped','value'=>0,'extra'=>false]] as $input) {
            $data = new DeclaredData($input);
            $this->assertSame(['extra'=>false,'title'=>'mapped','value'=>0], $data->getAsArray());
            $this->assertEquals((object) $data->getAsArray(), $data->getAsObject());
            $this->assertSame(0, $data['value']);
            $this->assertSame(false, $data->extra);
        }
    }
    public function testArrayAndMagicPropertyAccessShareTheSameValues()
    {
        $data = new DeclaredData;
        $data['value'] = 'changed'; $data->extra = 'dynamic'; $data[] = 'appended';
        $this->assertSame('changed', $data->value);
        $this->assertSame('dynamic', $data['extra']);
        $this->assertSame('appended', $data[0]);
        $this->assertTrue(isset($data['value'])); $this->assertTrue(isset($data->extra));
        unset($data->value); unset($data['extra']);
        // Declared fields remain present after unset, whereas dynamic fields disappear.
        $this->assertTrue(isset($data->value)); $this->assertNull($data['value']);
        $this->assertFalse(isset($data->extra)); $this->assertNull($data->extra);
        $data->nullable = null;
        $this->assertFalse(isset($data->nullable));
        $this->assertNull($data['missing']);
    }
    /** @dataProvider scalarInputs */
    public function testScalarConstructorPreservesValues($value)
    {
        $this->assertSame(['data'=>$value], (new data($value))->getAsArray());
        $declared = new DeclaredData($value);
        $this->assertSame($value, $declared['value']);
        $this->assertSame($value, $declared['data']);
    }
    public function scalarInputs() { return [[0], [false], [''], ['0'], [3.25]]; }
    public function testEmptyConstructorHasNoDynamicValues()
    {
        $this->assertSame([], (new data)->getAsArray());
    }
}
