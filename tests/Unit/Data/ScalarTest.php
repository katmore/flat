<?php
namespace flat\test\Unit\Data;
use flat\data\scalar;
use flat\data\rules;
use PHPUnit\Framework\TestCase;
class ScalarTest extends TestCase
{
    /** @dataProvider values */
    public function testScalarValuesAndType($value, $type)
    {
        $s = new scalar($value);
        $this->assertSame($value, $s->value);
        $this->assertSame($type, $s->gettype());
        $this->assertSame((string) $value, (string) $s);
    }
    public function values() { return [['text', 'string'], [42, 'integer'], [1.5, 'double'], [true, 'boolean']]; }
    public function testNestedScalarAndUnserialization()
    {
        $inner = new scalar('nested'); $outer = new scalar($inner);
        $this->assertSame($inner, $outer->value);
        $this->assertSame(scalar::class, $outer->gettype());
        $this->assertSame('nested', scalar::serialize($outer));
        $this->assertSame('restored', scalar::unserialize('restored')->value);
    }
    public function testArrayAndRuleInputForms()
    {
        $this->assertSame('array', (new scalar(['value' => 'array']))->value);
        $this->assertSame(7, (new scalar(new rules(['data' => 7])))->value);
        $this->assertSame('object', (new scalar(new rules(['data' => (object) ['value' => 'object']])))->value);
        $this->assertSame('map', (new scalar(new rules(['data' => ['value' => 'map']])))->value);
    }
    public function testEmptyInputsUseFallbackAndUnsupportedInputsRemainNull()
    {
        foreach ([null, false, 0, '', '0', []] as $value) $this->assertSame('', (new scalar($value))->value);
        foreach ([new \stdClass, ['value' => []]] as $value) $this->assertNull((new scalar($value))->value);
        // Maintainer contract: PHP-empty inputs use the supplied fallback.
        $this->assertSame('fallback', (new scalar('', 'fallback'))->value);
        $this->assertSame(0, (new scalar(false, 0))->value);
    }
    public function testPlainDisplay()
    {
        $this->expectOutputString('<text>');
        (new scalar('<text>'))->display([]);
    }
    public function testHtmlAndCommentDisplay()
    {
        $this->expectOutputString('<!-- begin display flat\\data\\scalar&lt;text&gt; end display -->' . "\n");
        (new scalar('<text>'))->display(['html', 'html_comment']);
    }
}
