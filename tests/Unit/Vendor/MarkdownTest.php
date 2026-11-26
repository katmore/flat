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
    /**
     * Parsedown 1.8.0 and ParsedownExtra 0.9.0 (before: 1.5.0 and 0.6.x). Each row is Markdown, then a fragment of the
     * HTML it now produces; the notes say what the old parser did.
     * @dataProvider changedRendering
     */
    public function testRenderingThatChangedWithParsedown18($markdown, $fragment)
    {
        $this->assertStringContainsString($fragment, (new extra)->text($markdown));
    }
    public function changedRendering()
    {
        return [
            'an ordered list keeps its first number (old: always 1)' => ["3. three\n4. four", '<ol start="3">'],
            'a year at the start of a line starts a list there (old: from 1)' => ["2015. A great year", '<ol start="2015">'],
            'a different bullet starts a new list (old: one list)' => ["* a\n+ b", "</ul>\n<ul>"],
            'a blank line between quotes makes two quotes (old: one)' => ["> a\n\n> b", "</blockquote>\n<blockquote>"],
            'every item of a loose list is a paragraph (old: the last item was bare)' => ["- a\n\n- b", "<li>\n<p>b</p>"],
            'a fenced block takes attributes (old: not recognised)' => ["```php {.hl}\ncode\n```", '<pre><code class="language-php">code</code></pre>'],
            'quotes in text are no longer entity-encoded (old: &quot;)' => ['say "hi"', 'say "hi"'],
        ];
    }
    public function testAnImageAddressCannotBreakOutOfItsAttribute()
    {
        // Parsedown 1.5.0 printed <img src="x"onerror="alert(1)" ...>: an attribute injected through the address.
        $html = (new extra)->text('![a](x"onerror="alert(1))');
        $this->assertStringContainsString('src="x&quot;onerror=&quot;alert(1)"', $html);
        $this->assertStringNotContainsString('onerror="alert', $html);
    }
    public function documents()
    {
        return [
            ["Heading\n=======\n\nSubheading\n----------", ['<h1>Heading</h1>','<h2>Subheading</h2>']],
            ["# Heading {#identity .special}\n\n[link](https://example.com){#target .external}", ['id="identity"','class="special"','id="target"']],
            ["    code <tag>\n\n    more code", ['<pre><code>code &lt;tag&gt;','more code</code></pre>']],
            ["```php\n\$x = '<tag>';\n```", ['class="language-php"','&lt;tag&gt;']],
            ["~~~\nplain\n\nmore\n~~~", ['<pre><code>plain','more</code></pre>']],
            ["1. first\n2. second\n\n   continuation\n\n- outer\n  - nested\n\nparagraph", ['<ol>','<p>first</p>','continuation','<ul>','nested']],
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
            ["    code\nplain", ['<pre><code>code</code></pre>','<p>plain</p>']],
            ["``` invalid fence text\n\n#", ['<pre><code class="language-invalid">','#']],
            ['*', ['<ul>','<li>','</li>']],
            ['~', ['<p>~</p>']],
            ['~unclosed', ['<p>~unclosed</p>']],
            ['http:///', ['http:///']],
            ['`unclosed', ['`unclosed']],
            ['[unclosed', ['[unclosed']],
            ['http:/// and x:/ before https://example.com', ['http:///','x:/ before','href="https://example.com"']],
            ["a || b\n--- || ---\nx || y\nplain", ['<p>a || b','plain']],
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
    /** @dataProvider libxmlModes */
    public function testHtmlMarkupPreservesCallerLibxmlStateAndBufferedErrors($internal)
    {
        $previous = libxml_use_internal_errors($internal);
        try {
            if ($internal) (new \DOMDocument)->loadXML('<prior>');
            $before = libxml_get_errors();
            $html = (new extra)->text('<div markdown="1">**bold**</div>');
            $this->assertStringContainsString('<strong>bold</strong>', $html);
            $this->assertSame($internal, libxml_use_internal_errors());
            $this->assertEquals($before, libxml_get_errors());
        } finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }
    }
    public function libxmlModes() { return [[false], [true]]; }
    public function testNestedHtml5MarkupReportsParserWarningsAndStillRenders()
    {
        $previous = libxml_use_internal_errors(false);
        $warnings = [];
        $handler = null;
        $handler = set_error_handler(function ($level, $message, $file, $line) use (&$warnings, &$handler) {
            if ($level === E_WARNING && strpos($message, 'DOMDocument::loadHTML(): Tag section invalid') === 0) {
                $warnings[] = $message;
                return true;
            }
            return $handler ? $handler($level, $message, $file, $line) : false;
        });
        try {
            $html = (new extra)->text("<div markdown=\"1\">\n<section markdown=\"1\">\n**nested**\n</section>\n</div>");
            $this->assertStringContainsString('<strong>nested</strong>', $html);
            $this->assertSame([
                'DOMDocument::loadHTML(): Tag section invalid in Entity, line: 2',
                'DOMDocument::loadHTML(): Tag section invalid in Entity, line: 1',
            ], $warnings);
            $this->assertFalse(libxml_use_internal_errors());
        } finally { restore_error_handler(); libxml_use_internal_errors($previous); }
    }
}
