<?php
namespace flat\test\Unit\Core\Html;
use flat\core\html\encode;
use PHPUnit\Framework\TestCase;
class HtmlTypeProbe extends encode { public static function typeOf($value): string { return parent::_data_to_type($value); } }
class HtmlNamedFixture { public $key = 'value'; }
class EncodeTest extends TestCase
{
    /** @dataProvider types */
    public function testTypeLabels($value, $label)
    {
        $this->assertSame($label, HtmlTypeProbe::typeOf($value));
    }
    public function types()
    {
        return [[null, 'null'], [true, 'boolean'], [1, 'integer'], [1.5, 'double'], ['text', 'string'],
            [new \stdClass, 'object'], [new HtmlNamedFixture, HtmlNamedFixture::class],
            [[], '[]'], [[1,2], 'integer[]'], [[1,'text'], 'array'], [['key'=>1], 'hashmap']];
    }
    public function testScalarEscapingAndBooleanMarkers()
    {
        $this->assertStringContainsString('&lt;b&gt;&quot;&#039;&amp;', encode::serialize('<b>"\'&'));
        $this->assertStringContainsString('data-boolean-true>true</span>', encode::serialize(true));
        $this->assertStringContainsString('data-boolean-false>false</span>', encode::serialize(false));
        $this->assertStringContainsString('data-empty-string', encode::serialize(''));
        $this->assertStringContainsString('data-null-value', encode::serialize(null));
        $this->assertStringContainsString('data-item-type="integer">42</span>', encode::serialize(42));
        $this->assertStringContainsString("\x01", encode::serialize("\x01"));
    }
    public function testArraysObjectsKeysAndCustomTags()
    {
        $html = encode::serialize(['name' => [1, 2], 'mixed' => [1, 'text'], 'object' => new HtmlNamedFixture, 'empty' => [], 'nil' => null]);
        $this->assertStringContainsString('data-item-hashmap', $html);
        $this->assertStringContainsString('data-item-array="integer[]"', $html);
        $this->assertStringContainsString('data-item-array', $html);
        $this->assertStringContainsString('data-key="name"', $html);
        $this->assertStringContainsString('data-index="0"', $html);
        $this->assertStringContainsString('data-item-object=', $html);
        $custom = encode::serialize(['key' => 'value'], ['parent_element' => 'section', 'child_element' => 'article', 'indent_level' => 1, 'indent_size' => 2]);
        $this->assertStringContainsString('<section data-item-hashmap>', $custom);
        $this->assertStringContainsString('<article data-item', $custom);
    }
    public function testResourcesUseDumpAndUnknownType()
    {
        $resource = fopen('php://memory', 'w+');
        try {
            $this->assertSame('unknown', HtmlTypeProbe::typeOf($resource));
            $this->assertStringContainsString('data-dump', encode::serialize($resource));
        } finally { fclose($resource); }
    }
    public function testUnserializationIsExplicitlyNotReady()
    {
        $this->expectException(\flat\core\status\exception\feature_not_ready::class);
        encode::unserialize('<div>text</div>');
    }
}
