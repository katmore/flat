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
                $this->assertStringContainsString('failed to open stream', $warning);
                return;
            }
        } finally { restore_error_handler(); }
        $this->fail('Missing file must fail');
    }
}
