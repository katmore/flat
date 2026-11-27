<?php
namespace flat\test\Unit\Cloud;
use PHPUnit\Framework\TestCase;
use flat\cloud\authorize;

/**
 * The subscription wrapper with the real legacy ARB client and response classes, offline: the client records the
 * XML it would send and answers from a queue (KD-101, KD-102).
 */
class AuthorizeSubscriptionSdkTest extends TestCase
{
    use \flat\test\Support\LegacyAuthorizeSdk;

    private $libxmlBefore;

    protected function setUp(): void
    {
        $this->libxmlBefore = libxml_use_internal_errors();
        foreach ([authorize\AuthorizeNetARB::class, \AuthorizeNetARB_Response::class, \AuthorizeNetGetSubscriptionList::class, \AuthorizeNetSubscriptionListPaging::class] as $class) {
            $this->loadLegacyAuthorizeClass($class);
        }
    }

    protected function tearDown(): void
    {
        libxml_clear_errors();
        libxml_use_internal_errors($this->libxmlBefore);
    }

    /** A reply as the real service sends it: the xsi and xsd namespaces come before the default one. */
    private static function reply($root, $body)
    {
        return '<?xml version="1.0" encoding="utf-8"?><' . $root . ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" '
            . 'xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd"><messages><resultCode>Ok</resultCode><message><code>I00001</code><text>Successful.</text></message></messages>'
            . $body . '</' . $root . '>';
    }

    private function wrap($arb)
    {
        return new class($arb) extends authorize\subscription {
            private $arb;
            public function __construct($arb) { $this->arb = $arb; }
            protected function _get_login_id() { return 'login'; }
            protected function _get_transaction_key() { return 'key'; }
            protected function _get_end_point() { return 'https://apitest.authorize.net'; }
            protected function _prepare_AuthorizeNetRequest($client) { return $client; }
            protected function _get_ARB() { return $this->arb; }
            public function void_transaction(array $params) { throw new \LogicException('unused'); }
            public function refund_transaction(array $params) { throw new \LogicException('unused'); }
            public function create_transaction(array $params) { throw new \LogicException('unused'); }
        };
    }

    /** A wrapper whose client answers from a queue instead of the network. */
    private function service(array $replies)
    {
        $arb = new class('login', 'key') extends authorize\AuthorizeNetARB {
            public $replies = [];
            protected function _sendRequest() { return $this->_handleResponse(array_shift($this->replies)); }
        };
        $arb->replies = $replies;
        return $this->wrap($arb);
    }

    public function testGetInfoReadsTheListResponseTheSchemaDescribes()
    {
        // ARBGetSubscriptionListResponse holds totalNumInResultSet and subscriptionDetails directly (AnetApiSchema.xsd).
        $service = $this->service([
            self::reply('ARBGetSubscriptionStatusResponse', '<status>active</status>'),
            self::reply('ARBGetSubscriptionListResponse', '<totalNumInResultSet>1</totalNumInResultSet><subscriptionDetails><subscriptionDetail><id>3001</id><name>flat test</name><status>active</status></subscriptionDetail></subscriptionDetails>'),
        ]);
        $this->assertSame('flat test', $service->get_info(['subscriptionId'=>'3001'])->name);
    }

    public function testASubscriptionMissingFromTheListIsNotFound()
    {
        $service = $this->service([
            self::reply('ARBGetSubscriptionStatusResponse', '<status>active</status>'),
            self::reply('ARBGetSubscriptionListResponse', '<totalNumInResultSet>0</totalNumInResultSet><subscriptionDetails/>'),
        ]);
        $this->expectException(authorize\subscription\not_found::class);
        $service->get_info(['subscriptionId'=>'3001']);
    }

    public function testEachCallSendsOnlyItsOwnData()
    {
        // One wrapper, several calls: the SDK appends each call's data to the next one's.
        $arb = new class('login', 'key') extends authorize\AuthorizeNetARB {
            public $sent = [];
            protected function _getPostUrl() { return 'http://127.0.0.1:9/'; } // nothing listens there: the request fails at once
            protected function _setPostString() { parent::_setPostString(); $this->sent[] = $this->_post_string; }
        };
        $service = $this->wrap($arb);
        $calls = [
            function () use ($service) { $service->update_amount(['subscriptionId'=>'9', 'amount'=>7]); },
            function () use ($service) { $service->get_status(['subscriptionId'=>'9']); },
            function () use ($service) { $service->cancel(['subscriptionId'=>'9']); },
        ];
        foreach ($calls as $call) {
            try { $call(); $this->fail('the request should fail: nothing listens'); }
            catch (authorize\no_response $expected) { $this->assertSame(1, 1); }
        }
        $this->assertCount(3, $arb->sent);
        $this->assertSame(1, substr_count($arb->sent[1], '<subscriptionId>'), 'status request: its own id only');
        $this->assertStringNotContainsString('<amount>', $arb->sent[1]);
        $this->assertSame(1, substr_count($arb->sent[2], '<subscriptionId>'), 'cancel request: its own id only');
        $this->assertStringNotContainsString('<amount>', $arb->sent[2]);
    }
}
