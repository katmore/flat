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
            // flat's old copy printed //@cc_onvar x=1;, which comments out the code; the IE-only comment is dropped now
            ["//@cc_on\nvar x = 1;", 'var x=1;'],
            ['var s = "hello world"; var t = \'single\';', 'var s="hello world";var t=\'single\';'],
            ['var s = "a\"b";', 'var s="a\"b";'],
            ["var s = 'a\\\nb';", "var s='ab';"],
            ['var r = /a\\/b/g; var x = 10 / 2;', 'var r=/a\\/b/g;var x=10 / 2;'],
            ['var r = /"quoted"/;', 'var r=/"quoted"/;'],
            ["var r = /'quoted'/;", "var r=/'quoted'/;"],
            ["function f() { return /'quoted'/; }", "function f(){return/'quoted'/;}"],
            ["return /'a'/.test(s);", "return/'a'/.test(s);"],
            ["function f(){ return /'q'/; foo(); }", "function f(){return/'q'/;foo();}"],
            ["if (x) { return /\"q\"/; }\nbar();", "if(x){return/\"q\"/;}\nbar();"],
            ["var x = a + ++b;", 'var x=a+ ++b;'],
            ["var x = a - --b;", 'var x=a- --b;'],
            ["var\tx = 1;\r\n/**/x ++;\rfoo(x);", "var x=1;x++;foo(x);"],
            ["function f() {\n return (1 + 2);\n}\nf();", "function f(){return(1+2);}\nf();"],
            ["x\n++y;", "x\n++y;"],
            // an empty-looking comment followed by a carriage return, a control character, or another star
            ["var a=1;/**\r*/var b=2;", 'var a=1;var b=2;'],
            ["var a=1;/**\t*/var b=2;", 'var a=1;var b=2;'],
            ["var a=1;/***/var b=2;", 'var a=1;var b=2;'],
            // a division by a string literal is passed through as written (it is read like a regular expression)
            ['var result = 6 /"2"/ 3;', 'var result=6 /"2"/ 3;'],
            ["var result = 6 /'2'/ 3;", "var result=6 /'2'/ 3;"],
        ];
    }
    public function testPeekReturnsFalseAtTheEndOfTheInput()
    {
        // minify() always appends a newline, so the end of the input is only reachable by setting the state directly.
        $probe = new class extends minifier {
            public function __construct() {}
            public function peekAt($input, $index) { $this->input = $input; $this->len = strlen($input); $this->index = $index; return $this->peek(); }
        };
        $this->assertFalse($probe->peekAt('ab', 2));
        $this->assertSame('b', $probe->peekAt('ab', 1));
        $this->assertSame("\n", $probe->peekAt("a\rb", 1));
        $this->assertSame(' ', $probe->peekAt("a\tb", 1));
    }
    /** @dataProvider commentPrograms */
    public function testCommentsPreserveTokenBoundariesAndStringContents($source, $expected, $result)
    {
        $this->assertSame($expected, trim(minifier::minify($source)));
    }
    // The Docker JavaScript check also executes both versions and checks $result.
    public function commentPrograms()
    {
        return [
            ['var/**/result=1;', 'var result=1;', 1],
            ['function f(){return/**/1;} var result=f();', 'function f(){return 1;}var result=f();', 1],
            ['var x=1; var result=x+/**/+x;', 'var x=1;var result=x+ +x;', 2],
            ['var x=3; var result=x-/**/-x;', 'var x=3;var result=x- -x;', 6],
            ['var x=1; var result=x+/**/ /**/+x;', 'var x=1;var result=x+ +x;', 2],
            ['var result=1+/**/2;', 'var result=1+2;', 3],
            ['var result=1+/**/"x";', 'var result=1+"x";', '1x'],
            ['var/* note */result=1;', 'var result=1;', 1],
            ['var/**//**/result=1;', 'var result=1;', 1],
            ["function f(){return/*\n*/1;} var result=f()===undefined;", "function f(){return\n1;}var result=f()===undefined;", true],
            ["function f(){return/*\r\n*/1;} var result=f()===undefined;", "function f(){return\n1;}var result=f()===undefined;", true],
            ['var result="/**/";', 'var result="/**/";', '/**/'],
            ["var result='/**/';", "var result='/**/';", '/**/'],
            ['var result=1;/**/', 'var result=1;', 1],
            ['/**/var result=1;', 'var result=1;', 1],
        ];
    }
    /**
     * JavaScript after 2015 (template literals, arrow functions, ?. and ??, classes, ...): the minified program must give
     * the same result as the original. The Docker JavaScript check executes both, as for commentPrograms().
     * @dataProvider modernPrograms
     */
    public function testModernSyntaxKeepsItsMeaning($source, $expected, $result)
    {
        $this->assertSame($expected, trim(minifier::minify($source)));
    }
    public function modernPrograms()
    {
        return [
            // arrow
            ['var f = (a, b) => a + b; var g = x => x * 2; var result = [f(1, 2), g(4)];', 'var f=(a,b)=>a+b;var g=x=>x*2;var result=[f(1,2),g(4)];', [3, 8]],
            // template
            ['var n = 3; var result = `n is ${n} and ${n + 1}`;', 'var n=3;var result=`n is ${n} and ${n + 1}`;', 'n is 3 and 4'],
            // template nested
            ['var a = 1; var result = `x${`y${a}`}z`;', 'var a=1;var result=`x${`y${a}`}z`;', 'xy1z'],
            // template with slash
            ['var result = `a // not a comment /* nor this */ b`;', 'var result=`a // not a comment /* nor this */ b`;', 'a // not a comment /* nor this */ b'],
            // template multiline
            ['var result = `line1' . "\n" . 'line2   spaced`;', 'var result=`line1' . "\n" . 'line2   spaced`;', 'line1' . "\n" . 'line2   spaced'],
            // optional chaining
            ['var o = {a: {b: 5}}; var result = [o?.a?.b, o?.x?.y];', 'var o={a:{b:5}};var result=[o?.a?.b,o?.x?.y];', [5, null]],
            // nullish
            ['var u; var result = [u ?? 7, 0 ?? 9];', 'var u;var result=[u??7,0??9];', [7, 0]],
            // exponent
            ['var result = 2 ** 10;', 'var result=2**10;', 1024],
            // numeric separator
            ['var result = 1_000 + 1;', 'var result=1_000+1;', 1001],
            // spread
            ['var a = [1, 2]; var result = [...a, 3];', 'var a=[1,2];var result=[...a,3];', [1, 2, 3]],
            // destructuring
            ['var {x, y: [z]} = {x: 1, y: [2]}; var result = [x, z];', 'var{x,y:[z]}={x:1,y:[2]};var result=[x,z];', [1, 2]],
            // class
            ['class A { constructor(v) { this.v = v; } get d() { return this.v * 2; } static s() { return 9; } } var result = [new A(2).d, A.s()];', 'class A{constructor(v){this.v=v;}get d(){return this.v*2;}static s(){return 9;}}var result=[new A(2).d,A.s()];', [4, 9]],
            // async
            ['async function f() { return await Promise.resolve(1); } var result = typeof f;', 'async function f(){return await Promise.resolve(1);}var result=typeof f;', 'function'],
            // let const
            ['let a = 1; const b = 2; { let a = 5; } var result = a + b;', 'let a=1;const b=2;{let a=5;}var result=a+b;', 3],
            // regex after paren
            ['var s = "a/b"; var result = s.split(/\\/ /).length + (1) / 1;', 'var s="a/b";var result=s.split(/\\/ /).length+(1)/ 1;', 2],
            // regex class slash
            ['var result = /[/]/.test("/");', 'var result=/[/]/.test("/");', true],
            // regex with quote
            ['var result = /"[^"]*"/.exec(\'say "hi" now\')[0];', 'var result=/"[^"]*"/.exec(\'say "hi" now\')[0];', '"hi"'],
            // return newline ASI
            ['function f() { return' . "\n" . ' 5; } var result = f() === undefined;', 'function f(){return' . "\n" . '5;}var result=f()===undefined;', true],
            // plus plus
            ['var a = 1, b = 2; var result = a++ + ++b;', 'var a=1,b=2;var result=a++ + ++b;', 4],
            // minus minus
            ['var a = 5, b = 2; var result = a-- - --b;', 'var a=5,b=2;var result=a-- - --b;', 4],
            // plus space plus
            ['var a = 1, b = 2; var result = a + +b;', 'var a=1,b=2;var result=a+ +b;', 3],
            // label loop
            ['var result = 0; outer: for (var i = 0; i < 3; i++) { for (var j = 0; j < 3; j++) { if (j == 1) continue outer; result++; } }', 'var result=0;outer:for(var i=0;i<3;i++){for(var j=0;j<3;j++){if(j==1)continue outer;result++;}}', 3],
            // asi no semicolon
            ['var a = 1' . "\n" . 'var b = 2' . "\n" . 'var result = a + b', 'var a=1' . "\n" . 'var b=2' . "\n" . 'var result=a+b', 3],
            // keyword regex
            ['var result = (function(){ return /x/.test("x") })();', 'var result=(function(){return/x/.test("x")})();', true],
            // div after bracket
            ['var a = [4]; var result = a[0] / 2;', 'var a=[4];var result=a[0]/ 2;', 2],
            // regex after colon
            ['var o = {r: /x\\/y/}; var result = o.r.test("x/y");', 'var o={r:/x\\/y/};var result=o.r.test("x/y");', true],
            // generator
            ['function* g() { yield 1; yield 2; } var result = Array.from(g());', 'function*g(){yield 1;yield 2;}var result=Array.from(g());', [1, 2]],
            // for of
            ['var s = 0; for (const v of [1, 2, 3]) s += v; var result = s;', 'var s=0;for(const v of[1,2,3])s+=v;var result=s;', 6],
            // default params
            ['function f(a, b = a + 1) { return b; } var result = f(1);', 'function f(a,b=a+1){return b;}var result=f(1);', 2],
            // a real UTF-8 identifier
            ['var café = 3; var result = café + 1;', 'var café=3;var result=café+1;', 4],
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
        return [['/* never closed', 'Unclosed multiline comment'], ['var s = "never closed', 'Unclosed string'], ['var r = /never closed', 'Unclosed regex'], ['var r = /[ab', 'Unclosed character class']];
    }
}
