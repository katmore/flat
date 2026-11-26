<?php
namespace flat\test\Unit\Vendor;
use PHPUnit\Framework\TestCase;
use flat\vendor\erusev\src\Parsedown;
use flat\vendor\erusev\src\ParsedownExtra;

/**
 * Parsedown 1.8.0 and ParsedownExtra 0.9.0: the parts flat's own converter (ParsedownExtra, default options) does not
 * reach: the option setters, safe mode, strict mode, inline-only rendering, the hooks a subclass uses, and the
 * edge cases of the block parser. Objects are built inside each test, so loading is measured as coverage.
 */
class ParsedownApiTest extends TestCase
{
    public function testTheOptionSettersReturnTheParserAndChangeTheOutput()
    {
        $parser = new Parsedown;
        $this->assertSame($parser, $parser->setBreaksEnabled(true));
        $this->assertSame("<p>a<br />\nb</p>", $parser->text("a\nb"));
        $this->assertSame($parser, $parser->setBreaksEnabled(false));
        $this->assertSame("<p>a\nb</p>", $parser->text("a\nb"));

        $this->assertStringContainsString('<a href="http://example.com">', (new Parsedown)->text('see http://example.com now'));
        $this->assertStringNotContainsString('<a ', (new Parsedown)->setUrlsLinked(false)->text('see http://example.com now'));

        $this->assertSame('<p>&lt;b&gt;x&lt;/b&gt;</p>', (new Parsedown)->setMarkupEscaped(true)->text('<b>x</b>'));
        $this->assertSame('<p><b>x</b></p>', (new Parsedown)->text('<b>x</b>'));
        $this->assertSame('<p><em>x</em></p>', (new Parsedown)->line('*x*') === '<em>x</em>' ? '<p><em>x</em></p>' : 'no');
        $this->assertInstanceOf(Parsedown::class, (new Parsedown)->setSafeMode(true));
        $this->assertInstanceOf(Parsedown::class, (new Parsedown)->setStrictMode(true));
    }

    public function testLineRendersInlineMarkdownOnly()
    {
        $this->assertSame('<em>x</em> and <strong>y</strong>', (new Parsedown)->line('*x* and **y**'));
        $this->assertSame('a<br />' . "\n" . 'b', (new Parsedown)->setBreaksEnabled(true)->line("a\nb"));
        // Parsedown::line() takes the inline types that must not nest: a link cannot sit inside a link.
        $this->assertSame('<a href="y">a [b](x) c</a>', (new Parsedown)->line('[a [b](x) c](y)', array('Link' => true)));
    }

    public function testALinkInsideALinkIsNotNested()
    {
        $html = (new Parsedown)->text('[a [b](x) c](y)');
        $this->assertSame(0, preg_match('#<a [^>]*><a #', $html));
    }

    /** @dataProvider unsafeMarkdown */
    public function testSafeModeNeutralisesActiveContent($markdown, $mustNotContain, $mustContain)
    {
        $html = (new Parsedown)->setSafeMode(true)->text($markdown);
        $this->assertStringNotContainsString($mustNotContain, $html);
        $this->assertStringContainsString($mustContain, $html);
        // The same input passes through without safe mode, so the test pins what safe mode does.
        $this->assertStringContainsString($mustNotContain, (new Parsedown)->text($markdown));
    }
    public function unsafeMarkdown()
    {
        return [
            'raw script' => ["<script>alert(1)</script>", '<script>', '&lt;script&gt;'],
            'javascript link' => ['[x](javascript:alert(1))', 'href="javascript:', 'href="javascript%3Aalert(1)"'],
            'mixed-case javascript link' => ['[x](JaVaScRiPt:alert(1))', 'href="JaVaScRiPt:', '<a href="'],
            'data image' => ['![x](data:text/html;base64,AAAA)', 'src="data:', '<img src="'],
            'event attribute through inline html' => ['<a href="http://e.com" onclick="alert(1)">x</a>', '<a href="http://e.com" onclick', '&lt;a href='],
            'vbscript link' => ['[x](vbscript:msgbox(1))', 'href="vbscript:', '<a href="'],
        ];
    }

    public function testSafeModeKeepsOrdinaryLinksAndImages()
    {
        $html = (new Parsedown)->setSafeMode(true)->text("[ok](http://example.com/a) ![i](http://example.com/i.png)\n\n[rel](relative/path) [mail](mailto:a@example.com)");
        $this->assertStringContainsString('<a href="http://example.com/a">ok</a>', $html);
        $this->assertStringContainsString('<img src="http://example.com/i.png"', $html);
        $this->assertStringContainsString('<a href="relative/path">rel</a>', $html);
        $this->assertStringContainsString('<a href="mailto:a@example.com">mail</a>', $html);
    }

    public function testSafeModeKeepsParsedownExtraIdAndClassAttributesAndDropsRawHtml()
    {
        $html = (new ParsedownExtra)->setSafeMode(true)->text("# Title {#idx .cls}\n\n<b onclick=\"alert(1)\">x</b>");
        $this->assertStringContainsString('id="idx"', $html);
        $this->assertStringContainsString('class="cls"', $html);
        $this->assertStringNotContainsString('<b onclick', $html);
    }

    public function testStrictModeRequiresASpaceAfterTheHeadingMarker()
    {
        $this->assertStringContainsString('<h1>Title</h1>', (new Parsedown)->text('#Title'));
        $this->assertStringContainsString('<p>#Title</p>', (new Parsedown)->setStrictMode(true)->text('#Title'));
        $this->assertStringContainsString('<h1>Title</h1>', (new Parsedown)->setStrictMode(true)->text('# Title'));
    }

    public function testPlainParsedownHandlesHtmlBlocks()
    {
        $html = (new Parsedown)->text("<div>\n*not emphasised*\n\ncontinues\n</div>\n\nafter");
        $this->assertStringContainsString("<div>\n*not emphasised*", $html);
        $this->assertStringContainsString('<p>after</p>', $html);
        $this->assertStringContainsString('<hr />', (new Parsedown)->text('<hr />'));
        $this->assertStringContainsString("<!-- a\nb -->", (new Parsedown)->text("<!-- a\nb -->"));
    }

    public function testBlockParserEdgeCases()
    {
        $p = function () { return new Parsedown; };
        // a fence whose info string holds a backtick is not a fence
        $this->assertStringNotContainsString('<pre>', $p()->text("``` a`b\ncode"));
        // an ordered list that does not start at 1 cannot interrupt a paragraph
        $this->assertSame("<p>a\n2. b</p>", $p()->text("a\n2. b"));
        $this->assertStringContainsString('<ol start="2">', $p()->text("a\n\n2. b"));
        // a list item whose content is indented five spaces or more starts with a code block
        $this->assertStringContainsString('<code>', $p()->text("-     code"));
        // continuation lines, blank lines and nested lists inside a list
        $this->assertStringContainsString("<li>a\nb</li>", $p()->text("- a\n  b"));
        $this->assertStringContainsString('<p>c</p>', $p()->text("- a\n\n  c"));
        $this->assertStringContainsString('<ul>', $p()->text("- a\n\n\n- b"));
        $this->assertStringContainsString('<li>', $p()->text("-\n- b"));
        // an empty list item followed by a blank line ends the list; a lazy continuation line joins the item
        $this->assertStringContainsString('<p>foo</p>', $p()->text("-\n\nfoo"));
        $this->assertStringContainsString("<li>a\nb</li>", $p()->text("- a\nb"));
        // a table whose separator row has a different number of cells is not a table
        $this->assertStringNotContainsString('<table>', $p()->text("a | b | c\n--- | ---\n1 | 2 | 3"));
        // a lone marker character
        $this->assertSame('<p>~</p>', $p()->text('~'));
        $this->assertSame('<p>*</p>', (new Parsedown)->line('*') === '*' ? '<p>*</p>' : 'no');
    }

    public function testSubclassHooksStillWork()
    {
        $parser = new class extends Parsedown {
            public function viaLines(array $lines) { return $this->lines($lines); }
            public function viaUnmarkedText($text) { return $this->unmarkedText($text); }
            public function viaHandle(array $element) { return $this->handleElementRecursive($element); }
            public function viaHandleMany(array $elements) { return $this->handleElementsRecursive($elements); }
            public function viaDepthFirst(array $element, $closure) { return $this->elementApplyRecursiveDepthFirst($closure, $element); }
            public function viaDepthFirstMany(array $elements, $closure) { return $this->elementsApplyRecursiveDepthFirst($closure, $elements); }
            public function viaMarkup(array $element) { return $this->element($element); }
        };
        $lines = $parser->viaLines(array('a', '', 'b'));
        $this->assertStringContainsString('<p>a</p>', $lines);
        $this->assertStringContainsString('<p>b</p>', $lines);
        $this->assertSame('plain &amp; text', $parser->viaUnmarkedText('plain & text'));

        // an element whose handler names a method, and one whose handler is an array
        $legacy = array('name' => 'p', 'handler' => 'line', 'text' => '*x*');
        $this->assertSame('<p><em>x</em></p>', $parser->viaMarkup($legacy));
        $array = array('name' => 'p', 'handler' => array('function' => 'line', 'argument' => '**y**', 'destination' => 'rawHtml'));
        $this->assertSame('<p><strong>y</strong></p>', $parser->viaMarkup($array));
        $elements = array('name' => 'ul', 'elements' => array(array('name' => 'li', 'handler' => 'line', 'text' => '*z*')));
        $handled = $parser->viaHandle($elements);
        $this->assertSame('<em>z</em>', $handled['elements'][0]['rawHtml']);
        $single = array('name' => 'blockquote', 'element' => array('name' => 'p', 'handler' => 'line', 'text' => '*q*'));
        $this->assertSame('<em>q</em>', $parser->viaHandle($single)['element']['rawHtml']);
        $this->assertCount(1, $parser->viaHandleMany(array($single)));

        // a block component that returns the old 'markup' key, and a handler whose result is another handler
        $legacyBlock = new class extends Parsedown {
            public function __construct() { $this->BlockTypes['@'] = array('Legacy'); }
            protected function blockLegacy($Line) { return array('markup' => '<legacy>' . substr($Line['text'], 1) . '</legacy>'); }
            protected function chain($argument, $nonNestables) { return array('function' => 'line', 'argument' => $argument, 'destination' => 'rawHtml'); }
            public function viaChain() { return $this->element(array('name' => 'p', 'handler' => array('function' => 'chain', 'argument' => '*c*', 'destination' => 'handler'))); }
        };
        $this->assertSame('<legacy>text</legacy>', $legacyBlock->text('@text'));
        $this->assertSame('<p><em>c</em></p>', $legacyBlock->viaChain());

        // safe mode drops badly named and on* attributes from elements a subclass builds
        $safe = new class extends Parsedown {
            public function __construct() { $this->setSafeMode(true); }
            public function viaElement(array $element) { return $this->element($element); }
        };
        $this->assertSame('<a href="http://e.com">x</a>', $safe->viaElement(array('name' => 'a', 'text' => 'x', 'attributes' => array('href' => 'http://e.com', 'onclick' => 'evil()', 'bad name' => 'y'))));

        $upper = function (array $element) { if (isset($element['name'])) $element['name'] = strtoupper($element['name']); return $element; };
        $deep = $parser->viaDepthFirst($elements, $upper);
        $this->assertSame('UL', $deep['name']);
        $this->assertSame('LI', $deep['elements'][0]['name']);
        $this->assertCount(1, $parser->viaDepthFirstMany(array($single), $upper));
        // the single-element child ('element') is walked too (upstream called the plural method and raised a TypeError)
        $this->assertSame('P', $parser->viaDepthFirst($single, $upper)['element']['name']);
    }

    public function testDefinitionListsWithBlankLines()
    {
        $html = (new ParsedownExtra)->text("Term\n: first\n\n    second paragraph\n\n: another");
        $this->assertStringContainsString('<dt>Term</dt>', $html);
        $this->assertStringContainsString('second paragraph', $html);
        $this->assertStringContainsString('another', $html);
        $this->assertStringContainsString('<dd>', (new ParsedownExtra)->text("Term\n\n: after a blank line"));
        $this->assertStringContainsString('<abbr title="Hyper Text">HTML</abbr>', (new ParsedownExtra)->text("*[HTML]: Hyper Text\n\n> HTML in a quote\n\n- HTML in a list"));
    }

    public function testBlockMarkupContinuesAcrossBlankLines()
    {
        $html = (new Parsedown)->text("<div>\none\n\n\ntwo\n</div>");
        $this->assertStringContainsString('<p>two</p>', $html);
    }
}
