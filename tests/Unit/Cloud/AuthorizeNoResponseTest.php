<?php
namespace flat\test\Unit\Cloud;
require_once __DIR__.'/../../Support/LegacyAuthorizeSdk.php';
use PHPUnit\Framework\TestCase;
use flat\cloud\authorize;

/**
 * KD-98: when Authorize.Net gives no usable answer (no reply at all, or one that is not its XML response) the
 * legacy wrappers raise no_response; before, the SDK's empty response looked like a success.
 */
class AuthorizeNoResponseTest extends TestCase
{
    use \flat\test\Support\LegacyAuthorizeSdk;

    private $libxmlBefore;

    protected function setUp(): void
    {
        // flat's CIM client constructor switches libxml to internal error capture and never restores it.
        $this->libxmlBefore = libxml_use_internal_errors();
        foreach ([authorize\AuthorizeNetCIM::class, authorize\AuthorizeNetARB::class, \AuthorizeNetCIM_Response::class] as $class) {
            $this->loadLegacyAuthorizeClass($class);
        }
    }

    protected function tearDown(): void
    {
        libxml_clear_errors();
        libxml_use_internal_errors($this->libxmlBefore);
    }

    private function ok()
    {
        return '<?xml version="1.0" encoding="utf-8"?><createCustomerProfileResponse xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">'
            . '<messages><resultCode>Ok</resultCode><message><code>I00001</code><text>Successful.</text></message></messages>'
            . '</createCustomerProfileResponse>';
    }

    public function testAnAuthorizeNetResponseIsReturnedUnchanged()
    {
        $response = new \AuthorizeNetCIM_Response($this->ok());
        $this->assertSame($response, authorize\no_response::checked($response));
    }

    public function testAnAuthorizeNetErrorIsStillAnAnswerNotNoResponse()
    {
        $error = str_replace(['Ok','I00001','Successful.'], ['Error','E00007','User authentication failed due to invalid authentication values.'], $this->ok());
        $response = authorize\no_response::checked(new \AuthorizeNetCIM_Response($error));
        $this->assertTrue($response->isError());
        $this->assertSame('E00007', $response->getMessageCode());
    }

    /** @dataProvider unusableReplies */
    public function testNoUsableReplyRaisesNoResponseThatSaysWhatHappened($reply)
    {
        try { authorize\no_response::checked(new \AuthorizeNetCIM_Response($reply)); }
        catch (authorize\no_response $error) {
            $this->assertInstanceOf(authorize\unexpected_response::class, $error);
            $this->assertStringContainsString('no usable reply from Authorize.Net', $error->getMessage());
            $this->assertStringContainsString('temporary issue', $error->getMessage());
            $this->assertStringContainsString('may or may not have been processed', $error->getMessage());
            return;
        }
        $this->fail('no_response was not raised');
    }
    public function unusableReplies()
    {
        return [[false], [''], ['not xml at all'], ['<html><body>502 Bad Gateway</body></html>'], ['<empty/>']];
    }

    public function testBothFlatClientsRaiseNoResponse()
    {
        $cim = new class('login', 'key') extends authorize\AuthorizeNetCIM { public function handle($reply) { return $this->_handleResponse($reply); } };
        $arb = new class('login', 'key') extends authorize\AuthorizeNetARB { public function handle($reply) { return $this->_handleResponse($reply); } };
        foreach ([$cim, $arb] as $client) {
            $this->assertInstanceOf(\AuthorizeNetXMLResponse::class, $client->handle($this->ok()));
            try { $client->handle(false); $this->fail('no_response was not raised by '.get_class($client)); }
            catch (authorize\no_response $expected) { $this->assertSame(1, 1); }
        }
    }

    public function testAWrapperCallRaisesInsteadOfLookingSuccessful()
    {
        $cim = new class('login', 'key') extends authorize\AuthorizeNetCIM {
            protected function _sendRequest() { $this->_setPostString(); return $this->_handleResponse(false); }
        };
        $customer = new class($cim) extends authorize\customer {
            private $cim;
            public function __construct($cim) { $this->cim = $cim; }
            protected function _get_login_id() { return 'login'; }
            protected function _get_transaction_key() { return 'key'; }
            protected function _get_end_point() { return 'https://apitest.authorize.net'; }
            protected function _prepare_AuthorizeNetRequest($client) { return $client; }
            protected function _get_CIM() { return $this->cim; }
        };
        $this->expectException(authorize\no_response::class);
        $customer->update(['customerProfileId'=>'42', 'email'=>'a@example.invalid']);
    }
}
