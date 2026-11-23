<?php
namespace flat\test\Unit\Cloud;
require_once __DIR__.'/../../Support/LegacyAuthorizeSdk.php';
use PHPUnit\Framework\TestCase;
use flat\cloud\authorize;
use flat\cloud\authorize\customer as c;

/**
 * KD-100: flat cannot build a payment method that Authorize.Net accepts. These pin what happens today, found
 * by the sandbox tests; they change when KD-100 is decided.
 */
class AuthorizePaymentMethodTest extends TestCase
{
    use \flat\test\Support\LegacyAuthorizeSdk;

    private $libxmlBefore;

    protected function setUp(): void
    {
        $this->libxmlBefore = libxml_use_internal_errors(); // flat's CIM constructor leaves it switched on
        foreach ([authorize\AuthorizeNetCIM::class, \AuthorizeNetCIM_Response::class] as $class) {
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
                return $this->_handleResponse('<r xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd"><messages><resultCode>Ok</resultCode></messages><customerPaymentProfileId>2001</customerPaymentProfileId></r>');
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

    private function paymentProfile()
    {
        $profile = new class extends c\payment_profile { public function get_data_type() { return 'creditCard'; } };
        $profile->billTo = new c\billTo(['firstName'=>'Flat', 'lastName'=>'Test']);
        $profile->payment = new c\payment_profile\creditCard(['cardNumber'=>'4111111111111111', 'expirationDate'=>'2030-12']);
        return $profile;
    }

    /** @group known-defect */
    public function testCurrentlyAPaymentArrayIsAPhpErrorNamingAnAbstractClass()
    {
        $customer = $this->customer($cim);
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Cannot instantiate abstract class flat\cloud\authorize\customer\payment_profile');
        $customer->create_paymethod(['customerProfileId'=>'1', 'payment'=>['billTo'=>['firstName'=>'Flat']]]);
    }

    /** @group known-defect */
    public function testCurrentlyAPaymentProfileObjectBecomesXmlAuthorizeNetCannotAccept()
    {
        $customer = $this->customer($cim);
        $this->assertSame('2001', $customer->create_paymethod(['customerProfileId'=>'1', 'payment'=>$this->paymentProfile()]));
        $this->assertStringContainsString('<>1</>', $cim->sent, 'elements without a name');
        $this->assertStringNotContainsString('<creditCard>', $cim->sent, 'the card fields are not wrapped in creditCard');
        $this->assertStringContainsString('<type>creditCard</type>', $cim->sent, 'a field Authorize.Net does not define');
    }

    /** @group known-defect */
    public function testCurrentlyUpdatingAPaymentMethodSendsTheSameInvalidXml()
    {
        $customer = $this->customer($cim);
        $customer->update_paymethod(['customerProfileId'=>'1', 'customerPaymentProfileId'=>'2001', 'payment'=>$this->paymentProfile()]);
        $this->assertStringContainsString('<>1</>', $cim->sent);
        $this->assertStringNotContainsString('<creditCard>', $cim->sent);
    }
}
