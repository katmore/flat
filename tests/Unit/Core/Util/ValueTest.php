<?php
namespace flat\test\Unit\Core\Util;
use flat\core\util\validate\value;
use PHPUnit\Framework\TestCase;
class StringableFixture { public function __toString() { return 'text'; } }
class ValueTest extends TestCase
{
    /** @dataProvider stringableValues */
    public function testStringability($input, $expected)
    {
        $this->assertSame($expected, value::is_stringable($input));
    }
    public function stringableValues()
    {
        return [[null, true], [false, true], [0, true], ['text', true], [[], null], [new \stdClass, null], [new StringableFixture, true]];
    }
    public function testResourceCanBeConvertedToStringWithoutClosingIt()
    {
        $stream = fopen('php://memory', 'w+');
        try { $this->assertTrue(value::is_stringable($stream)); $this->assertTrue(is_resource($stream)); }
        finally { fclose($stream); }
    }
    /** @dataProvider scalarValues */
    public function testScalarRequirementReturnsTheUnchangedScalar($value)
    {
        $this->assertSame($value, value::require_scalar($value));
        $this->assertTrue(value::require_scalar($value, ['return_valid_value' => false]));
    }
    public function scalarValues() { return [[0], [false], [''], ['0'], [42], [1.5], [true], ['text']]; }
    public function testInvalidScalarRequirementsAndFallbacks()
    {
        foreach ([null, [], new \stdClass] as $value) {
            $this->assertNull(value::require_scalar($value));
            $this->assertFalse(value::require_scalar($value, ['return_valid_value' => false]));
            foreach ([0, false, '', 'fallback'] as $fallback) {
                $this->assertSame($fallback, value::require_scalar($value, ['invalid_return' => $fallback]));
            }
        }
        $this->assertSame('text', value::require_scalar('text', ['unknown_option' => true]));
    }
}
