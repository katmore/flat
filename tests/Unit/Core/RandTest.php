<?php
namespace flat\test\Unit\Core;

use flat\core\rand;
use PHPUnit\Framework\TestCase;

class RandTest extends TestCase
{
    public function testGetWithinBounds()
    {
        $seen = [];
        for ($i = 0; $i < 200; $i++) {
            $value = rand::get(5, 10);
            $this->assertIsInt($value);
            $this->assertGreaterThanOrEqual(5, $value);
            $this->assertLessThanOrEqual(10, $value);
            $seen[$value] = true;
        }
        $this->assertGreaterThan(1, count($seen));
    }

    public function testGetMinEqualsMax()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->assertSame(7, rand::get(7, 7));
        }
    }

    public function testZeroMaximumMeansTheDefaultMaximum()
    {
        for ($i = 0; $i < 20; $i++) {
            $value = rand::get(1, 0);
            $this->assertGreaterThanOrEqual(1, $value);
            $this->assertLessThanOrEqual(getrandmax(), $value);
        }
    }

    public function testGetRejectsMaxLessThanMin()
    {
        $this->expectException(\flat\core\rand\exception\bad_param::class);
        rand::get(10, 5);
    }

    public function testGetRejectsNegativeMin()
    {
        $this->expectException(\flat\core\rand\exception\bad_param::class);
        rand::get(-1, 5);
    }

    public function testGetRejectsNonIntParams()
    {
        try {
            rand::get('a', 5);
            $this->fail('expected bad_param for string min');
        } catch (\flat\core\rand\exception\bad_param $e) {
            $this->addToAssertionCount(1);
        }
        $this->expectException(\flat\core\rand\exception\bad_param::class);
        rand::get(1.5, 5);
    }

    public function testGetRejectsBadMaxParameters()
    {
        try {
            rand::get(0, 'a');
            $this->fail('expected bad_param for string max');
        } catch (\flat\core\rand\exception\bad_param $e) {
            $this->addToAssertionCount(1);
        }
        $this->expectException(\flat\core\rand\exception\bad_param::class);
        rand::get(0, getrandmax() + 1);
    }
    public function testBadParamMessageNamesTheParameterAndOptionalDetails()
    {
        $this->assertSame("bad param 'min'", (new rand\exception\bad_param('min'))->getMessage());
        $this->assertSame("bad param 'min': too small", (new rand\exception\bad_param('min','too small'))->getMessage());
    }

    public function testBytesLength()
    {
        $a = rand::bytes(16);
        $b = rand::bytes(16);
        $this->assertSame(16, strlen($a));
        $this->assertSame(16, strlen($b));
        $this->assertNotSame($a, $b);
    }

    public function testInvokeAndGenerateMatchBounds()
    {
        $rand = new rand(5, 10);
        for ($i = 0; $i < 50; $i++) {
            $generated = $rand->generate();
            $this->assertGreaterThanOrEqual(5, $generated);
            $this->assertLessThanOrEqual(10, $generated);
            $invoked = $rand(5, 10);
            $this->assertGreaterThanOrEqual(5, $invoked);
            $this->assertLessThanOrEqual(10, $invoked);
        }
    }
    /** @group known-defect */
    public function testInvocationWithoutArgumentsCurrentlyIgnoresConstructedBounds()
    {
        $rand = new rand(5, 10);
        for ($i = 0; $i < 50; $i++) {
            $this->assertGreaterThanOrEqual(5, $rand->generate());
            $this->assertLessThanOrEqual(10, $rand->generate());
        }
        // Invoked without arguments, the object falls back to 0..getrandmax().
        $outside = 0;
        for ($i = 0; $i < 100; $i++) {
            $value = $rand();
            $this->assertGreaterThanOrEqual(0, $value); $this->assertLessThanOrEqual(getrandmax(), $value);
            if ($value > 10) $outside++;
        }
        $this->assertGreaterThan(0, $outside);
    }
}
