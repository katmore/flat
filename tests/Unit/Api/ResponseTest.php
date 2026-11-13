<?php
namespace flat\test\Unit\Api;
use PHPUnit\Framework\TestCase;
use flat\api\response;
function unknown_response_exception_fixture($value) { return new response\exception($value); }
class HeaderResponse extends response
{
    public function add(\flat\api\response_header $header) { $this->_add_header($header); }
}
class ResponseTest extends TestCase
{
    private $savedEvent;
    protected function setUp(): void
    {
        $p = new \ReflectionProperty(\flat\event::class,'_event'); $p->setAccessible(true);
        $this->savedEvent = $p->getValue(); $p->setValue(null,null);
    }
    protected function tearDown(): void
    {
        $p = new \ReflectionProperty(\flat\event::class,'_event'); $p->setAccessible(true); $p->setValue(null,$this->savedEvent);
    }
    public function testSuccessAndExplicitStatusMapping()
    {
        $this->assertSame(200,(new response\ok)->get_status()->get_code());
        $this->assertSame('value',(new response\ok(['key'=>'value']))->key);
        $this->assertSame('value',(new response\ok((object) ['key'=>'value']))->key);
        $this->assertSame([1,2],(new response\ok([1,2]))->data);
        $this->assertSame('scalar',(new response\ok('scalar'))->data);
        $this->assertNull((new response\ok(0))->data);
        $status = new \flat\api\status\forbidden;
        $this->assertSame($status,(new response\map($status))->get_status());
        $this->assertSame('value',(new response\map($status,['key'=>'value']))->key);
        $this->assertSame('value',(new response\map($status,(object) ['key'=>'value']))->key);
        $this->assertSame('scalar',(new response\map($status,'scalar'))->data);
        $moved = new response\moved_permanently('/next');
        $this->assertSame('/next',$moved->location); $this->assertSame(301,$moved->get_status()->get_code());
        $this->assertSame(405,(new response\no_interface)->get_status()->get_code());
    }
    /** @dataProvider errorResponses */
    public function testErrorMessagesDataAndStatusCodes($class,$code)
    {
        $error = new $class('failure',(object) ['key'=>'value']);
        $this->assertSame('failure',$error->message);
        $this->assertSame($code,$error->get_status()->get_code());
        $this->assertSame('value',$error->data->key);
        $inferred = new $class;
        $this->assertStringContainsString('by',strtolower($inferred->message));
        $duplicate = new $class('outer',(object) ['message'=>'inner']);
        $this->assertSame('inner',$duplicate->data->message);
    }
    public function errorResponses()
    {
        return [[response\error::class,500], [response\forbidden::class,403], [response\method_not_allowed::class,405],
            [response\not_found::class,404], [response\bad_request::class,400], [response\unauthorized::class,401]];
    }
    public function testNotFoundPreservesFalsyAndAlreadyMessagedPayloads()
    {
        foreach ([0,false,'',[],['message'=>'inner']] as $data) {
            $this->assertSame($data,(new response\not_found('missing',$data))->data);
        }
        $this->assertSame('missing',(new response\not_found('missing',['key'=>'value']))->data->message);
        $this->assertNull((new response\not_found('',null,999))->message);
        $this->assertNull((new response\unauthorized('',null,999))->message);
    }
    public function testBadRequestAddsMessageToArrayOrObjectWithoutOverwritingIt()
    {
        $array = new response\bad_request('bad',['key'=>'value']);
        $this->assertSame(['key'=>'value','message'=>'bad'],$array->data);
        $object = new response\bad_request('bad',(object) ['key'=>'value']);
        $this->assertSame('bad',$object->data->message);
        $this->assertSame('scalar',(new response\bad_request('bad','scalar'))->data);
    }
    public function testResponseExceptionsPreserveTheResponseAndStatus()
    {
        $response = new response\error('failure');
        $exception = new response\exception($response);
        $this->assertSame($response,$exception->get_response());
        $this->assertStringContainsString('500 Internal Server Error',$exception->getMessage());
        $this->assertStringContainsString('failure',$exception->getMessage());
        $scalar = new response\exception('scalar error');
        $this->assertSame('scalar error',$scalar->get_response()->message);
        $this->assertInstanceOf(response\ok::class,(new response\exception(new response\ok))->get_response());
    }
    public function testUnknownErrorWithoutClassCallerGetsGenericMessage()
    {
        $exception = unknown_response_exception_fixture(null);
        $this->assertSame('an unknown error has occurred',$exception->get_response()->message);
        $this->assertSame(500,$exception->get_response()->get_status()->get_code());
    }
    public function testUnknownErrorWithClassCallerNamesTheCallingClass()
    {
        $exception = new response\exception(null);
        $this->assertSame('an unknown error has occurred by '.self::class,$exception->get_response()->message);
        $this->assertSame(500,$exception->get_response()->get_status()->get_code());
    }
    public function testMissingStatusReportsTheResponseAndInvalidValue()
    {
        $response = new response;
        try { $response->get_status(); }
        catch (response\invalid_status $e) {
            $this->assertSame($response,$e->get_response()); $this->assertNull($e->get_status_value()); return;
        }
        $this->fail('Response without status must fail');
    }
    public function testHeadersExposeFieldAndValueAndReplaceDuplicateFields()
    {
        $first = new \flat\api\response_header('X-Example','one');
        $this->assertSame('X-Example',$first->get_field());
        $this->assertSame('one',$first->get_value());
        $this->assertSame('X-Example: one',$first->get_header_string());
        $bare = new \flat\api\response_header('X-Bare');
        $this->assertSame('X-Bare',$bare->get_header_string()); $this->assertSame('',$bare->get_value());
        $response = new HeaderResponse;
        $this->assertSame([],$response->get_headers());
        $this->assertSame([],$response->get_header_strings());
        $response->add($first); $response->add(new \flat\api\response_header('X-Example','two'));
        $this->assertCount(1,$response->get_headers());
        $this->assertSame('two',$response->get_headers()['X-Example']->get_value());
        $this->assertSame(['X-Example: two'],$response->get_header_strings());
        $this->assertSame('etag: "local-tag"',(new \flat\api\response_header\etag('"local-tag"'))->get_header_string());
    }
    public function testAnyMethodFallbackResourcesReturnTheirStatusAndMessage()
    {
        $input = new \flat\input\map;
        $response = (new response\forbidden\any_method)->any_method($input);
        $this->assertSame(403,$response->get_status()->get_code());
        $this->assertSame('forbidden resource',$response->message);
        $response = (new response\not_found\any_method)->any_method($input);
        $this->assertSame(404,$response->get_status()->get_code());
        $this->assertSame('resource not found',$response->message);
    }
    public function testEmptyHeaderFieldThrowsItsDeclaredLegacyException()
    {
        $this->expectException(\flat\api\header\exception\missing_request_field::class);
        $this->expectExceptionMessage('missing request field');
        new \flat\api\response_header('');
    }
}
