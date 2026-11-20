<?php
namespace flat\test\Unit\Core\Xml;
use flat\core\xml\dom;
use PHPUnit\Framework\TestCase;
class NoDocumentDom extends dom { public function get_DOMDocument() { return null; } }
class DomTest extends TestCase
{
    const XML = '<?xml version="1.0"?><root id="example"><item>A</item><item>B</item></root>';
    public function testSubclassWithoutADocumentHasNoXPathResults()
    {
        $dom = new NoDocumentDom(null);
        $this->assertNull($dom->get_DOMDocument()); $this->assertNull($dom->xpath('//item'));
    }
    public function testInvalidXmlFileRaisesTheLibraryException()
    {
        $path = tempnam(sys_get_temp_dir(),'flat-xml-invalid-'); file_put_contents($path,'<root>');
        try {
            $this->expectException(\flat\core\xml\dom\exception\invalid_input::class);
            $this->expectExceptionMessage('could not load file'); dom::load($path);
        } finally { unlink($path); }
    }
    public function testHtmlConversionFailureRestoresLibxmlState()
    {
        $before = libxml_use_internal_errors();
        set_error_handler(function ($level,$message) { if ($message === 'Array to string conversion') throw new \RuntimeException('input conversion failed'); return false; });
        try {
            try { dom::load_html(['unexpected input']); }
            catch (\flat\core\xml\dom\exception\invalid_input $e) {
                $this->assertStringContainsString('input conversion failed',$e->getMessage());
                $this->assertSame($before,libxml_use_internal_errors()); return;
            }
            $this->fail('Input conversion failure must be wrapped');
        } finally { restore_error_handler(); }
    }
    public function testGenericInputFailureReportsThePublicInputContract()
    {
        set_error_handler(function ($level,$message) { if ($message === 'Array to string conversion') throw new \RuntimeException('input conversion failed'); return false; });
        try {
            $this->expectException(\flat\core\xml\dom\exception\invalid_input::class);
            $this->expectExceptionMessage('specified input is invalid XML'); dom::load(['unexpected input']);
        } finally { restore_error_handler(); }
    }
    public function testXmlLoadDocumentAndIterator()
    {
        $dom = dom::load_xml(self::XML);
        $this->assertInstanceOf(\DOMDocument::class, $dom->get_DOMNode());
        $this->assertSame($dom->get_DOMNode(), $dom->get_DOMDocument());
        $this->assertSame($dom->get_DOMDocument(), $dom->get_DOMDocument());
        $this->assertTrue($dom->valid()); $this->assertSame('root', $dom->key());
        $this->assertTrue($dom->hasChildren());
        $children = $dom->getChildren();
        $this->assertSame('item', $children->key());
        $this->assertSame('A', $children->current()->textContent);
        $children->next(); $this->assertSame('B', $children->current()->textContent);
        $children->next(); $this->assertFalse($children->valid());
        $children->rewind(); $this->assertSame('A', $children->current()->textContent);
        $text = $children->getChildren();
        $this->assertSame(0, $text->key()); $this->assertFalse($text->hasChildren());
        $this->assertStringContainsString('<root id="example">', $dom->xml());
    }
    public function testExistingNodeProducesAnIndependentDocument()
    {
        $doc = new \DOMDocument; $doc->loadXML('<root><item>A</item></root>');
        $dom = dom::load($doc->documentElement);
        $copy = $dom->get_DOMDocument();
        $this->assertNotSame($doc, $copy);
        $this->assertSame('root', $copy->documentElement->nodeName);
        $this->assertSame('A', $copy->documentElement->textContent);
    }
    public function testEmptyDomAndXmlStringConstructor()
    {
        $empty = dom::load();
        $this->assertFalse($empty->valid());
        $this->assertNull($empty->get_DOMDocument()->documentElement);
        $this->assertSame('root', dom::load(self::XML)->get_DOMDocument()->documentElement->nodeName);
    }
    public function testXmlFileConstructor()
    {
        $path = tempnam(sys_get_temp_dir(), 'flat-xml-');
        try {
            file_put_contents($path, self::XML);
            $this->assertSame('example', dom::load($path)->get_DOMDocument()->documentElement->getAttribute('id'));
        } finally { unlink($path); }
    }
    public function testHtmlLoadingAndAttributeStripping()
    {
        $before = libxml_use_internal_errors();
        $dom = dom::load_html('<p id="sample">café</p>');
        $this->assertSame($before, libxml_use_internal_errors());
        $this->assertSame('UTF-8', $dom->get_DOMDocument()->encoding);
        $this->assertStringContainsString('id="sample"', $dom->html([]));
        $this->assertStringNotContainsString('id="sample"', $dom->html(['no_attributes']));
        $this->assertSame('p', dom::load('<p>text</p>')->get_DOMDocument()->getElementsByTagName('p')->item(0)->nodeName);
    }
    public function testMalformedXmlRaisesLibraryException()
    {
        $this->expectException(\flat\core\xml\dom\exception\invalid_input::class);
        dom::load_xml('<?xml version="1.0"?><root>');
    }
    public function testXPathResultsSupportIterationAndChildAccess()
    {
        $dom = dom::load_xml(self::XML);
        $nodes = $dom->xpath('//item');
        $this->assertSame('A', $nodes->get(0)->textContent);
        $this->assertSame('B', $nodes->get(1)->textContent);
        $this->assertNull($nodes->get('0'));
        $this->assertNull($nodes->get(99));
        $this->assertTrue($nodes->valid());
        $this->assertSame('item', $nodes->key());
        $this->assertTrue($nodes->hasChildren());
        $text = $nodes->getChildren();
        $this->assertSame('A', $text->current()->nodeValue);
        $this->assertSame(0, $text->key());
        $this->assertFalse($text->hasChildren());
        $nodes->next(); $this->assertSame('B', $nodes->current()->textContent);
        $nodes->next(); $this->assertFalse($nodes->valid());
        $nodes->rewind(); $this->assertSame('A', $nodes->current()->textContent);
        $this->assertSame('example', $dom->xpath('/root')->get(0)->getAttribute('id'));
    }
    public function testEncoderRejectsUnrelatedXmlFormat()
    {
        $this->assertNull(\flat\core\xml\encode::unserialize(self::XML));
    }
    public function testObjectConversionGroupsRepeatedNodesPreservesEmptyFirstValue()
    {
        $xml = '<?xml version="1.0"?><root><item/><item/></root>';
        $object = dom::xml_to_object($xml);
        // Repeated values preserve the first empty string through deepcopy.
        $this->assertSame(['', ''], $object->root->item);
        $this->assertEquals(['root' => ['item' => ['', '']]], dom::load_xml($xml)->to_object(['assoc' => true]));
    }
    public function testObjectConversionUsesNameAndIndexAttributes()
    {
        $xml = '<?xml version="1.0"?><root><item key="renamed" index="first"/><item key="renamed" index="second"/><item key="renamed" index="second"/></root>';
        $object = dom::xml_to_object($xml, ['node_name_attr' => 'key', 'index_name_attr' => 'index']);
        $this->assertSame(['first' => '', 'second' => '', 0 => ''], $object->root->renamed);
    }
    public function testObjectConversionCanPreserveMixedParentText()
    {
        $xml = '<?xml version="1.0"?><root>before<item/>after</root>';
        $object = dom::xml_to_object($xml, ['preserve_parent_value' => '_text']);
        $this->assertSame('beforeafter', $object->root->_text);
        $this->assertSame('', $object->root->item);
    }

}
