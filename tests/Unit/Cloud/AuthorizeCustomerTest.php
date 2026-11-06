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

    /** @dataProvider createModes */
    public function testCreateForwardsValidationMode($options, $expected)
    {
        $customer = new CustomerFixture;
        $this->assertSame('profile-42', $customer->create($options + ['email'=>'local@example.invalid']));
        list($method, $arguments) = $customer->client->calls[0];
        $this->assertSame('createCustomerProfile', $method);
        $this->assertInstanceOf(\AuthorizeNetCustomer::class, $arguments[0]);
        // Existing profile_Customer uses isset on null SDK fields and drops email.
        $this->assertNull($arguments[0]->email);
        $this->assertSame($expected, $arguments[1]);
    }
    public function createModes()
    {
        return [[[], 'liveMode'], [['validate'=>true], 'liveMode'], [['validate'=>false], 'none'],
            [['validate'=>'liveMode'], 'liveMode'], [['validate'=>'none'], 'none'],
            [['validationMode'=>'testMode'], 'testMode'], [['validate'=>123], 'liveMode']];
    }

    public function testExplicitPaymentObjectIsForwarded()
    {
        $customer = new CustomerFixture; $payment = new PaymentProfileFixture;
        $customer->create(['description'=>'local customer', 'payment'=>$payment]);
        $this->assertSame($payment, $customer->client->calls[0][1][0]->paymentProfiles[0]);
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
            ['create', ['email'=>'a', 'payment'=>[]], \Error::class],
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
        $this->assertNull($customer->client->calls[0][1][1]->email);
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

    public function testUnimplementedConvenienceOperationsCurrentlyReturnNull()
    {
        $customer = new CustomerFixture;
        $this->assertNull($customer->void_transaction([]));
        $this->assertNull($customer->refund_transaction([]));
        $this->assertNull($customer->authonly('42','7','1.00'));
        $this->assertNull($customer->authcapture('42','7','1.00'));
        $this->assertNull($customer->captureonly('42','7'));
        $this->assertNull($customer->captureonly('42','7',['memo'=>'local',0=>'ignored']));
        $this->assertNull($customer->get_transaction([]));
        $this->assertSame([], $customer->client->calls);
    }
    public function testPaymentMethodCreationAndUpdateForwardValidatedIds()
    {
        $customer = new CustomerFixture; $payment = new PaymentProfileFixture;
        $customer->client->response->paymentIds = [new \SimpleXMLElement('<id>7</id>')];
        $this->assertSame('7',$customer->create_paymethod(['customerProfileId'=>'42','payment'=>$payment]));
        $this->assertSame(['createCustomerPaymentProfile',['42',$payment]],$customer->client->calls[0]);
        $this->assertSame(['//customerPaymentProfileId'],$customer->client->response->queries);
        $this->assertNull($customer->update_paymethod(['customerProfileId'=>'42','customerPaymentProfileId'=>'7','payment'=>$payment]));
        $this->assertSame(['updateCustomerPaymentProfile',['42','7',$payment]],$customer->client->calls[1]);
    }
    public function testMissingPaymentIdDoesNotRaiseAnIndexZeroNotice()
    {
        $customer = new CustomerFixture;
        $previous = set_error_handler(function ($severity,$message,$file,$line) { throw new \ErrorException($message,0,$severity,$file,$line); });
        try {
            $this->expectException(authorize\unexpected_response::class);
            $customer->create_paymethod(['customerProfileId'=>'42']);
        } finally { restore_error_handler(); }
    }
    /** @dataProvider rejectedPaymentMethods */
    public function testPaymentMethodRejectionUsesExistingResponseError($method,$params)
    {
        $customer = new CustomerFixture; $customer->client->response->error = true;
        $this->expectException(authorize\response_error::class);
        $customer->$method($params);
    }
    public function rejectedPaymentMethods()
    {
        return [['create_paymethod',['customerProfileId'=>'42']], ['update_paymethod',['customerProfileId'=>'42','customerPaymentProfileId'=>'7']]];
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
