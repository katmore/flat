<?php
namespace flat\test\Unit\Cloud;
require_once __DIR__.'/../../Support/LegacyAuthorizeSdk.php';
use PHPUnit\Framework\TestCase;
use flat\cloud\authorize;
use flat\cloud\authorize\customer as c;

/**
 * KD-100: payment methods (create_paymethod, update_paymethod, and a payment given to create) become the XML
 * Authorize.Net defines. The client records what it would send, so no network is used.
 */
class AuthorizePaymentMethodTest extends TestCase
{
    use \flat\test\Support\LegacyAuthorizeSdk;

    private $libxmlBefore;

    protected function setUp(): void
    {
        $this->libxmlBefore = libxml_use_internal_errors(); // flat's CIM constructor leaves it switched on
        foreach ([authorize\AuthorizeNetCIM::class, \AuthorizeNetCIM_Response::class, \AuthorizeNetPaymentProfile::class] as $class) {
            $this->loadLegacyAuthorizeClass($class);
        }
    }

    protected function tearDown(): void
    {
        libxml_clear_errors();
        libxml_use_internal_errors($this->libxmlBefore);
    }

    /** A customer wrapper whose client records the XML it would send and answers with a success. */
    private function customer(&$cim)
    {
        $cim = new class('login', 'key') extends authorize\AuthorizeNetCIM {
            public $sent;
            protected function _sendRequest()
            {
                $this->_setPostString();
                $this->sent = $this->_post_string;
                return $this->_handleResponse('<r xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd"><messages><resultCode>Ok</resultCode></messages><customerPaymentProfileId>2001</customerPaymentProfileId><customerProfileId>1001</customerProfileId></r>');
            }
        };
        return new class($cim) extends authorize\customer {
            private $cim;
            public function __construct($cim) { $this->cim = $cim; }
            protected function _get_login_id() { return 'login'; }
            protected function _get_transaction_key() { return 'key'; }
            protected function _get_end_point() { return 'https://apitest.authorize.net'; }
            protected function _prepare_AuthorizeNetRequest($client) { return $client; }
            protected function _get_CIM() { return $this->cim; }
        };
    }

    /** The sent XML as a document: it must be well formed, and is queried by element names. */
    private function xml($sent)
    {
        $document = simplexml_load_string($sent);
        $this->assertInstanceOf(\SimpleXMLElement::class, $document, 'the request is well-formed XML');
        return function ($path) use ($document) {
            $nodes = $document->xpath($path);
            return $nodes ? (string) $nodes[0] : null;
        };
    }

    private function card()
    {
        return ['cardNumber'=>'4111111111111111', 'expirationDate'=>'2030-12', 'cardCode'=>'123'];
    }

    /** Built in the test, not in a data provider: providers run outside code coverage. */
    private function paymentSpecification($name)
    {
        $card = $this->card();
        $person = ['firstName'=>'Flat', 'lastName'=>'Test'];
        switch ($name) {
            case 'array with a creditCard element': return ['billTo'=>$person, 'payment'=>['creditCard'=>$card]];
            case 'array with card fields': return ['billTo'=>$person, 'payment'=>$card];
            case 'array with a payment object': return ['billTo'=>new c\billTo($person), 'payment'=>new c\payment_profile\creditCard($card)];
            case 'payment_profile object':
                $profile = new class extends c\payment_profile { public $customerType = 'individual'; public function get_data_type() { return 'creditCard'; } };
                $profile->billTo = new c\billTo($person);
                $profile->payment = new c\payment_profile\creditCard($card);
                return $profile;
        }
        throw new \LogicException($name);
    }

    public function paymentSpecifications()
    {
        return [['array with a creditCard element'], ['array with card fields'], ['array with a payment object'], ['payment_profile object']];
    }

    /** @dataProvider paymentSpecifications */
    public function testACardPaymentMethodBecomesTheXmlAuthorizeNetDefines($name)
    {
        $payment = $this->paymentSpecification($name);
        $customer = $this->customer($cim);
        $this->assertSame('2001', $customer->create_paymethod(['customerProfileId'=>'1001', 'payment'=>$payment]));
        $q = $this->xml($cim->sent);
        $this->assertSame('1001', $q('/*/*[local-name()="customerProfileId"]'));
        $this->assertSame('Flat', $q('//*[local-name()="paymentProfile"]/*[local-name()="billTo"]/*[local-name()="firstName"]'));
        $this->assertSame('4111111111111111', $q('//*[local-name()="paymentProfile"]/*[local-name()="payment"]/*[local-name()="creditCard"]/*[local-name()="cardNumber"]'));
        $this->assertSame('2030-12', $q('//*[local-name()="creditCard"]/*[local-name()="expirationDate"]'));
        $this->assertNull($q('//*[local-name()="type"]'), 'no field Authorize.Net does not define');
        $this->assertNull($q('//*[local-name()="bankAccount"]'), 'an empty bank account is left out');
        $this->assertSame($name === 'payment_profile object' ? 'individual' : null, $q('//*[local-name()="paymentProfile"]/*[local-name()="customerType"]'));
    }

    public function testACustomerTypeIsSent()
    {
        $customer = $this->customer($cim);
        $customer->create_paymethod(['customerProfileId'=>'1001', 'payment'=>['customerType'=>'business', 'payment'=>$this->card()]]);
        $this->assertSame('business', $this->xml($cim->sent)('//*[local-name()="paymentProfile"]/*[local-name()="customerType"]'));
    }

    public function testBankAccountFieldsWithoutAWrapperAreRecognised()
    {
        $customer = $this->customer($cim);
        $customer->create_paymethod(['customerProfileId'=>'1001', 'payment'=>['payment'=>['routingNumber'=>'125000024', 'accountNumber'=>'12345678']]]);
        $this->assertSame('12345678', $this->xml($cim->sent)('//*[local-name()="bankAccount"]/*[local-name()="accountNumber"]'));
    }

    public function testABankAccountPaymentMethodBecomesBankAccountXml()
    {
        $customer = $this->customer($cim);
        $customer->create_paymethod(['customerProfileId'=>'1001', 'payment'=>['payment'=>['bankAccount'=>[
            'accountType'=>'checking', 'routingNumber'=>'125000024', 'accountNumber'=>'12345678', 'nameOnAccount'=>'Flat Test']]]]);
        $q = $this->xml($cim->sent);
        $this->assertSame('125000024', $q('//*[local-name()="bankAccount"]/*[local-name()="routingNumber"]'));
        $this->assertNull($q('//*[local-name()="creditCard"]'));
    }

    public function testUpdatingAPaymentMethodNamesTheMethodInsideThePaymentProfile()
    {
        $customer = $this->customer($cim);
        $this->assertNull($customer->update_paymethod(['customerProfileId'=>'1001', 'customerPaymentProfileId'=>'2001', 'payment'=>['payment'=>$this->card()]]));
        $q = $this->xml($cim->sent);
        $this->assertSame('2001', $q('//*[local-name()="paymentProfile"]/*[local-name()="customerPaymentProfileId"]'));
        $this->assertSame('4111111111111111', $q('//*[local-name()="creditCard"]/*[local-name()="cardNumber"]'));
    }

    public function testAPaymentGivenToCreateAProfileIsPartOfTheProfile()
    {
        $customer = $this->customer($cim);
        $customer->create(['email'=>'a@example.invalid', 'payment'=>['payment'=>$this->card()]]);
        $q = $this->xml($cim->sent);
        $this->assertSame('a@example.invalid', $q('//*[local-name()="profile"]/*[local-name()="email"]'));
        $this->assertSame('4111111111111111', $q('//*[local-name()="profile"]/*[local-name()="paymentProfiles"]/*[local-name()="payment"]/*[local-name()="creditCard"]/*[local-name()="cardNumber"]'));
    }

    public function testAPaymentIsRequired()
    {
        $customer = $this->customer($cim);
        foreach (['create_paymethod'=>['customerProfileId'=>'1'], 'update_paymethod'=>['customerProfileId'=>'1', 'customerPaymentProfileId'=>'2']] as $method=>$params) {
            try { $customer->$method($params); $this->fail("$method needs a payment"); }
            catch (authorize\missing_required_param $expected) { $this->assertNull($cim->sent); }
        }
    }

    /** @dataProvider invalidPayments */
    public function testAnInvalidPaymentStopsBeforeAnyRequest($payment)
    {
        $customer = $this->customer($cim);
        try { $customer->create_paymethod(['customerProfileId'=>'1', 'payment'=>$payment]); $this->fail('bad_param was not raised'); }
        catch (authorize\bad_param $expected) { $this->assertNull($cim->sent); }
    }
    public function invalidPayments()
    {
        $card = $this->card();
        return [
            'a string' => ['card'],
            'nothing in it' => [[]],
            'both payment types' => [['payment'=>['creditCard'=>$card, 'bankAccount'=>['accountNumber'=>'1']]]],
            'payment without a type' => [['payment'=>['nothing'=>'useful']]],
            'payment that is not an array' => [['payment'=>'card']],
            'an unknown card field' => [['payment'=>['creditCard'=>$card + ['colour'=>'red']]]],
            'an unknown billTo field' => [['billTo'=>['nickname'=>'x'], 'payment'=>$card]],
            'billTo that is not fields' => [['billTo'=>'Flat', 'payment'=>$card]],
        ];
    }
}
