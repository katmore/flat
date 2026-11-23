<?php
namespace flat\test\Unit\Cloud;
require_once __DIR__.'/../../Support/LegacyAuthorizeSdk.php';
use PHPUnit\Framework\TestCase;
use flat\cloud\authorize;

/**
 * KD-105: create_transaction() sends the XML Authorize.Net defines for each kind of transaction, and reads the
 * answer. The client records what it would send and answers with a direct response of the real length.
 */
class AuthorizeTransactionRequestTest extends TestCase
{
    use \flat\test\Support\LegacyAuthorizeSdk;

    private $libxmlBefore;

    protected function setUp(): void
    {
        $this->libxmlBefore = libxml_use_internal_errors(); // flat's CIM constructor leaves it switched on
        foreach ([authorize\AuthorizeNetCIM::class, \AuthorizeNetCIM_Response::class, \AuthorizeNetAIM_Response::class] as $class) {
            $this->loadLegacyAuthorizeClass($class);
        }
    }

    protected function tearDown(): void
    {
        libxml_clear_errors();
        libxml_use_internal_errors($this->libxmlBefore);
    }

    /** A direct response as Authorize.Net sends it with x_encap_char=|: 55 fields. */
    private static function direct($code, $reasonCode, $reasonText, $authCode, $type)
    {
        $fields = array_fill(0, 55, '');
        $fields[0] = $code; $fields[1] = '1'; $fields[2] = $reasonCode; $fields[3] = $reasonText; $fields[4] = $authCode;
        $fields[5] = 'Y'; $fields[6] = '60000000001'; $fields[9] = '5.00'; $fields[10] = 'CC'; $fields[11] = $type;
        return '|' . implode('|,|', $fields) . '|';
    }

    private function customer(&$cim, $direct)
    {
        $cim = new class('login', 'key') extends authorize\AuthorizeNetCIM {
            public $sent;
            public $direct;
            protected function _sendRequest()
            {
                $this->_setPostString();
                $this->sent = $this->_post_string;
                return $this->_handleResponse('<r xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd"><messages><resultCode>Ok</resultCode></messages><directResponse>' . $this->direct . '</directResponse></r>');
            }
        };
        $cim->direct = $direct;
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

    private function xml($sent)
    {
        $document = simplexml_load_string($sent);
        $this->assertInstanceOf(\SimpleXMLElement::class, $document, 'the request is well-formed XML');
        return function ($path) use ($document) {
            $nodes = $document->xpath($path);
            return $nodes ? (string) $nodes[0] : null;
        };
    }

    public function transactionKinds()
    {
        return [['authcapture', 'profileTransAuthCapture'], ['authonly', 'profileTransAuthOnly'], ['captureonly', 'profileTransCaptureOnly']];
    }

    /** @dataProvider transactionKinds */
    public function testEachKindBecomesTheXmlAuthorizeNetDefines($kind, $element)
    {
        $customer = $this->customer($cim, self::direct('1', '1', 'This transaction has been approved.', 'ABC123', 'auth_capture'));
        $fields = ['amount'=>'5.00', 'customerProfileId'=>'1001', 'customerPaymentProfileId'=>'2001'];
        if ($kind === 'captureonly') $fields['approvalCode'] = 'ABC123';
        $response = $customer->create_transaction(['transaction'=>['transactionType'=>$kind] + $fields]);

        $this->assertInstanceOf(authorize\customer\transaction\response\success::class, $response);
        $this->assertSame('APPROVED', $response->status);
        $this->assertSame('ABC123', $response->authorization_code);

        $q = $this->xml($cim->sent);
        $base = '/*/*[local-name()="transaction"]/*[local-name()="' . $element . '"]/*[local-name()=';
        $this->assertSame('5.00', $q($base . '"amount"]'));
        $this->assertSame('1001', $q($base . '"customerProfileId"]'));
        $this->assertSame('2001', $q($base . '"customerPaymentProfileId"]'));
        $this->assertSame($kind === 'captureonly' ? 'ABC123' : null, $q($base . '"approvalCode"]'));
        $this->assertNull($q('//*[local-name()="type"]'), 'no field Authorize.Net does not define');
    }

    public function testADeclinedAnswerIsAFailureWithItsReason()
    {
        $customer = $this->customer($cim, self::direct('2', '2', 'This transaction has been declined.', '', 'auth_capture'));
        $response = $customer->create_transaction(['authcapture'=>['amount'=>'5.00', 'customerProfileId'=>'1001', 'customerPaymentProfileId'=>'2001']]);
        $this->assertInstanceOf(authorize\customer\transaction\response\failure::class, $response);
        $this->assertSame('DECLINED', $response->status);
        $this->assertSame('2', $response->reason_code);
        $this->assertSame('This transaction has been declined.', $response->reason);
    }

    public function testAnAmountIsRequiredByTheKindOfTransactionItself()
    {
        // The adapter checks the kind: a captureonly object cannot be sent as an authcapture.
        $adapter = new authorize\customer\transaction\authcapture\AuthorizeNetTransaction(new authorize\customer\transaction\authcapture(['amount'=>'1.00']));
        $this->assertSame('1.00', $adapter->amount);
        $this->expectException(authorize\bad_param::class);
        new authorize\customer\transaction\authcapture\AuthorizeNetTransaction(new authorize\customer\transaction\captureonly());
    }
}
