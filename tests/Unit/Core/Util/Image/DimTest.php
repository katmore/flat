<?php
namespace flat\test\Unit\Core\Util\Image;
use flat\core\util\image\dim;
use PHPUnit\Framework\TestCase;
class DimTest extends TestCase
{
    private function size($width, $height) { return new dim(['width' => $width, 'height' => $height]); }
    public function testAliasesRatioAndString()
    {
        $d = new dim(['x' => 20, 'y' => 10]);
        $this->assertSame(20, $d->width); $this->assertSame(10, $d->height);
        $this->assertSame(20, $d->w); $this->assertSame(10, $d->h);
        $this->assertSame(2.0, $d->ratio()); $this->assertSame('20x10', (string) $d);
        $d = new dim(['w' => '30', 'h' => '15']);
        $this->assertSame(30, $d->width); $this->assertSame(15, $d->height);
        $this->assertSame(1.0, (new dim)->ratio());
    }
    public function testScalingMaintainsRatioAndReturnsOriginalWhenUnnecessary()
    {
        $small = $this->size(20, 10); $min = $this->size(40, 40); $max = $this->size(100, 100);
        $this->assertSame('80x40', (string) dim::scale_up($small, $min));
        $this->assertSame($small, dim::scale_down($small, $max));
        $large = $this->size(200, 100);
        $this->assertSame('100x50', (string) dim::scale_down($large, $max));
        $this->assertSame($large, dim::scale_up($large, $min));
        $this->assertSame('100x50', (string) dim::scale_dim($large, $min, $max));
    }
    public function testInstanceScalingModes()
    {
        $d = $this->size(20, 10); $d->scale($this->size(40, 40));
        $this->assertSame('80x40', (string) $d);
        $d->scale(null, $this->size(20, 20)); $this->assertSame('20x10', (string) $d);
        $d->scale($this->size(40, 40), $this->size(100, 100)); $this->assertSame('80x40', (string) $d);
        $d->scale(); $this->assertSame('80x40', (string) $d);
    }
    /** @dataProvider invalidSizes */
    public function testInvalidSizes($value)
    {
        $this->expectException(\flat\core\util\image\exception\bad_dim::class);
        $this->size($value, 10);
    }
    public function invalidSizes() { return [['not numeric'], [-1]]; }
}
