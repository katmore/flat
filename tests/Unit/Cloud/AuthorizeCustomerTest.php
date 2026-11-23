<?php
namespace flat\test\Unit\Cloud;
require_once __DIR__.'/../../Support/LegacyAuthorizeSdk.php';

use PHPUnit\Framework\TestCase;
use flat\cloud\authorize;

class CimResponseDouble
{
    public $error = false;
    public $xml;
    public $paymentIds = [];
    public $queries = [];
    public function isError() { return $this->error; }
    public function getMessageCode() { return 'E-local'; }
    public function getMessageText() { return 'local rejection'; }
    public function getCustomerProfileId() { return 'profile-42'; }
    public function xpath($query) { $this->queries[] = $query; return $this->paymentIds; }
}
class CimTransactionResponseDouble extends CimResponseDouble
{
    public $transaction;
    public function getTransactionResponse() { return $this->transaction; }
}
class CimDouble
{
    public $calls = [];
    public $response;
    public function __construct() { $this->response = new CimResponseDouble; }
    public function __call($method, $arguments)
    {
        $this->calls[] = [$method, $arguments];
        return $this->response;
    }
}
class CustomerFixture extends authorize\customer
{
    public $client;
    public function __construct() { $this->client = new CimDouble; }
    protected function _get_CIM() { return $this->client; }
    protected function _get_login_id() { return 'local-login'; }
    protected function _get_transaction_key() { return 'local-key'; }
    protected function _get_end_point() { return 'https://gateway.invalid'; }
}
class PaymentProfileFixture extends authorize\customer\payment_profile
{
    public function get_data_type() { return 'fixture'; }
}
class NativeCustomerSetupFixture extends authorize\customer
{
    public $prepared = 0;
    protected function _get_login_id() { return 'local-login'; }
    protected function _get_transaction_key() { return 'local-key'; }
    protected function _get_end_point() { return 'https://gateway.invalid'; }
    protected function _prepare_AuthorizeNetRequest($client) { $this->prepared++; return $client; }
    public function client() { return $this->_get_CIM(); }
}

class AuthorizeCustomerTest extends TestCase
{
    use \flat\test\Support\LegacyAuthorizeSdk;
    public function testNativeLegacyClientIsPreparedAndCachedWhenSubclassProvidesTheHook()
    {
        $previous = libxml_use_internal_errors();
        try {
            $this->assertTrue($this->loadLegacyAuthorizeClass(authorize\AuthorizeNetCIM::class));
            $customer = new NativeCustomerSetupFixture; $client = $customer->client();
            $this->assertInstanceOf(\AuthorizeNetCIM::class,$client);
            $this->assertSame($client,$customer->client());
            $this->assertSame(1,$customer->prepared);
        } finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }
    }
    public function testGetMapsProfileAndMissingProfileReturnsNull()
    {
        $customer = new CustomerFixture;
        $this->assertNull($customer->get(['customerProfileId'=>'42']));
        $customer->client->response->xml = new \SimpleXMLElement('<response><profile><customerProfileId>42</customerProfileId><email>local@example.invalid</email></profile></response>');
        $profile = $customer->get(['customerProfileId'=>'42']);
        $this->assertSame('42', $profile->id);
        $this->assertSame('local@example.invalid', $profile->email);
        $this->assertSame(['getCustomerProfile', ['42']], $customer->client->calls[1]);
    }

    public function testProfileAliasDoesNotReplaceExplicitId()
    {
        $profile = new authorize\customer\profile(['id'=>'original', 'customerProfileId'=>'alias']);
        $this->assertSame('original', $profile->id);
        $dto = new authorize\customer\profile_Customer($profile);
        $this->assertSame('original', $dto->customerProfileId);
    }

    public function testProfileFieldsReachTheSdkCustomer()
    {
        $profile = new authorize\customer\profile(['merchantCustomerId'=>'m-1', 'description'=>'a customer', 'email'=>'a@example.invalid']);
        $dto = new authorize\customer\profile_Customer($profile);
        $this->assertSame('m-1', $dto->merchantCustomerId);
        $this->assertSame('a customer', $dto->description);
        $this->assertSame('a@example.invalid', $dto->email);
        $this->assertNull($dto->customerProfileId);
    }

    /** @dataProvider createModes */
    public function testCreateForwardsValidationMode($options, $expected)
    {
        $this->loadLegacyAuthorizeClass(\AuthorizeNetPaymentProfile::class);
        $customer = new CustomerFixture;
        $this->assertSame('profile-42', $customer->create($options + ['email'=>'local@example.invalid', 'payment'=>['billTo'=>['firstName'=>'Local']]]));
        list($method, $arguments) = $customer->client->calls[0];
        $this->assertSame('createCustomerProfile', $method);
        $this->assertInstanceOf(\AuthorizeNetCustomer::class, $arguments[0]);
        $this->assertSame('local@example.invalid', $arguments[0]->email);
        $this->assertSame($expected, $arguments[1]);
    }
    /** @dataProvider createModes */
    public function testWithoutAPaymentTheValidationModeIsAlwaysNone($options)
    {
        // Authorize.Net refuses any validation mode for a profile without payment profiles (E00013, seen on the sandbox).
        $customer = new CustomerFixture;
        $customer->create($options + ['email'=>'local@example.invalid']);
        $this->assertSame('none', $customer->client->calls[0][1][1]);
    }
    public function createModes()
    {
        return [[[], 'liveMode'], [['validate'=>true], 'liveMode'], [['validate'=>false], 'none'],
            [['validate'=>'liveMode'], 'liveMode'], [['validate'=>'none'], 'none'],
            [['validationMode'=>'testMode'], 'testMode'], [['validate'=>123], 'liveMode']];
    }

    public function testExplicitPaymentObjectIsConvertedForTheSdk()
    {
        $this->loadLegacyAuthorizeClass(\AuthorizeNetPaymentProfile::class);
        $customer = new CustomerFixture; $payment = new PaymentProfileFixture;
        $payment->billTo = new authorize\customer\billTo(['firstName'=>'Local']);
        $customer->create(['description'=>'local customer', 'payment'=>$payment]);
        $profile = $customer->client->calls[0][1][0]->paymentProfiles[0];
        $this->assertInstanceOf(\AuthorizeNetPaymentProfile::class, $profile);
        $this->assertSame('Local', $profile->billTo->firstName);
    }

    /** @dataProvider invalidRequests */
    public function testInvalidRequestsStopBeforeClientCalls($method, $params, $exception)
    {
        $customer = new CustomerFixture;
        try { $customer->$method($params); }
        catch (\Throwable $error) {
            $this->assertInstanceOf($exception, $error);
            $this->assertSame([], $customer->client->calls);
            return;
        }
        $this->fail('Expected validation failure');
    }
    public function invalidRequests()
    {
        $missing = authorize\missing_required_param::class; $bad = authorize\bad_param::class;
        return [['get', [], $missing], ['update', [], $missing], ['update', ['id'=>'42'], $bad],
            ['create', [], $bad], ['create', ['email'=>'a', 'validate'=>'invalid'], $bad],
            ['create', ['email'=>'a', 'payment'=>'invalid'], $bad],
            ['create', ['email'=>'a', 'payment'=>[]], $bad],
            ['create_paymethod', [], $missing], ['update_paymethod', [], $missing],
            ['update_paymethod', ['customerPaymentProfileId'=>'7'], $missing],
            ['delete_paymethod', [], $missing], ['delete_paymethod', ['customerPaymentProfileId'=>'7'], $missing],
            ['create_transaction', [], $missing], ['create_transaction', ['transaction'=>new \stdClass], $bad],
            ['create_transaction', ['transaction'=>'invalid'], $bad],
            ['create_transaction', ['transaction'=>[]], $missing],
            ['create_transaction', ['transaction'=>['transactionType'=>'unknown']], $bad],
            ['create_transaction', ['authonly'=>false], $bad]];
    }

    /** @dataProvider profileIds */
    public function testUpdateAndDeleteForwardIds($params)
    {
        $customer = new CustomerFixture;
        $this->assertNull($customer->update($params + ['email'=>'updated@example.invalid']));
        $this->assertSame('42', $customer->client->calls[0][1][0]);
        $this->assertSame('updated@example.invalid', $customer->client->calls[0][1][1]->email);
        $this->assertNull($customer->delete_paymethod(['customerProfileId'=>'42','customerPaymentProfileId'=>'7']));
        $this->assertSame(['deleteCustomerPaymentProfile', ['42','7']], $customer->client->calls[1]);
    }
    public function profileIds() { return [[['id'=>'42']], [['customerProfileId'=>'42']]]; }

    /** @dataProvider failingCalls */
    public function testClientRejectionBecomesResponseError($method, $params)
    {
        $customer = new CustomerFixture; $customer->client->response->error = true;
        $this->expectException(authorize\response_error::class);
        $this->expectExceptionMessage('E-local');
        $customer->$method($params);
    }
    public function failingCalls()
    {
        return [['get',['customerProfileId'=>'42']], ['update',['id'=>'42','email'=>'a']],
            ['create',['email'=>'a']], ['delete_paymethod',['customerProfileId'=>'42','customerPaymentProfileId'=>'7']]];
    }

    /** @dataProvider retiredOperations */
    public function testRetiredConvenienceOperationsFailClearlyWithoutCallingTheGateway($method,array $arguments,$hint)
    {
        $customer = new CustomerFixture;
        try {
            $customer->$method(...$arguments);
            $this->fail("$method() should be retired");
        } catch (\BadMethodCallException $e) {
            $this->assertStringContainsString("$method() is not implemented".$hint, $e->getMessage());
        }
        $this->assertSame([], $customer->client->calls);
    }
    public function retiredOperations()
    {
        $useCreate = function ($type) { return "; use create_transaction() with transactionType '$type'"; };
        return [
            'void' => ['void_transaction', [[]], ''],
            'refund' => ['refund_transaction', [[]], ''],
            'get' => ['get_transaction', [[]], ''],
            'authonly' => ['authonly', ['42','7','1.00'], $useCreate('authonly')],
            'authcapture' => ['authcapture', ['42','7','1.00'], $useCreate('authcapture')],
            'captureonly' => ['captureonly', ['42','7',['memo'=>'local']], $useCreate('captureonly')],
        ];
    }
    public function testPaymentMethodCreationAndUpdateForwardValidatedIds()
    {
        $this->loadLegacyAuthorizeClass(\AuthorizeNetPaymentProfile::class);
        $customer = new CustomerFixture; $payment = ['billTo'=>['firstName'=>'Local']];
        $customer->client->response->paymentIds = [new \SimpleXMLElement('<id>7</id>')];
        $this->assertSame('7',$customer->create_paymethod(['customerProfileId'=>'42','payment'=>$payment]));
        $this->assertSame('createCustomerPaymentProfile',$customer->client->calls[0][0]);
        $this->assertSame('42',$customer->client->calls[0][1][0]);
        $this->assertSame('Local',$customer->client->calls[0][1][1]->billTo->firstName);
        $this->assertSame(['//customerPaymentProfileId'],$customer->client->response->queries);
        $this->assertNull($customer->update_paymethod(['customerProfileId'=>'42','customerPaymentProfileId'=>'7','payment'=>$payment]));
        $this->assertSame(['updateCustomerPaymentProfile','42','7'],[$customer->client->calls[1][0],$customer->client->calls[1][1][0],$customer->client->calls[1][1][1]]);
        $this->assertSame('Local',$customer->client->calls[1][1][2]->billTo->firstName);
    }
    public function testMissingPaymentIdDoesNotRaiseAnIndexZeroNotice()
    {
        $this->loadLegacyAuthorizeClass(\AuthorizeNetPaymentProfile::class);
        $customer = new CustomerFixture;
        $previous = set_error_handler(function ($severity,$message,$file,$line) { throw new \ErrorException($message,0,$severity,$file,$line); });
        try {
            $this->expectException(authorize\unexpected_response::class);
            $customer->create_paymethod(['customerProfileId'=>'42','payment'=>['billTo'=>['firstName'=>'Local']]]);
        } finally { restore_error_handler(); }
    }
    /** @dataProvider rejectedPaymentMethods */
    public function testPaymentMethodRejectionUsesExistingResponseError($method,$params)
    {
        $this->loadLegacyAuthorizeClass(\AuthorizeNetPaymentProfile::class);
        $customer = new CustomerFixture; $customer->client->response->error = true;
        $this->expectException(authorize\response_error::class);
        $customer->$method($params);
    }
    public function rejectedPaymentMethods()
    {
        $payment = ['billTo'=>['firstName'=>'Local']];
        return [['create_paymethod',['customerProfileId'=>'42','payment'=>$payment]], ['update_paymethod',['customerProfileId'=>'42','customerPaymentProfileId'=>'7','payment'=>$payment]]];
    }
    /** @dataProvider transactionSpecifications */
    public function testTransactionSpecificationsNormalizeAndReachTheClient($params,$label,$class)
    {
        $this->loadLegacyAuthorizeClass(\AuthorizeNetAIM_Response::class);
        $reply = new CimTransactionResponseDouble; $reply->transaction = new \AuthorizeNetAIM_Response(null,',','',[]);
        $reply->transaction->response_code = '1'; $reply->transaction->authorization_code = 'local-auth';
        $customer = new CustomerFixture; $customer->client->response = $reply;
        $response = $customer->create_transaction($params);
        $this->assertInstanceOf(authorize\customer\transaction\response\success::class,$response);
        $this->assertSame('APPROVED',$response->status); $this->assertSame('local-auth',$response->authorization_code);
        $call = $customer->client->calls[0];
        $this->assertSame('createCustomerProfileTransaction',$call[0]); $this->assertSame($label,$call[1][0]);
        $this->assertInstanceOf($class,$call[1][1]);
    }
    public function transactionSpecifications()
    {
        $rows = [];
        foreach (['authcapture'=>'AuthCapture','authonly'=>'AuthOnly','captureonly'=>'CaptureOnly'] as $kind=>$label) {
            $class = authorize\customer\transaction::class.'\\'.$kind;
            $rows[] = [[$kind=>['amount'=>'1.00']],$label,$class];
            $rows[] = [[$kind=>new $class],$label,$class];
            $rows[] = [['transaction'=>['transactionType'=>$label,'amount'=>'1.00']],$label,$class];
            $rows[] = [['transaction'=>['transactionType'=>$kind]],$label,$class];
            $rows[] = [['transaction'=>new $class,'authonly'=>false],$label,$class];
        }
        return $rows;
    }
    public function testTransactionClientRejectionIsPropagatedBeforeDecoding()
    {
        $customer = new CustomerFixture; $customer->client->response->error = true;
        $this->expectException(authorize\response_error::class);
        $customer->create_transaction(['authcapture'=>[]]);
    }
}
