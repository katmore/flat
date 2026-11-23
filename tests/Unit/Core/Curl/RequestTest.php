<?php
namespace flat\test\Unit\Core\Curl;
use flat\core\curl\request;
use PHPUnit\Framework\TestCase;
class FormStringValue { public function __toString() { return 'a&b'; } }
class FormMagicCallValue { public function __call($name, $arguments) { return 'not stringable'; } }
class RequestTest extends TestCase
{
    public function testUrlParsingPreservesComponentsAndNoBodyDefaults()
    {
        $url = 'https://example.com:8443/path?q=value#fragment';
        $request = new request($url);
        $this->assertSame(['scheme'=>'https','host'=>'example.com','port'=>8443,'path'=>'/path','query'=>'q=value','fragment'=>'fragment'], $request->url);
        $this->assertSame($url, $request->url_string);
        $this->assertNull($request->data); $this->assertNull($request->content_type);
        $this->assertSame('http', request::parse_url('http://example.com/')['scheme']);
    }
    /** @dataProvider invalidUrls */
    public function testInvalidUrls($url, $exception)
    {
        $this->expectException($exception); request::parse_url($url);
    }
    public function invalidUrls()
    {
        return [[123, \flat\core\curl\exception\bad_param::class], ['', \flat\core\curl\exception\missing_param::class],
            ['http://example.com:invalid', \flat\core\curl\exception\bad_param::class],
            ['file:///tmp/file', \flat\core\curl\exception\bad_param::class], ['http:/path', \flat\core\curl\exception\bad_param::class]];
    }
    /** @dataProvider missingSchemes */
    public function testMissingSchemeRaisesBadParamWithoutPhpNotices($url)
    {
        $this->expectException(\flat\core\curl\exception\bad_param::class);
        $this->expectExceptionMessage('missing scheme');
        new request($url);
    }
    public function missingSchemes() { return [['/relative'], ['example.com'], ['//example.com/path'], ['?q=value']]; }
    public function testFormKeysAndValuesSurviveEncoding()
    {
        $fields = ['a&b'=>'x=y&z', 'a=b'=>'a+b', 'a+b'=>'#value', 'é'=>'café'];
        foreach ([$fields, (object) $fields] as $input) {
            $request = new request('https://example.com/', $input);
            parse_str($request->data, $decoded);
            $this->assertSame($fields, $decoded);
            $this->assertSame(4, $request->field_count);
            $this->assertSame('a%26b=x%3Dy%26z&a%3Db=a%2Bb&a%2Bb=%23value&%C3%A9=caf%C3%A9', $request->data);
        }
    }
    public function testUrlencodedArrayObjectAndStringBodies()
    {
        foreach ([['name'=>'a b','flag'=>true], (object) ['name'=>'a b','flag'=>true], 'name=a+b&flag=1'] as $data) {
            $request = new request('https://example.com/', $data);
            $this->assertSame('application/x-www-form-urlencoded', $request->content_type);
            $this->assertSame('name=a+b&flag=1', $request->data);
            $this->assertSame(2, $request->field_count);
        }
    }
    public function testNonStringScalarInUrlencodedModeHasNoBody()
    {
        $this->assertNull((new request('https://example.com/', 42))->data);
    }
    /** @dataProvider invalidFormValues */
    public function testNestedFormValuesRaiseBadParamWithoutPhpWarnings($data)
    {
        $this->expectException(\flat\core\curl\exception\bad_param::class);
        $this->expectExceptionMessage('urlencoded form values must be scalar, null, or stringable');
        new request('https://example.com/', $data);
    }
    public function invalidFormValues()
    {
        return [
            [['items'=>['x']]],
            [(object) ['items'=>['x']]],
            ['items%5B%5D=x'],
            [['items'=>[]]],
            [['item'=>(object) ['key'=>'value']]],
            [['item'=>new FormMagicCallValue]],
        ];
    }
    public function testResourceFormValuesRaiseBadParamWithoutPhpWarnings()
    {
        $resource = fopen('php://memory', 'r+');
        try {
            $this->expectException(\flat\core\curl\exception\bad_param::class);
            $this->expectExceptionMessage('urlencoded form values must be scalar, null, or stringable');
            new request('https://example.com/', ['file'=>$resource]);
        } finally { fclose($resource); }
    }
    public function testFlatFormValuesKeepTheirEncoding()
    {
        $fields = ['zero'=>0,'false'=>false,'true'=>true,'null'=>null,'decimal'=>1.25,'text'=>'a b','object'=>new FormStringValue];
        foreach ([$fields, (object) $fields] as $data) {
            $request = new request('https://example.com/', $data);
            $this->assertSame('zero=0&false=&true=1&null=&decimal=1.25&text=a+b&object=a%26b', $request->data);
            $this->assertSame(7, $request->field_count);
            $this->assertSame('application/x-www-form-urlencoded', $request->content_type);
        }
    }
    public function testJsonBodyAndContentType()
    {
        $request = new request('https://example.com/', ['value'=>0], 'json');
        $this->assertSame('{"value":0}', $request->data);
        $this->assertSame('application/json', $request->content_type);
    }
    public function testJsonBodiesStillAcceptNestedData()
    {
        $request = new request('https://example.com/', ['items'=>['x'],'meta'=>(object) ['key'=>'value']], 'json');
        $this->assertSame('{"items":["x"],"meta":{"key":"value"}}', $request->data);
        $this->assertSame('application/json', $request->content_type);
    }
    public function testJsonEncodingFailureIsRejected()
    {
        $this->expectException(\flat\core\curl\exception\bad_param::class);
        new request('https://example.com/', ["invalid"=>"\xB1"], 'json');
    }
    /** @dataProvider jsonBodies */
    public function testJsonPreservesFalsyBodies($input, $expected)
    {
        $request = new request('https://example.com/', $input, 'json');
        $this->assertSame('application/json', $request->content_type);
        $this->assertSame($expected, $request->data);
    }
    public function jsonBodies()
    {
        return [[0, '0'], [false, 'false'], ['', '""'], ['0', '"0"'], [[], '[]']];
    }
    /** @dataProvider rawBodies */
    public function testRawModePreservesTheSuppliedBody($body)
    {
        $request = new request('https://example.com/', $body, 'none', 'text/plain');
        $this->assertSame('text/plain', $request->content_type);
        $this->assertSame($body, $request->data);
    }
    public function rawBodies()
    {
        return [['body'], ['0'], [''], ["binary\0body"], [0], [false]];
    }
    /** @dataProvider invalidRawContentTypes */
    public function testRawModeRequiresANonemptyStringContentType($contentType)
    {
        $this->expectException(\flat\core\curl\exception\bad_param::class);
        new request('https://example.com/', 'body', 'none', $contentType);
    }
    public function invalidRawContentTypes()
    {
        return [[null], [''], [false], [42], [[]]];
    }
    public function testNullRemainsTheNoBodyDefaultForJsonAndRawModes()
    {
        foreach (['json', 'none'] as $encoding) {
            $request = new request('https://example.com/', null, $encoding);
            $this->assertNull($request->data);
            $this->assertNull($request->content_type);
        }
    }
    public function testEmptyUrlencodedQueryStringIsRejected()
    {
        $this->expectException(\flat\core\curl\exception\bad_param::class);
        new request('https://example.com/', '&');
    }
}
