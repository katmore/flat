<?php
namespace flat\test\Unit\Data;
use PHPUnit\Framework\TestCase;
class DataFixture extends \flat\data { public $first = 'one'; public $second = 'two'; public $nil; protected $hidden = 'secret'; }
class ReadyFixture extends DataFixture implements \flat\data\ready, \flat\data\transform
{
    public $stages = [];
    public function data_transform(array $data = null) { $this->stages[] = 'transform'; }
    public function data_ready() { $this->stages[] = 'ready'; }
}
class ShortTypeFixture extends DataFixture implements \flat\data\typed\shortname { public $type; }
class FullTypeFixture extends DataFixture implements \flat\data\typed\classname { public $type; }
class ExplicitTypeFixture extends DataFixture implements \flat\data\typed\explicit { public $type; public static function get_data_type() { return 'explicit'; } }
class TextFixture extends DataFixture implements \flat\data\stringify\text { public function get_text() { return 'text representation'; } }
class BadTextFixture extends DataFixture implements \flat\data\stringify\text { public function get_text() { return false; } }
class JsonFixture extends DataFixture implements \flat\data\stringify\json {}
class XmlFixture extends DataFixture implements \flat\data\stringify\xml { public function get_xml_serialize_options() { return ['meta' => false]; } }
class HtmlFixture extends DataFixture implements \flat\data\stringify\html { public function get_html_serialize_options() { return []; } }
class UnknownStringFixture extends DataFixture implements \flat\data\stringify {}
class UnstringableFixture extends DataFixture implements \flat\data\not_stringable {}
class EmptyStringFixture extends DataFixture implements \flat\data\stringify, \flat\data\stringify\empty_string {}
class WebFixture extends DataFixture implements \flat\data\webview {}
class OtherFieldsFixture extends \flat\data { public $alpha; }
class RulesFixture extends DataFixture implements \flat\data\rules\consumer
{
    public $rules;
    public function set_data_rules(\flat\data\rules $rules) { $this->rules = $rules; }
}
class MapperTest extends TestCase
{
    public function testBaseFieldListIsAbsentAndUnknownDumpTypesUseHumanFallback()
    {
        $this->assertNull(\flat\data::field_list());
        $data = new DataFixture;
        $this->assertSame((string)$data->dump('human'),(string)$data->dump('unknown'));
    }
    protected function setUp(): void
    {
        $p = new \ReflectionProperty(\flat\core\controller\data::class, '_field');
        $p->setAccessible(true); $p->setValue(null, null);
    }
    protected function tearDown(): void { $this->setUp(); }
    public function testWebviewDataIsHtmlEncodedOnConstruction()
    {
        $data = new WebFixture(['first' => '<tag>&']);
        $this->assertSame('&lt;tag&gt;&amp;', $data->first);
        $plain = new DataFixture(['first' => '<tag>&']);
        $this->assertSame('<tag>&', $plain->first);
        $this->assertStringContainsString('&lt;tag&gt;', $plain->dump('human', ['htmlencode'])->value);
        $this->assertStringContainsString('<tag>', $plain->dump('human')->value);
    }
    public function testRuleConsumerReceivesConstructedAndExplicitRules()
    {
        $data = new RulesFixture(['first' => 'value']);
        $this->assertSame(['first' => 'value'], $data->rules->data);
        $rules = new \flat\data\rules(['data' => ['first' => 'next']]);
        $data->map($rules);
        $this->assertSame($rules, $data->rules);
    }
    public function testFieldListUsesTheInstanceAndCachesPerClass()
    {
        $this->assertSame(['first','second','nil'], DataFixture::field_list(new DataFixture));
        $this->assertSame(['first','second','nil'], DataFixture::field_list());
        // Each class keeps its own list; the shared cache used to leak the first one.
        $this->assertSame(['alpha'], OtherFieldsFixture::field_list());
        $this->assertSame(['alpha'], DataFixture::field_list(new OtherFieldsFixture));
    }
    public function testConstructorsMapNamedAndPositionalDataAndIgnoreUnknownFields()
    {
        $a = new DataFixture(['first' => 42, 'unknown' => 1]);
        $this->assertSame(42, $a->first);
        $this->assertFalse(property_exists($a, 'unknown'));
        $b = new DataFixture('a', 'b');
        $this->assertSame('a', $b->first); $this->assertSame('b', $b->second);
        $this->assertSame(['first' => 42, 'second' => 'two', 'nil' => null], $a->get_as_assoc());
        $this->assertEquals((object) $a->get_as_assoc(), $a->get_as_stdClass());
    }
    public function testFieldInspectionAndCallbacks()
    {
        $a = new DataFixture;
        $this->assertTrue($a->is_field_exist('nil'));
        $this->assertFalse($a->is_field_exist('missing'));
        $this->assertFalse($a->is_field_exist('hidden'));
        $this->assertNull($a->check_field_exist('first'));
        $this->assertSame(['first', 'second', 'nil'], DataFixture::field_list());
        $this->assertSame(['first', 'second', 'nil'], DataFixture::field_list());
        $this->assertTrue(DataFixture::is_field('first'));
        $this->assertTrue(DataFixture::is_field(['first', 'second']));
        $this->assertFalse(DataFixture::is_field(['first', 'missing']));
        $this->assertNull(DataFixture::is_field([123]));
        $seen = [];
        $callback = function ($name, $result) use (&$seen) { $seen[$name] = $result; };
        $this->assertFalse(DataFixture::is_field(['missing', 'other'], $callback));
        $this->assertSame(['missing' => false, 'other' => false], $seen);
        $this->assertTrue(DataFixture::is_field('first', $callback));
        $this->assertNull(DataFixture::is_field(123));
    }
    public function testInvalidFieldName()
    {
        $this->expectException(\flat\data\exception\bad_field::class);
        (new DataFixture)->is_field_exist(123);
    }
    public function testMissingField()
    {
        $this->expectException(\flat\data\exception\not_field::class);
        (new DataFixture)->check_field_exist('missing');
    }
    public function testMagicFieldAccessStrippingAndFallbacks()
    {
        $a = new DataFixture(['first' => '<b>bold</b><i>italic</i>', 'second' => '']);
        $this->assertSame($a->first, $a->first());
        $this->assertSame('bolditalic', $a->first(['strip' => '']));
        $this->assertSame('<b>bold</b>italic', $a->strip('first', '<b>'));
        $this->assertSame('bolditalic', $a->strip_tags('first', ''));
        $this->assertSame('fallback', $a->second(['on_empty' => 'fallback']));
        $this->assertSame('fallback', $a->second('fallback'));
        $this->assertNull($a->missing());
        $this->assertNull(DataFixture::strip_tags_value([]));
        $this->assertSame('fallback', DataFixture::or_empty_value(0, 'fallback'));
        $this->assertSame('value', DataFixture::or_empty_value('value'));
        $this->assertSame('unknown', $a->or_empty('second'));
        $this->assertSame('<b>bold</b><i>italic</i>', $a->first(['strip']));
    }
    public function testEachFieldAndScalarConversions()
    {
        $a = new DataFixture(['first' => '42', 'second' => '1.5', 'nil' => 'hello']);
        $a->strings_to_scalar(true);
        $this->assertSame(42, $a->first->value);
        $this->assertSame(1.5, $a->second->value);
        $this->assertSame('hello', $a->nil->value);
        $b = new DataFixture(['first' => '42', 'second' => 1, 'nil' => 2.5]);
        $b->strings_to_scalar(); $b->ints_to_scalar(); $b->floats_to_scalar();
        $this->assertSame('42', $b->first->value);
        $this->assertSame(1, $b->second->value);
        $this->assertSame(2.5, $b->nil->value);
        $c = new DataFixture;
        $c->null_to_emptystring(); $this->assertSame('', $c->nil);
        $c->each_field(function ($value) { return strtoupper($value); }, ['first']);
        $this->assertSame('ONE', $c->first); $this->assertSame('two', $c->second);
    }
    public function testRecursiveHtmlEncodingClonesTheData()
    {
        $a = new DataFixture(['first' => '<b>&amp;</b>', 'second' => [true, false, null, 4, 1.5], 'nil' => new DataFixture(['first' => '<'])]);
        $b = $a->htmlentities(ENT_QUOTES, 'UTF-8', true);
        $this->assertNotSame($a, $b);
        $this->assertSame('&lt;b&gt;&amp;amp;&lt;/b&gt;', $b->first);
        $this->assertSame(['true', 'false', '', '4', '1.5'], $b->second);
        $this->assertSame('&lt;', $b->nil->first);
        $this->assertSame('<b>&amp;</b>', $a->first);
        $this->assertSame('&lt;b&gt;&amp;&lt;/b&gt;', $a->htmlentities()->first);
    }
    public function testTypeAndLifecycleInterfaces()
    {
        $a = new ReadyFixture(['first' => 'mapped']);
        $this->assertSame(['transform', 'ready'], $a->stages);
        $this->assertSame('mapped', $a->first);
        $this->assertSame('ShortTypeFixture', (new ShortTypeFixture)->type);
        $this->assertSame(FullTypeFixture::class, (new FullTypeFixture)->type);
        $this->assertSame('explicit', (new ExplicitTypeFixture)->type);
    }
    public function testStringRepresentations()
    {
        $a = new DataFixture;
        $this->assertSame(json_encode($a), $a->get_string());
        $this->assertSame(json_encode($a), (string) $a);
        $this->assertSame(json_encode($a), $a->to_json());
        $this->assertSame(json_encode($a), $a->get_string('json'));
        $this->assertStringContainsString('one', $a->to_xml());
        $this->assertStringContainsString('one', $a->get_string('xml'));
        $this->assertStringContainsString('flat-encoded-html', $a->get_string('html'));
        $this->assertSame('text representation', (new TextFixture)->get_string());
        $this->assertSame('', (new EmptyStringFixture)->get_string());
        $this->assertSame(json_encode(new JsonFixture), (new JsonFixture)->get_string());
        $this->assertStringContainsString('one', (new XmlFixture)->get_string());
        $this->assertStringContainsString('flat-encoded-html', (new HtmlFixture)->get_string());
    }
    /** @dataProvider invalidStringifiers */
    public function testInvalidStringifierThrows($class)
    {
        $this->expectException(\flat\data\exception\not_stringable::class);
        (new $class)->get_string();
    }
    public function invalidStringifiers() { return [[UnstringableFixture::class], [BadTextFixture::class], [UnknownStringFixture::class]]; }
    public function testKeyPrefixMappingPreservesUnmatchedKeysAndExistingMap()
    {
        $data = ['pre_first' => 1, 'second' => 2]; $map = ['existing' => 3];
        $this->assertSame(['existing' => 3, 'first' => 1, 'second' => 2], DataFixture::map_key_prefix($data, 'pre_', $map));
        $this->assertSame($map, DataFixture::map_key_prefix($data, [], $map));
        $this->assertSame($data, DataFixture::map_key_prefix($data, null));
        $this->assertArrayHasKey('first', DataFixture::map_key_prefix($data, ['pre_', 'other_']));
    }
    public function testInvalidPrefixThrows()
    {
        $data = ['a' => 1];
        $this->expectException(\flat\data\exception\bad_rule::class);
        DataFixture::map_key_prefix($data, 123);
    }
    public function testEmptyMappingKeyThrows()
    {
        $data = ['' => 1];
        $this->expectException(\flat\core\mappable\exception\bad_property_name::class);
        DataFixture::map_key_prefix($data, 'prefix');
    }
    /** @dataProvider nonNumericStrings */
    public function testNumericConversionPreservesNonNumericStrings($text)
    {
        $data = new DataFixture(['first' => $text]);
        $data->strings_to_scalar(true);
        $this->assertSame($text, $data->first->value);
        $this->assertSame('string', $data->first->gettype());
    }
    public function nonNumericStrings() { return [['hello'], ['12cats'], ['1.5x'], ['<b>text</b>']]; }
    public function testDumpFormatsAndStringFailureRecovery()
    {
        $data = new DataFixture;
        $this->assertStringContainsString('first', (string) $data->dump());
        $this->assertStringContainsString('DataFixture', (string) $data->dump('var'));
        $this->assertStringContainsString('first', (string) $data->dump('human', ['htmlencode']));
        $notices = [];
        set_error_handler(function ($number, $message) use (&$notices) {
            if ($number === E_USER_NOTICE && strpos($message, 'exception while converting') === 0) {
                $notices[] = $message; return true;
            }
            return false;
        });
        try { $this->assertSame('', (string) new BadTextFixture); }
        finally { restore_error_handler(); }
        $this->assertCount(1, $notices);
        $this->assertStringContainsString('cannot be converted to string', $notices[0]);
    }
    public function testHtmlEncodingPlainObjectsAndUnsupportedResources()
    {
        $stream = fopen('php://memory', 'w+');
        try {
            $data = new DataFixture(['first' => (object) ['x' => '<'], 'second' => $stream]);
            $encoded = $data->htmlentities();
            $this->assertSame('&lt;', $encoded->first->x);
            $this->assertSame('??', $encoded->second);
            // Current clone is shallow for a plain nested object.
            $this->assertSame($data->first, $encoded->first);
        } finally { fclose($stream); }
    }
    public function testUtf8FixOnlyTouchesSelectedStringFields()
    {
        $data = new DataFixture(['first' => "caf\xe9", 'second' => 42]);
        $data->utf8_fix(['first']);
        $this->assertSame("caf\xc3\xa9", $data->first);
        $this->assertSame(42, $data->second);
    }

    public function testMapAppliesMissingDefaultsAndOptionalEmptyDefaults()
    {
        $data = new DataFixture;
        $data->map(['first' => 'set'], null, ['first' => 'fallback', 'second' => 'default']);
        $this->assertSame('set', $data->first);
        $this->assertSame('default', $data->second);
        $data->map(['first' => ''], null, ['first' => 'fallback']);
        $this->assertSame('', $data->first);
        $data->map(['first' => ''], null, ['first' => 'fallback'], ['default_on_empty']);
        $this->assertSame('fallback', $data->first);
        $data->map(['pre_first' => 'prefixed'], 'pre_');
        $this->assertSame('prefixed', $data->first);
    }
    public function testRuleObjectConstructorAndRemapping()
    {
        $rules = new \flat\data\rules(['data' => ['first' => 'ruled']]);
        $data = new DataFixture($rules);
        $this->assertSame('ruled', $data->first);
        $data->map($rules);
        $this->assertSame('ruled', $data->first);
    }
    /** @dataProvider invalidMappingRules */
    public function testInvalidMappingRules($data, $defaults, $flags)
    {
        $this->expectException(\flat\data\exception\bad_rule::class);
        (new DataFixture)->map($data, null, $defaults, $flags);
    }
    public function invalidMappingRules() { return [[null, null, null], [['first' => 1], 'bad', null], [['first' => 1], null, 'bad']]; }

}
