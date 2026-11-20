<?php
namespace flat\test\Unit\Core\Curl;
use flat\core\curl\request;
use PHPUnit\Framework\TestCase;
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
    public function testJsonBodyAndContentType()
    {
        $request = new request('https://example.com/', ['value'=>0], 'json');
        $this->assertSame('{"value":0}', $request->data);
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
