<?php
namespace flat\test\Unit\Input;

use flat\input\map;
use PHPUnit\Framework\TestCase;

class MapTest extends TestCase
{
    public function testArrayBecomesProperties()
    {
        $map = new map(['a' => 1, 'b' => 'x']);
        $this->assertSame(1, $map->a);
        $this->assertSame('x', $map->b);
    }

    public function testObjectBecomesProperties()
    {
        $input = new \stdClass();
        $input->x = 9;
        $input->y = 'z';
        $map = new map($input);
        $this->assertSame(9, $map->x);
        $this->assertSame('z', $map->y);
    }

    public function testScalarGoesToData()
    {
        $map = new map('hello');
        $this->assertSame('hello', $map->data);
    }
    public function testEmptyInputHasNoProperties()
    {
        $this->assertSame([], get_object_vars(new map()));
        $this->assertSame([], get_object_vars(new map(null)));
        // Nested arrays are kept as arrays, not converted to maps.
        $map = new map(['nested' => ['k' => 1]]);
        $this->assertSame(['k' => 1], $map->nested);
    }
    /** @dataProvider falsyInput */
    public function testFalsyScalarInputIsTreatedAsEmpty($input)
    {
        $this->assertSame([], get_object_vars(new map($input)));
    }
    public function falsyInput() { return [[0], [false], ['0'], [''], [0.0], [[]]]; }
    public function testWhitespaceAndOtherNonEmptyScalarsAreData()
    {
        foreach ([' ', 'a', 1, true, 1.5, '00'] as $input) $this->assertSame($input, (new map($input))->data);
    }
}
