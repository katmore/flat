<?php
namespace flat\test\Unit\Vendor;
use PHPUnit\Framework\TestCase;
use flat\vendor\erusev\parsedown\extra;
require_once __DIR__.'/../../Support/PhpSubprocess.php';
class MarkdownTest extends TestCase
{
    use \flat\test\Support\PhpSubprocess;
    public function testQuoteHandlerRejectsANonmatchingExtensionMarker()
    {
        // Define the extension during the test so parser loading is measured,
        // rather than happening while PHPUnit discovers test classes.
        $parser = new class extends extra {
            public function __construct() { parent::__construct(); $this->BlockTypes['x'] = ['Quote']; }
        };
        $this->assertSame('<p>x ordinary paragraph</p>',$parser->text('x ordinary paragraph'));
    }
    public function testBothBundledLoadersWorkInAFreshPhpRuntime()
    {
        list($status,$out,$err) = $this->subprocess('echo (new \\flat\\vendor\\erusev\\src\\Parsedown)->text("**base**"); echo (new \\flat\\vendor\\erusev\\src\\ParsedownExtra)->text("**extra**");');
        $this->assertSame(0,$status); $this->assertSame('',$err);
        $this->assertSame('<p><strong>base</strong></p><p><strong>extra</strong></p>',$out);
    }
    /** @dataProvider documents */
    public function testSupportedBlockAndInlineSyntax($markdown,array $fragments)
    {
        $html = (new extra)->text($markdown);
        foreach ($fragments as $fragment) $this->assertStringContainsString($fragment,$html);
    }
    public function documents()
    {
        return [
            ["Heading\n=======\n\nSubheading\n----------", ['<h1>Heading</h1>','<h2>Subheading</h2>']],
            ["# Heading {#identity .special}\n\n[link](https://example.com){#target .external}", ['id="identity"','class="special"','id="target"']],
            ["    code <tag>\n\n    more code", ['<pre><code>code &lt;tag&gt;','more code</code></pre>']],
            ["```php\n\$x = '<tag>';\n```", ['class="language-php"','&lt;tag&gt;']],
            ["~~~\nplain\n\nmore\n~~~", ['<pre><code>plain','more</code></pre>']],
            ["1. first\n2. second\n\n   continuation\n\n- outer\n  - nested\n\nparagraph", ['<ol>','<li>first</li>','continuation','<ul>','nested']],
            ["> quoted\n> second\n\n> other\n\nnormal", ['<blockquote>','quoted','other','<p>normal</p>']],
            ["before\n\n---\n\nafter", ['<hr />','<p>after</p>']],
            ["<!-- comment\nmultiline -->\n\n<div>\ncontent\n</div>", ['<!-- comment','multiline -->','<div>']],
            ["<div markdown=\"1\">\n**bold**\n</div>", ['<div>','<strong>bold</strong>']],
            ["Term\n: definition\n\nOther\n: another\n  continuation", ['<dl>','<dt>Term</dt>','<dd>definition</dd>','continuation']],
            ["*[HTML]: Hyper Text Markup Language\n\nHTML is a format.", ['<abbr title="Hyper Text Markup Language">HTML</abbr>']],
            ["First[^note] and repeated[^note].\n\n[^note]: Footnote text\n\n    More detail.", ['fnref1:note','Footnote text','More detail.','footnotes']],
            ["[example][ref]\n\n[ref]: https://example.com \"title\"", ['href="https://example.com"','title="title"']],
            ["![image](https://example.com/image.png \"title\")", ['<img src="https://example.com/image.png"','alt="image"']],
            ["<user@example.com> <https://example.com> https://example.com/path", ['href="mailto:user@example.com"','href="https://example.com"','href="https://example.com/path"']],
            ['**strong** *em* __strong__ _em_ ~~deleted~~ `code` & < > \\*literal\\*', ['<strong>strong</strong>','<em>em</em>','<del>deleted</del>','<code>code</code>','&amp;','*literal*']],
            ["left | center | right\n:--- | :---: | ---:\na | b | c\n\nx | y | z", ['text-align: left','text-align: center','text-align: right','<td style="text-align: right;">c</td>']],
            ["<span class=\"inline\">text</span> &amp;\n\n<!--inline-->", ['<span class="inline">text</span>','&amp;','<!--inline-->']],
            ["paragraph\ncontinued\n\nsecond\n\tindented", ['paragraph','continued','second','indented']],
            ["## Second\n\n#### Fourth\n\n####### Too deep", ['<h2>Second</h2>','<h4>Fourth</h4>','<p>####### Too deep</p>']],
            ["```\ncode\n```\n\nfollowing", ['<pre><code>code</code></pre>','<p>following</p>']],
            ["- first\n\n  second paragraph\n\n- last\n\nafter", ['<p>first</p>','second paragraph','last','<p>after</p>']],
            ["> quote\ncontinued\n\nnormal", ['<blockquote>','continued','<p>normal</p>']],
            ["<hr>\n\n<div>closed</div>\n\n<div>\n<div>\nnested\n</div>\n\nouter\n</div>\n\nafter", ['<hr>','<div>closed</div>','nested','outer','<p>after</p>']],
            ["<hr> trailing\n\n<widget />\n\n<widget /> trailing", ['trailing','<widget />']],
            ['Unmatched * and ** and _ and !broken ![missing] [unclosed [missing] <broken \\q', ['Unmatched','!broken','![missing]','[unclosed','[missing]','&lt;broken','\\q']],
            ["[shortcut]\n\n[shortcut]: https://example.com", ['href="https://example.com"']],
            ['before <!--inline comment--> after', ['<!--inline comment-->','before','after']],
            ["| left | right |\n| --- | --- |\n| a | b |", ['<th>left</th>','<td>b</td>']],
            ["Term\n: first\n: second\n\nOther\n: third", ['<dd>first</dd>','<dd>second</dd>','<dd>third</dd>']],
            ["Heading {#setext}\n==================", ['id="setext"']],
            ["Two[^second] then one[^first].\n\n[^first]: First note\n    continued\n[^second]: Second note\n\n[^unused]: not referenced", ['First note','continued','Second note','fnref1:first']],
            ["Text[^block].\n\n[^block]:\n\n    > quoted footnote", ['quoted footnote','footnotes']],
            ["<div markdown=\"1\">\n<section markdown=\"1\">\n**nested**\n</section>\n</div>", ['<strong>nested</strong>']],
            ["    code\nplain", ['<pre><code>code</code></pre>','<p>plain</p>']],
            ["``` invalid fence text\n\n#", ['invalid fence text','#']],
            ['*', ['<p>*</p>']],
            ['~', ['<p>~</p>']],
            ['~unclosed', ['<p>~unclosed</p>']],
            ['http:///', ['http:///']],
            ['`unclosed', ['`unclosed']],
            ['[unclosed', ['[unclosed']],
            ['http:/// and x:/ before https://example.com', ['http:///','x:/ before','href="https://example.com"']],
            ["a || b\n--- || ---\nx || y\nplain", ['<th>a</th>','<td>y</td>','plain']],
            ["- item\n[ref]: https://example.com\n  [ref]", ['href="https://example.com"','item']],
            ["Text[^missing] and note[^a].\n\n[^a]: note\n\nplain", ['[^missing]','<p>plain</p>']],
            ["---\n: not a definition", ['<hr />','not a definition']],
        ];
    }
    public function testConfigurationAndRepeatedParserInstances()
    {
        $parser = new extra;
        $this->assertSame($parser,$parser->setBreaksEnabled(true));
        $this->assertStringContainsString('<br />',$parser->text("one\ntwo"));
        $this->assertSame($parser,$parser->setMarkupEscaped(true));
        $this->assertStringContainsString('&lt;b&gt;',$parser->text('<b>text</b>'));
        $this->assertSame($parser,$parser->setUrlsLinked(false));
        $this->assertStringNotContainsString('<a ',$parser->text('https://example.com'));
        $this->assertSame($parser->text('plain'),$parser->parse('plain'));
        $this->assertSame(extra::instance('flat-test'),extra::instance('flat-test'));
        $this->assertNotSame(extra::instance('flat-test'),extra::instance('flat-test-other'));
    }
}
