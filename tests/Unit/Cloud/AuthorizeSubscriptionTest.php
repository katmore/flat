<?php
namespace flat\test\Unit\Cloud;
require_once __DIR__.'/../../Support/LegacyAuthorizeSdk.php';
use PHPUnit\Framework\TestCase;
use flat\cloud\authorize;

class ArbResponseDouble
{
    public $error = false;
    public $status = 'ACTIVE';
    public $totals = ['1'];
    public $details = [];
    public $subscriptionId = 'sub-created';
    public function isError() { return $this->error; }
    public function getSubscriptionId() { return $this->subscriptionId; }
    public function getMessageCode() { return 'E-local'; }
    public function getMessageText() { return 'local rejection'; }
    public function getSubscriptionStatus() { return $this->status; }
    public function xpath($query) { return substr($query,-19) === 'totalNumInResultSet' ? $this->totals : $this->details; }
}
class ArbClientDouble
{
    public $calls = [];
    public $response;
    public $responses = [];
    public function __construct() { $this->response = new ArbResponseDouble; }
    public function __call($method,$arguments) { $this->calls[] = [$method,$arguments]; return $this->responses ? array_shift($this->responses) : $this->response; }
}
class PagingArbClientDouble extends ArbClientDouble
{
    public $offsets = [];
    public function __call($method,$arguments)
    {
        if ($method === 'getSubscriptionList') $this->offsets[] = $arguments[0]->paging->offset;
        return parent::__call($method,$arguments);
    }
}
class CountingArbClientDouble extends ArbClientDouble
{
    public $count = 0;
    public function __call($method,$arguments) { $this->count++; return $this->response; }
}
class SubscriptionFixture extends authorize\subscription
{
    public $client;
    public function __construct() { $this->client = new ArbClientDouble; }
    protected function _get_ARB() { return $this->client; }
    protected function _get_login_id() { return 'local-login'; }
    protected function _get_transaction_key() { return 'local-key'; }
    protected function _get_end_point() { return 'https://gateway.invalid'; }
    public function void_transaction(array $params) { throw new \LogicException('Not used by subscription tests'); }
    public function refund_transaction(array $params) { throw new \LogicException('Not used by subscription tests'); }
    public function create_transaction(array $params) { throw new \LogicException('Not used by subscription tests'); }
}
class BoundedSubscriptionFixture extends SubscriptionFixture
{
    const arb_search_max_tics = 2;
}
class NativeArbFixture extends SubscriptionFixture
{
    public $prepared = 0;
    protected function _prepare_AuthorizeNetRequest($client) { $this->prepared++; return $client; }
    protected function _get_ARB() { return $this->nativeClient(); }
    public function nativeClient() { return authorize\subscription::_get_ARB(); }
}
class AuthorizeSubscriptionTest extends TestCase
{
    use \flat\test\Support\LegacyAuthorizeSdk;
    protected function setUp(): void { $this->loadLegacyAuthorizeClass(\AuthorizeNetARB::class); }
    private function payment() { return ['creditCard'=>['cardNumber'=>'local-card']]; }
    private function createParams() { return ['payment'=>$this->payment()+['schedule'=>['interval'=>['unit'=>'months','length'=>1]]],'amount'=>'12.34']; }
    public function testNativeArbConstructionUsesTheApplicationPreparationHookAndCachesItsClient()
    {
        $service = new NativeArbFixture;
        $client = $service->nativeClient();
        $this->assertInstanceOf(\AuthorizeNetARB::class,$client);
        $this->assertSame($client,$service->nativeClient()); $this->assertSame(1,$service->prepared);
    }
    /** @dataProvider requiredIdMethods */
    public function testExistingSubscriptionOperationsRequireTheirIdentifier($method)
    {
        $this->expectException(authorize\missing_required_param::class);
        (new SubscriptionFixture)->$method([]);
    }
    public function requiredIdMethods() { return array_map(function ($m) { return [$m]; },['update_amount','update_paymethod','cancel','get_status','get_info']); }
    public function testUpdateAmountAndCancelPassValidatedIdentifiersToTheirClient()
    {
        $service = new SubscriptionFixture;
        $this->assertNull($service->update_amount(['subscriptionId'=>'sub','amount'=>12]));
        $this->assertSame('sub',$service->client->calls[0][1][0]);
        $this->assertSame(12,$service->client->calls[0][1][1]->amount);
        $this->assertNull($service->cancel(['subscriptionId'=>'sub']));
        $this->assertSame(['cancelSubscription',['sub']],$service->client->calls[1]);
    }
    public function testRetiredGetTransactionFailsClearlyWithoutCallingTheGateway()
    {
        $service = new SubscriptionFixture;
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('authorize subscription get_transaction() is not implemented');
        try { $service->get_transaction([]); }
        finally { $this->assertSame([], $service->client->calls); }
    }
    /** @dataProvider invalidAmounts */
    public function testUpdateAmountRetainsItsExistingIntegerRestriction($params,$kind)
    {
        $this->expectException($kind); (new SubscriptionFixture)->update_amount(['subscriptionId'=>'sub']+$params);
    }
    public function invalidAmounts() { return [[[],authorize\missing_required_param::class],[['amount'=>'12'],authorize\bad_param::class]]; }
    /** @dataProvider statusValues */
    public function testStatusAndUpdatabilityUseTheExistingStatusMethod($status,$expected)
    {
        $service = new SubscriptionFixture; $service->client->response->status = $status;
        $this->assertSame(strtolower($status),$service->get_status(['subscriptionId'=>'sub']));
        $this->assertSame($expected,$service->is_updatable(['subscriptionId'=>'sub']));
    }
    public function statusValues() { return [['ACTIVE',true],['SUSPENDED',true],['CANCELLED',false]]; }
    /** @dataProvider rejectedOperations */
    public function testGatewayRejectionsUseTheDeclaredResponseError($method,$params)
    {
        $service = new SubscriptionFixture; $service->client->response->error = true;
        $this->expectException(authorize\response_error::class); $this->expectExceptionMessage('E-local');
        $service->$method(['subscriptionId'=>'sub']+$params);
    }
    public function rejectedOperations()
    {
        return [['update_amount',['amount'=>12]],['cancel',[]],['get_status',[]],['update_paymethod',['payment'=>$this->payment()]],['create',$this->createParams()]];
    }
    /** @dataProvider paymentForms */
    public function testPaymentFormsProduceRealSdkSubscriptionObjects($payment,$property,$expected)
    {
        $service = new SubscriptionFixture;
        $this->assertNull($service->update_paymethod(['subscriptionId'=>'sub','payment'=>$payment]));
        $dto = $service->client->calls[0][1][1];
        $this->assertInstanceOf(\AuthorizeNet_Subscription::class,$dto); $this->assertSame($expected,$dto->$property);
    }
    public function paymentForms()
    {
        return [[$this->payment(),'creditCardCardNumber','local-card'],
            [['cardNumber'=>'raw-card'],'creditCardCardNumber','raw-card'],
            [['accountType'=>'checking','accountNumber'=>'raw-bank'],'bankAccountAccountNumber','raw-bank'],
            [['bankAccount'=>['accountNumber'=>'nested-bank']],'bankAccountAccountNumber','nested-bank'],
            [new authorize\subscription\payment\creditCard(['cardNumber'=>'typed-card']),'creditCardCardNumber','typed-card'],
            [['bankAccount'=>new authorize\subscription\payment\bankAccount(['accountNumber'=>'typed-bank'])],'bankAccountAccountNumber','typed-bank']];
    }
    /** @dataProvider rejectedPayments */
    public function testInvalidPaymentShapesFailBeforeClientTransmission($params,$kind)
    {
        $service = new SubscriptionFixture;
        try { $service->update_paymethod(['subscriptionId'=>'sub']+$params); $this->fail('Expected invalid payment'); }
        catch (\Exception $error) { $this->assertInstanceOf($kind,$error); $this->assertSame([],$service->client->calls); }
    }
    public function rejectedPayments()
    {
        return [[[],authorize\missing_required_param::class],
            [['payment'=>['creditCard'=>[],'bankAccount'=>[]]],authorize\bad_param::class],
            [['payment'=>['creditCard'=>42]],authorize\bad_param::class],
            [['payment'=>[]],authorize\bad_param::class]];
    }
    public function testCreateMapsItsFieldsAndReturnsTheSubscriptionId()
    {
        $service = new SubscriptionFixture; $params=$this->createParams();
        $params['order']=['name'=>'local order']; $params['billTo']=new authorize\subscription\billTo(['firstName'=>'Local']);
        $this->assertSame('sub-created',$service->create($params));
        $dto=$service->client->calls[0][1][0]; $this->assertSame('12.34',$dto->amount); $this->assertSame('local order',$dto->name);
        $this->assertSame('Local',$dto->billToFirstName);
        $this->assertSame('months',$dto->intervalUnit); $this->assertSame(1,$dto->intervalLength);
        $params['order']=new authorize\subscription\order(['name'=>'object order']); $service->create($params);
        $this->assertSame('object order',$service->client->calls[1][1][0]->name);
    }
    /** @dataProvider invalidCreate */
    public function testCreateRejectsMissingOrMalformedDocumentedFields($change,$kind)
    {
        $params=$this->createParams();
        if ($change==='missing amount') unset($params['amount']);
        if ($change==='invalid amount') $params['amount']=false;
        if ($change==='missing schedule') unset($params['payment']['schedule']);
        if ($change==='invalid schedule') $params['payment']['schedule']='invalid';
        if ($change==='invalid order') $params['order']=42;
        $this->expectException($kind); (new SubscriptionFixture)->create($params);
    }
    public function invalidCreate() { return [['missing amount',authorize\missing_required_param::class],['invalid amount',authorize\bad_param::class],['missing schedule',authorize\missing_required_param::class],['invalid schedule',authorize\bad_param::class],['invalid order',authorize\bad_param::class]]; }
    public function testCreateAcceptsAnExistingScheduleObject()
    {
        $service=new SubscriptionFixture; $params=$this->createParams();
        $params['payment']['schedule']=new authorize\subscription\schedule(['interval'=>['unit'=>'months','length'=>1]]);
        $this->assertSame('sub-created',$service->create($params)); $this->assertCount(1,$service->client->calls);
    }
    public function testInfoSurfacesAListRejectionAfterSuccessfulStatusLookup()
    {
        $service=new SubscriptionFixture; $bad=new ArbResponseDouble; $bad->error=true;
        $service->client->responses=[new ArbResponseDouble,$bad];
        $this->expectException(authorize\response_error::class); $service->get_info(['subscriptionId'=>'sub']);
    }
    public function testInfoStopsAfterFullPageExceedsDeclaredTotal()
    {
        $service=new SubscriptionFixture;
        $service->client->response->details=array_fill(0,1000,new \SimpleXMLElement('<subscriptionDetail><id>other</id></subscriptionDetail>'));
        $this->expectException(authorize\subscription\not_found::class); $service->get_info(['subscriptionId'=>'sub']);
    }
    public function testInfoCircuitBreakerBoundsRepeatedFullPages()
    {
        $this->assertSame(100000,authorize\subscription::arb_search_max_tics);
        $service=new BoundedSubscriptionFixture; $service->client=new CountingArbClientDouble;
        $service->client->response->totals=[(string)PHP_INT_MAX];
        $service->client->response->details=array_fill(0,1000,new \SimpleXMLElement('<subscriptionDetail/>'));
        try { $service->get_info(['subscriptionId'=>'sub']); $this->fail('Expected bounded not found'); }
        catch (authorize\subscription\not_found $error) { $this->assertSame('sub',$error->get_subscriptionId()); }
        $this->assertSame(BoundedSubscriptionFixture::arb_search_max_tics+3,$service->client->count);
    }
    public function testInfoRequestsEachPageInTurn()
    {
        $service=new SubscriptionFixture; $service->client=new PagingArbClientDouble;
        $page=function (array $details) { $r=new ArbResponseDouble; $r->totals=['2000']; $r->details=$details; return $r; };
        $service->client->responses=[new ArbResponseDouble,
            $page(array_fill(0,1000,new \SimpleXMLElement('<subscriptionDetail><id>other</id></subscriptionDetail>'))),
            $page([new \SimpleXMLElement('<subscriptionDetail><id>sub</id><name>second page</name></subscriptionDetail>')])];
        $this->assertSame('second page',$service->get_info(['subscriptionId'=>'sub'])->name);
        $this->assertSame([1,2],$service->client->offsets);
    }
    public function testInfoFindsASubscriptionInActualXmlDetails()
    {
        $service=new SubscriptionFixture; $service->client->response->details=[new \SimpleXMLElement('<subscriptionDetail><id>sub</id><name>local</name></subscriptionDetail>')];
        $this->assertSame('local',$service->get_info(['subscriptionId'=>'sub'])->name);
        $this->assertSame('subscriptionActive',$service->client->calls[1][1][0]->searchType);
        $this->assertSame(1000,$service->client->calls[1][1][0]->paging->limit);
    }
    public function testInfoMissingTotalUsesUnexpectedResponseWithoutAnIndexZeroWarning()
    {
        $service=new SubscriptionFixture; $service->client->response->totals=[];
        $this->expectException(authorize\unexpected_response::class); $service->get_info(['subscriptionId'=>'sub']);
    }
    public function testInactiveInfoWithoutAMatchUsesNotFound()
    {
        $service=new SubscriptionFixture; $service->client->response->status='CANCELLED';
        $service->client->response->details=[new \SimpleXMLElement('<subscriptionDetail><name>no identifier</name></subscriptionDetail>')];
        try { $service->get_info(['subscriptionId'=>'sub']); $this->fail('Expected not found'); }
        catch (authorize\subscription\not_found $error) { $this->assertSame('sub',$error->get_subscriptionId()); }
        $this->assertSame('subscriptionInactive',$service->client->calls[1][1][0]->searchType);
    }
}
