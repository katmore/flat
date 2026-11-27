<?php
namespace flat\test\Unit\Core\Md;
use PHPUnit\Framework\TestCase;
use flat\core\md\convert;
class ConvertTest extends TestCase
{
    public function testMarkdownStringInstanceAndFileProduceEquivalentHtml()
    {
        $markdown = "# Heading\n\nA **bold** paragraph with [link](https://example.com).\n\n- one\n- two";
        $expected = "<h1>Heading</h1>\n<p>A <strong>bold</strong> paragraph with <a href=\"https://example.com\">link</a>.</p>\n<ul>\n<li>one</li>\n<li>two</li>\n</ul>";
        $instance = new convert($markdown);
        $this->assertSame($expected, convert::string_to_html($markdown));
        $this->assertSame($expected, $instance->get_html());
        $this->assertSame($expected, (string) $instance);
        $this->assertSame('', (string) new convert(''));
        $path = tempnam(sys_get_temp_dir(), 'flat-md-');
        try {
            file_put_contents($path, $markdown);
            $this->assertSame($expected, (new convert\file($path))->get_html());
        } finally { unlink($path); }
    }
    public function testMarkdownExtraTablesAndFencedCode()
    {
        $html = convert::string_to_html("Name | Value\n--- | ---\na | b\n\n```php\n<code>\n```");
        $this->assertStringContainsString('<table>', $html);
        $this->assertStringContainsString('<td>b</td>', $html);
        $this->assertStringContainsString('&lt;code&gt;', $html);
    }
    /** @dataProvider untrustedMarkdown */
    public function testRawHtmlAndUnsafeAddressesAreNeutralisedByDefault($markdown, $mustNotContain, $mustContain)
    {
        $html = convert::string_to_html($markdown);
        $this->assertStringNotContainsString($mustNotContain, $html);
        $this->assertStringContainsString($mustContain, $html);
        $this->assertSame($html, (new convert($markdown))->get_html());
        $this->assertSame($html, convert::string_to_html($markdown, ['safe' => true]));
    }
    public function untrustedMarkdown()
    {
        return [
            'script' => ["<script>alert(1)</script>", '<script>', '&lt;script&gt;alert(1)&lt;/script&gt;'],
            'html block' => ["<div onclick=\"x()\">box</div>", '<div', '&lt;div onclick='],
            'inline html' => ['a <b onmouseover="x()">bold</b> b', '<b ', '&lt;b onmouseover='],
            'iframe' => ['<iframe src="http://evil.example"></iframe>', '<iframe', '&lt;iframe'],
            'javascript link' => ['[x](javascript:alert(1))', 'href="javascript:', '<a href="'],
            'data image' => ['![x](data:text/html;base64,AAAA)', 'src="data:', '<img src="'],
            'quote in an image address' => ['![a](x"onerror="alert(1))', 'onerror="alert', 'src="x&quot;onerror=&quot;alert(1)"'],
        ];
    }
    public function testOrdinaryMarkdownAndMarkdownExtraAreUnchangedBySafeMode()
    {
        $markdown = "# Title {#top .big}\n\n[ok](http://example.com/a \"t\") and ![i](http://example.com/i.png) and [rel](a/b)\n\n"
            . "Term\n: definition\n\nText[^1]\n\n[^1]: Note\n\n*[HTML]: Hyper Text\n\nHTML <http://example.com>";
        $html = convert::string_to_html($markdown);
        foreach (['id="top"', 'class="big"', '<a href="http://example.com/a" title="t">ok</a>', '<img src="http://example.com/i.png"',
                  '<a href="a/b">rel</a>', '<dt>Term</dt>', 'footnote', '<abbr title="Hyper Text">HTML</abbr>', 'href="http://example.com"'] as $part) {
            $this->assertStringContainsString($part, $html);
        }
    }
    public function testCharacterEntitiesAreShownLiterallyInSafeMode()
    {
        // Safe mode escapes entities along with raw HTML, so a typed &copy; is shown as the text "&copy;".
        $this->assertStringContainsString('<p>&amp;copy; &amp;amp;</p>', convert::string_to_html('&copy; &amp;'));
        $this->assertStringContainsString('<p>&copy; &amp;</p>', convert::string_to_html('&copy; &amp;', ['safe' => false]));
    }
    public function testTrustedTextCanLetRawHtmlThrough()
    {
        $markdown = "<div class=\"box\">raw</div>\n\n[x](javascript:void(0))";
        foreach ([convert::string_to_html($markdown, ['safe' => false]), (new convert($markdown, ['safe' => false]))->get_html()] as $html) {
            $this->assertStringContainsString('<div class="box">raw</div>', $html);
            $this->assertStringContainsString('href="javascript:void(0)"', $html);
        }
        $this->assertStringContainsString('<div class="box">', convert::string_to_html($markdown, ['safe' => 0]));
        $path = tempnam(sys_get_temp_dir(), 'flat-md-');
        try {
            file_put_contents($path, $markdown);
            $this->assertStringContainsString('&lt;div', (new convert\file($path))->get_html());
            $this->assertStringContainsString('<div class="box">', (new convert\file($path, ['safe' => false]))->get_html());
        } finally { unlink($path); }
    }
    public function testAnUnknownOptionIsRejected()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("unknown option 'trusted'");
        convert::string_to_html('text', ['trusted' => true]);
    }
    public function testNonStringSourceIsRejected()
    {
        $this->expectException(convert\exception\invalid_source::class);
        new convert([]);
    }
    public function testMissingFileRaisesInvalidSource()
    {
        $warning = null;
        set_error_handler(function ($severity, $message) use (&$warning) { $warning = $message; return true; });
        try {
            try { new convert\file(sys_get_temp_dir().'/flat-md-absent-'.bin2hex(random_bytes(8))); }
            catch (convert\exception\invalid_source $e) {
                $this->assertStringContainsString('could not be read', $e->getMessage());
                $this->assertStringContainsString('Failed to open stream', $warning);
                return;
            }
        } finally { restore_error_handler(); }
        $this->fail('Missing file must fail');
    }
}
