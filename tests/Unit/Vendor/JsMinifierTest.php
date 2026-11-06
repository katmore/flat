<?php
namespace flat\test\Unit\Vendor;
use PHPUnit\Framework\TestCase;
use flat\vendor\tedious\JShrink\minifier;
class JsMinifierTest extends TestCase
{
    /** @dataProvider programs */
    public function testWhitespaceCommentsStringsAndRegexArePreservedAsNeeded($source, $expected, $options = [])
    {
        $this->assertSame($expected, trim(minifier::minify($source, $options)));
    }
    public function programs()
    {
        return [
            ['', ''], ["var x = 1; // comment\nvar y = 2;", 'var x=1;var y=2;'],
            ["/* remove */ var x = 1;", 'var x=1;'],
            ["/*! license */\nvar x = 1;", "/*! license */\nvar x=1;"],
            ["/*! license */\nvar x = 1;", 'var x=1;', ['flaggedComments'=>false]],
            ["var x = 1;\n/*! license */\nvar y = 2;", "var x=1;\n/*! license */\nvar y=2;"],
            ["/*@cc_on @*/ var x = 1;", "/*@cc_on @*/\nvar x=1;"],
            ["//@cc_on\nvar x = 1;", '//@cc_onvar x=1;'],
            ['var s = "hello world"; var t = \'single\';', 'var s="hello world";var t=\'single\';'],
            ['var s = "a\"b";', 'var s="a\"b";'],
            ["var s = 'a\\\nb';", "var s='ab';"],
            ['var r = /a\\/b/g; var x = 10 / 2;', 'var r=/a\\/b/g;var x=10 / 2;'],
            ['var r = /"quoted"/;', 'var r=/"quoted"/;'],
            ["var r = /'quoted'/;", "var r=/'quoted'/;"],
            ["function f() { return /'quoted'/; }", "function f(){return /'quoted'/}"],
            ["var x = a + ++b;", 'var x=a+ ++b;'],
            ["var x = a - --b;", 'var x=a- --b;'],
            ["var\tx = 1;\r\n/**/x ++;\rfoo(x);", "var x=1;x++;foo(x);"],
            ["function f() {\n return (1 + 2);\n}\nf();", "function f(){return(1+2);}\nf();"],
            ["x\n++y;", "x\n++y;"],
        ];
    }
    /** @dataProvider brokenPrograms */
    public function testMalformedProgramsThrowAndRestoreOutputBuffer($source, $message)
    {
        $before = ob_get_level();
        try { minifier::minify($source); }
        catch (\RuntimeException $e) {
            $this->assertStringContainsString($message, $e->getMessage());
            $this->assertSame($before, ob_get_level());
            $this->assertSame('var ok=1;', trim(minifier::minify('var ok = 1;')));
            return;
        }
        $this->fail('Malformed JavaScript should fail');
    }
    public function brokenPrograms()
    {
        return [['/* never closed', 'Unclosed multiline comment'], ['var s = "never closed', 'Unclosed string'], ['var r = /never closed', 'Unclosed regex']];
    }
}
