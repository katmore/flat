<?php
namespace flat\test\Unit\Api;
use PHPUnit\Framework\TestCase;
use flat\core\controller\api;
use flat\api\response;
class GetApi extends api implements \flat\api\method\GET
{
    public static $handler;
    public function GET_method(\flat\input $input) { return self::$handler ? call_user_func(self::$handler,$input) : new response\ok((array) $input); }
    public function requestMethod() { return $this->_get_request_method(); }
    public function isHead() { return $this->_is_HEAD_request(); }
}
class AnyApi extends GetApi implements \flat\api\method\any
{
    public static $any;
    public function any_method(\flat\input $input) { return self::$any ? call_user_func(self::$any,$input) : null; }
}
class NoApi extends api {}
class ControllerTest extends TestCase
{
    private $saved = [];
    protected function setUp(): void
    {
        foreach ((new \ReflectionClass(api::class))->getProperties(\ReflectionProperty::IS_STATIC) as $p) {
            $p->setAccessible(true); $this->saved[$p->getName()] = $p->getValue();
            $p->setValue(null, $p->getName() === '_media_type' ? '' : null);
        }
        GetApi::$handler = null; AnyApi::$any = null;
    }
    protected function tearDown(): void
    {
        foreach ($this->saved as $name=>$value) { $p = new \ReflectionProperty(api::class,$name); $p->setAccessible(true); $p->setValue(null,$value); }
        GetApi::$handler = null; AnyApi::$any = null;
    }
    public function testMethodDispatchHeadTraceAndNoInterface()
    {
        $api = new GetApi; $input = new \flat\input\map(['value'=>42]);
        $this->assertSame(42, $api->set_input($input,'GET')->value);
        $this->assertFalse($api->isHead());
        $this->assertSame(42, $api->set_input($input,'HEAD')->value);
        $this->assertTrue($api->isHead());
        $this->assertNull($api->set_input($input,'TRACE')->value); // Dynamic input export currently loses fields.
        $this->assertInstanceOf(response\no_interface::class, (new NoApi)->set_input($input,'POST'));
        api::set_method('GET'); $this->assertSame(42, $api->set_input($input)->value);
        api::set_accept_mtype('application/json'); $this->assertSame('application/json', api::get_accept_mtype());
    }
    public function testAnyInterfaceFallsBackToSpecificMethodOrUsesItsOwnResponse()
    {
        $input = new \flat\input\map(['value'=>42]); $api = new AnyApi;
        $this->assertSame(42, $api->set_input($input,'GET')->value);
        AnyApi::$any = function () { return new response\ok(['any'=>true]); };
        $this->assertTrue($api->set_input($input,'POST')->any);
        AnyApi::$any = function () { throw new response\exception(new response\forbidden('denied')); };
        $this->assertInstanceOf(response\forbidden::class, $api->set_input($input,'GET'));
        AnyApi::$any = null;
        GetApi::$handler = function () { throw new response\exception(new response\not_found('missing')); };
        $this->assertInstanceOf(response\not_found::class, $api->set_input($input,'GET'));
    }
    public function testResponseHandlerCanObserveOrReplaceResponses()
    {
        $api = new GetApi; $input = new \flat\input\map;
        $replacement = new response\ok(['replacement'=>true]);
        api::set_response_handler(function ($response,$method) use ($replacement) { $this->assertSame('GET',$method); return $replacement; });
        $this->assertSame($replacement, $api->set_input($input,'GET'));
        $this->assertInstanceOf(response\ok::class, $api->set_input($input,'GET', function () { return null; }));
    }
    /** @dataProvider constructorInputs */
    public function testConstructorMapsInputsAndInvokesSuccessAndAlwaysCallbacks($input, $expected)
    {
        $seen = [];
        GetApi::$handler = function ($input) use (&$seen) { $seen['input'] = (array) $input; return new response\ok; };
        new GetApi(['method'=>'GET','input'=>$input], ['success'=>function ($response) use (&$seen) { $seen['success']=$response; }, 'always'=>function ($response) use (&$seen) { $seen['always']=$response; }]);
        $this->assertSame($expected,$seen['input']);
        $this->assertSame($seen['success'],$seen['always']);
    }
    public function constructorInputs() { return [[['a'=>1],['a'=>1]], [(object) ['a'=>1],['a'=>1]], [42,['data'=>42]], [null,[]]]; }
    public function testStringMethodConstructorAndDirectCallback()
    {
        $seen = null; new GetApi('GET', function ($response) use (&$seen) { $seen=$response; });
        $this->assertInstanceOf(response\ok::class,$seen);
        $ok = false; new GetApi('GET',['ok'=>function () use (&$ok) { $ok=true; }]);
        $this->assertTrue($ok);
    }
    public function testResponseExceptionsFromHandlerReachConstructorErrorAndAlwaysCallbacks()
    {
        $error = new response\error('denied',(object) ['key'=>'value']); $seen = [];
        new GetApi(['method'=>'GET','response_handler'=>function () use ($error) { throw new response\exception($error); }],
            ['error'=>function ($response,$message,$data,$exception) use (&$seen) { $seen=[$response,$message,$data,$exception]; },
             'always'=>function ($response,$exception) use (&$seen) { $seen[]=$response; }]);
        $this->assertSame($error,$seen[0]); $this->assertSame('denied',$seen[1]);
        $this->assertSame('value',$seen[2]->key); $this->assertInstanceOf(response\exception::class,$seen[3]);
        $this->assertSame($error,$seen[4]);
    }
    public function testReturnedErrorResponseSuppliesItsOwnMetadata()
    {
        $error = new response\error('denied',(object)['key'=>'value']);
        GetApi::$handler = function () use ($error) { return $error; }; $seen = [];
        new GetApi('GET',['error'=>function ($response,$message,$data) use (&$seen) { $seen=[$response,$message,$data]; }]);
        $this->assertSame($error,$seen[0]); $this->assertSame('denied',$seen[1]); $this->assertSame('value',$seen[2]->key);
    }
    public function testNonErrorFailureStatusUsesNullOptionalErrorMetadata()
    {
        $response = new response\forbidden; GetApi::$handler = function () use ($response) { return $response; };
        $seen = [];
        new GetApi('GET',['error'=>function (...$args) use (&$seen) { $seen=$args; }]);
        $this->assertSame([$response,null,null,null],$seen);
    }
    public function testOrdinaryHandledExceptionReturnsWithoutAnAlwaysResponse()
    {
        $exception = new \RuntimeException('failure'); GetApi::$handler = function () use ($exception) { throw $exception; }; $seen = [];
        new GetApi('GET',['error'=>function (...$args) use (&$seen) { $seen=$args; },'always'=>function () { $this->fail('No response exists'); }]);
        $this->assertSame([null,'failure',null,$exception],$seen);
    }
    public function testUnhandledConstructorExceptionPropagates()
    {
        GetApi::$handler = function () { throw new \RuntimeException('failure'); };
        $this->expectException(\RuntimeException::class); new GetApi('GET');
    }
    public function testInvalidResponseIsRejected()
    {
        GetApi::$handler = function () { return 'invalid'; };
        $this->expectException(\flat\core\controller\api\exception\bad_response::class);
        (new GetApi)->set_input(new \flat\input\map,'GET');
    }
    /** @group known-defect */
    public function testUnassignedRequestMethodGetterCurrentlyThrows()
    {
        $api = new GetApi; $api->set_input(new \flat\input\map,'GET');
        $this->expectException(\flat\core\controller\api\exception\missing_method::class); $api->requestMethod();
    }
    public function testInvalidFallbackMethodIsRejected()
    {
        $this->expectException(\flat\core\controller\api\exception\unknown_method::class); api::set_method('PATCH');
    }
    /** @group known-defect */
    public function testMissingMethodCurrentlyRaisesUnknownMethod()
    {
        $this->expectException(\flat\core\controller\api\exception\unknown_method::class);
        (new GetApi)->set_input(new \flat\input\map);
    }
}
