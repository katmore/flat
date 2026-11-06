<?php
namespace flat\test\Unit\Cloud;

use PHPUnit\Framework\TestCase;
use flat\cloud\authorize;
use net\authorize\api\contract\v1 as Api;

class AuthorizeFixture extends authorize
{
    protected function _get_login_id() { return 'local-login'; }
    protected function _get_transaction_key() { return 'local-key'; }
    protected function _get_end_point() { return 'https://gateway.invalid/never-called'; }
    public function authentication() { return $this->_load_MerchantAuthentication(); }
    public function execute($controller) { return $this->_executeWithApiResponse($controller); }
}

// Real SDK base type; only the network boundary is replaced.
class AuthorizeControllerDouble extends \net\authorize\api\controller\base\ApiOperationBase
{
    public $response;
    public $endpoint;
    public $failure;
    public function __construct($response) { $this->response = $response; }
    public function executeWithApiResponse($endPoint = \net\authorize\api\constants\ANetEnvironment::CUSTOM)
    {
        $this->endpoint = $endPoint;
        if ($this->failure) { throw $this->failure; }
        return $this->response;
    }
}

class AuthorizeTest extends TestCase
{
    public static function response($code = 'Ok')
    {
        $message = new Api\MessagesType\MessageAType;
        $message->setCode('E0001'); $message->setText('local rejection');
        $messages = new Api\MessagesType;
        $messages->setResultCode($code); $messages->setMessage([$message]);
        $response = new Api\ANetApiResponseType;
        $response->setMessages($messages);
        return $response;
    }

    public function testAuthenticationUsesSubclassCredentials()
    {
        $auth = (new AuthorizeFixture)->authentication();
        $this->assertInstanceOf(Api\MerchantAuthenticationType::class, $auth);
        $this->assertSame('local-login', $auth->getName());
        $this->assertSame('local-key', $auth->getTransactionKey());
    }

    public function testSuccessfulResponseIsReturnedAndEndpointPassedToController()
    {
        $response = self::response(); $controller = new AuthorizeControllerDouble($response);
        $this->assertSame($response, (new AuthorizeFixture)->execute($controller));
        $this->assertSame('https://gateway.invalid/never-called', $controller->endpoint);
    }

    public function testRejectedResponseRetainsSdkMessages()
    {
        $response = self::response('Error');
        try { (new AuthorizeFixture)->execute(new AuthorizeControllerDouble($response)); }
        catch (authorize\response_error $error) {
            $this->assertSame($response, $error->get_response());
            $this->assertSame('E0001', $error->get_message_code());
            $this->assertSame('local rejection', $error->get_message_text());
            $this->assertSame("authorize.net response error 'E0001': local rejection", $error->getMessage());
            return;
        }
        $this->fail('Expected rejection');
    }

    public function testNullResponseBecomesResponseError()
    {
        $this->expectException(authorize\response_error::class);
        $this->expectExceptionMessage('authorize.net response error');
        (new AuthorizeFixture)->execute(new AuthorizeControllerDouble(null));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testControllerExceptionIsWrappedUsingExistingDiagnosticFormat()
    {
        $controller = new AuthorizeControllerDouble(null);
        $controller->failure = new \RuntimeException('local transport failure');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('local transport failure');
        (new AuthorizeFixture)->execute($controller);
    }

    public function testResponseWithoutMessagesAndLegacyScalarErrors()
    {
        $empty = new authorize\response_error(new Api\ANetApiResponseType);
        $this->assertNull($empty->get_message_code());
        $this->assertNull($empty->get_message_text());
        foreach ([null, false, 0, 12, 'declined'] as $value) {
            $error = new authorize\response_error($value, 'ignored legacy second argument');
            $this->assertSame($value, $error->get_message_code());
            $this->assertSame($value, $error->get_message_text());
            $this->assertNull($error->get_response());
        }
    }

    public function testParameterErrorsExposeDetails()
    {
        $error = new authorize\bad_param('amount', 'must be positive');
        $this->assertSame('amount', $error->get_param_key());
        $this->assertSame('must be positive', $error->get_reason());
        $this->assertSame("bad param 'amount': must be positive", $error->getMessage());
        $this->assertSame("bad param 'amount'", (new authorize\bad_param('amount'))->getMessage());
        foreach ([true, false] as $all) {
            $error = new authorize\missing_required_param(['id', 'email'], $all);
            $this->assertSame($all, $error->is_all_keys_required());
            $this->assertSame(['id', 'email'], $error->get_param_key());
            $this->assertStringContainsString($all ? 'ALL' : 'ONE', $error->get_reason());
        }
        foreach (['id', null] as $key) {
            $error = new authorize\missing_required_param($key);
            $this->assertNull($error->is_all_keys_required());
            $this->assertNotEmpty($error->get_reason());
        }
    }
}
