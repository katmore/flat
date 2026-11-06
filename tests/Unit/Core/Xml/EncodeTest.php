<?php
namespace flat\test\Unit\Core\Xml;
use flat\core\xml\encode;
use PHPUnit\Framework\TestCase;
class NamedXmlFixture { public $value = 'text'; }
class EncodeTest extends TestCase
{
    private function document($input, array $options = [])
    {
        $doc = new \DOMDocument;
        $this->assertTrue($doc->loadXML(encode::serialize($input, $options), LIBXML_NONET));
        return $doc;
    }
    /** @dataProvider scalars */
    public function testScalarDocuments($input, $text, $meta)
    {
        $root = $this->document($input)->documentElement;
        $this->assertSame($text, trim($root->textContent));
        $this->assertSame($meta, $root->getAttributeNS(encode::xmlns, 'meta'));
        $this->assertSame('0.3', $root->getAttributeNS(encode::xmlns, 'flat-xml-ver'));
    }
    public function scalars() { return [['hello & <world>', 'hello & <world>', 'string'], [42, '42', 'integer'], [1.5, '1.5', 'double'], [true, '1', 'boolean'], [false, '', 'boolean'], [null, '', 'NULL'], ['', '', 'string'], [0, '', 'integer']]; }
    public function testObjectAndArrayTypes()
    {
        $root = $this->document([1, 2])->documentElement;
        $this->assertSame('xs:integer[2]', $root->getAttribute('extxs:ArrayType'));
        $this->assertSame('extxs:Hashmap', $this->document(['key' => 1])->documentElement->getAttribute('xsi:type'));
        $this->assertSame('extxs:Mixed[2]', $this->document([1, 'text'])->documentElement->getAttribute('extxs:ArrayType'));
        $this->assertSame('xs:integer[12]', $this->document(range(1, 12))->documentElement->getAttribute('extxs:ArrayType'));
        $this->assertSame('Generic', $this->document((object) ['key' => 'value'])->documentElement->getAttribute('extxs:ObjectType'));
        $this->assertSame('\\' . NamedXmlFixture::class, $this->document(new NamedXmlFixture)->documentElement->getAttribute('extxs:ObjectType'));
    }
    public function testNestedNodesEscapingAndTypeDetection()
    {
        $doc = $this->document(['items' => [1, 2], 'object' => new NamedXmlFixture, 'nil' => null, 'empty' => '', 'zero' => 0, 'bool' => false,
            'integerString' => '42', 'floatString' => '1.5', 'date' => '2001-01-01T12:00:00Z', 'hex' => 'ABCDEF', 'url' => 'https://example.com/path', 'ordinary' => 'hello world']);
        $root = $doc->documentElement;
        $expected = ['integerstring' => 'extxs:NumericStringInt', 'floatstring' => 'extxs:NumericStringFloat', 'date' => 'xs:DateTime', 'hex' => 'xs:hexBinary', 'url' => 'xs:anyURI', 'ordinary' => 'xs:string'];
        foreach ($expected as $tag => $type) $this->assertSame($type, $root->getElementsByTagName($tag)->item(0)->getAttribute('xsi:type'));
        $this->assertSame('true', $root->getElementsByTagName('nil')->item(0)->getAttribute('xsi:nil'));
        $this->assertSame('', $root->getElementsByTagName('bool')->item(0)->textContent);
    }
    public function testInvalidAndNumericKeysUseDefaultNodeAndPreserveKey()
    {
        $root = $this->document(['bad key' => 'x', '1start' => 'y', '' => 'z', 'UPPER' => 'text', 3 => 'indexed'], ['default_node' => 'item'])->documentElement;
        $items = $root->getElementsByTagName('item');
        $this->assertSame(4, $items->length);
        $this->assertSame('bad key', $items->item(0)->getAttribute('extxs:key'));
        $this->assertSame('1start', $items->item(1)->getAttribute('extxs:key'));
        $this->assertSame('3', $items->item(3)->getAttribute('extxs:index'));
        $this->assertSame('UPPER', $root->getElementsByTagName('upper')->item(0)->getAttribute('extxs:key'));
    }
    public function testMetaOptionsCustomRootChecksumsAndIndentation()
    {
        $doc = $this->document(['a' => 1], ['top_node' => 'root', 'meta' => false, 'checksum' => [], 'indent_size' => 1]);
        $this->assertSame('root', $doc->documentElement->nodeName);
        $this->assertFalse($doc->documentElement->hasAttribute('fx:meta'));
        $this->assertFalse($doc->documentElement->hasAttribute('fx:md5'));
        $this->assertSame('custom', $this->document('text', ['meta_attr' => 'custom'])->documentElement->getAttribute('fx:meta'));
        $this->assertSame('custom & text', $this->document('text', ['meta' => 'custom & text'])->documentElement->getAttribute('fx:meta'));
        $this->assertSame('integer', $this->document(42, ['meta' => []])->documentElement->getAttribute('fx:meta'));
        // Current checksum is derived from null, regardless of the serialized input.
        $this->assertSame(md5('null'), $this->document('first')->documentElement->getAttribute('fx:md5'));
        $this->assertSame(sha1('null'), $this->document('second', ['checksum' => ['sha1']])->documentElement->getAttribute('fx:sha1'));
    }
    public function testNamedNestedStructureAndRealFloatValues()
    {
        $root = $this->document((object) ['float'=>1.25,'named'=>new NamedXmlFixture])->documentElement;
        $this->assertSame('xs:decimal',$root->getElementsByTagName('float')->item(0)->getAttribute('xsi:type'));
        $this->assertStringContainsString(NamedXmlFixture::class,$root->textContent);
    }
    public function testStructureMetadataFallbackForNonScalarMetaOption()
    {
        $input = (object) ['nested'=>(object) ['value'=>'text']];
        $root = $this->document($input,['meta'=>[]])->documentElement;
        $this->assertSame('extxs:structure',$root->getAttributeNS(encode::xmlns,'meta'));
    }
    public function testNestedArrayAndObjectTypeDetection()
    {
        $root = $this->document([range(1,12),new NamedXmlFixture,null])->documentElement;
        $this->assertSame('extxs:Mixed[3]',$root->getAttribute('extxs:ArrayType'));
        $this->assertSame('array',$root->getElementsByTagNameNS(encode::xmlns,'data')->item(0)->getAttribute('fx:php-type'));
    }
    public function testDumpEnabledEmptyAndNumericStringObjectKeys()
    {
        $object = new \stdClass; $object->{''}='empty'; $object->{'0'}='zero';
        $doc = $this->document($object,['dump_ok'=>true]);
        $this->assertStringContainsString('data:application/php-object-dump;base64,',$doc->saveXML());
        $this->assertEquals($object,encode::unserialize($doc->saveXML()));
    }
    public function testResourceFallbackWithAndWithoutDumpPreservesWarnings()
    {
        $handle = fopen('php://memory','r+'); $warnings=[];
        set_error_handler(function ($level,$message) use (&$warnings) {
            if (strpos($message,'htmlspecialchars() expects parameter 1 to be string') === 0 || $message === 'Undefined variable: input') { $warnings[]=$message; return true; }
            return false;
        });
        try {
            $plain = encode::serialize($handle,['dump_ok'=>false,'checksum'=>[]]);
            $this->assertStringContainsString('<!--unserializable-->',$plain);
            $dump = encode::serialize($handle,['dump_ok'=>true,'checksum'=>[]]);
            $this->assertStringContainsString('extxs:DumpObject="extxs:Resource"',$dump);
            $this->assertStringContainsString('extxs:encoding="none"',$dump);
            $legacy = encode::serialize($handle,['dump_ok'=>false,'checksum'=>['md5']]);
            $this->assertStringContainsString('<fx:data md5="'.md5(''). '">', $legacy);
        } finally { restore_error_handler(); fclose($handle); }
        $this->assertCount(4,$warnings);
    }
    public function testLegacyIndexAndScalarTypeDecoding()
    {
        $prefix = '<?xml version="1.0"?><fx:data xmlns:fx="'.encode::xmlns.'" xmlns="'.encode::xmlns.'/obj" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:extxs="'.encode::xmlns.'-extxs" xsi:type="extxs:Array">';
        $xml = $prefix.'<item extxs:index="7" xsi:type="xs:decimal">1.25</item><flag xsi:type="xs:boolean">true</flag></fx:data>';
        $this->assertSame([7=>1.25,'flag'=>true],encode::unserialize($xml));
    }
    public function testInstanceAndCompatibilityAlias()
    {
        $object = new encode('text', ['meta' => false]);
        $this->assertSame($object->get_xml(), (string) $object);
        $this->assertStringContainsString('text', (string) $object);
        $this->assertStringContainsString('text', \flat\core\encode\xml::serialize('text'));
    }
    /** @dataProvider roundTripValues */
    public function testRoundTripsValuesWithExplicitMetadata($value)
    {
        $decoded = encode::unserialize(encode::serialize($value));
        $this->assertEquals($value, $decoded);
        $this->assertSame(gettype($value), gettype($decoded));
        if (is_array($value)) $this->assertSame($value, $decoded);
    }
    public function roundTripValues()
    {
        return [[0], [false], [''], ['0'], [null], [true], [42], [1.5], ['hello & <world>'], ['  text  '], ["\nline\n"],
            [[]], [new \stdClass], [[0, false, '', null, '0', true]],
            [['' => '', 'UPPER' => 0, 'bad key' => false, 7 => null]],
            [[['nested' => [0, false, null, '']]]],
            [(object) ['number' => 0, 'flag' => false, 'text' => '', 'nothing' => null]],
            [(object) ['object' => (object) ['text' => 'nested'], 'array' => [1, 2]]]];
    }
    public function testAssocDecodingConvertsObjectsWithoutChangingScalarTypes()
    {
        $value = (object) ['nested' => (object) ['flag' => false]];
        $this->assertSame(['nested' => ['flag' => false]], encode::unserialize(encode::serialize($value), ['assoc' => true]));
        $this->assertNull(encode::unserialize('<?xml version="1.0"?><unrelated/>'));
    }
    public function testLegacyXmlWithoutNewMetadataCanStillBeRead()
    {
        $xml = '<?xml version="1.0"?><fx:data xmlns:fx="'.encode::xmlns.'" xmlns="'.encode::xmlns.'-object" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:extxs="'.encode::xmlns.'-extxs" xsi:type="extxs:Hashmap"><item extxs:key="count" xsi:type="xs:integer">42</item><text>hello</text><nothing xsi:nil="true"/></fx:data>';
        $this->assertSame(['count' => 42, 'text' => 'hello', 'nothing' => null], encode::unserialize($xml));
    }
}
