<?php
namespace flat\test\Unit\Cloud;
require_once __DIR__.'/../../Support/LegacyAuthorizeSdk.php';
use PHPUnit\Framework\TestCase;
use flat\cloud\authorize;
class ScheduleFixture extends authorize\subscription\schedule
{
    public $startDate_timeZone;
    public $new_subscription;
}
class TransactionSuccessFixture extends authorize\customer\transaction\response\success { public $status_int; }
class TransactionFailureFixture extends authorize\customer\transaction\response\failure { public $status_int; }
class AuthorizeDataTest extends TestCase
{
    use \flat\test\Support\LegacyAuthorizeSdk;
    /** @dataProvider lengths */
    public function testIntervalNormalizesScalarLengths($input, $expected)
    {
        $interval = new authorize\subscription\interval(['length'=>$input]);
        $this->assertSame($expected,$interval->length);
    }
    public function lengths() { return [['12',12],[12,12],[false,false],[[],[]],[null,null],['0','0']]; }
    /** @dataProvider occurrences */
    public function testScheduleDefaultsAndScalarOccurrenceNormalization($input, $expected)
    {
        $schedule = new ScheduleFixture(['totalOccurrences'=>$input,'startDate'=>'2020-03-04','startDate_timeZone'=>'America/Denver',
            'interval'=>['unit'=>'months','length'=>'2']]);
        $this->assertSame($expected,$schedule->totalOccurrences);
        $this->assertSame('2020-03-04',$schedule->startDate);
        $this->assertSame(2,$schedule->interval->length);
    }
    public function occurrences() { return [[null,9999],[0,9999],['12',12],[12,12],[true,true],[[],9999]]; }
    public function testNewScheduleUsesCurrentTimezoneAndInvalidTimezoneClearsDate()
    {
        $timezone = date_default_timezone_get(); date_default_timezone_set('America/Denver');
        try {
            $schedule = new ScheduleFixture(['new_subscription'=>true]);
            $this->assertSame('America/Denver',$schedule->startDate_timeZone);
        } finally { date_default_timezone_set($timezone); }
        $schedule = new ScheduleFixture(['startDate'=>'2020-03-04','startDate_timeZone'=>'invalid/timezone']);
        $this->assertNull($schedule->startDate);
    }
    public function testValidTimezoneConversionUsesNativeDateTimeZone()
    {
        $schedule = new ScheduleFixture(['startDate'=>'2020-03-04','startDate_timeZone'=>'UTC']);
        $this->assertSame('2020-03-03',$schedule->startDate);
    }
    public function testSubscriptionCacheRetainsMappedMetadata()
    {
        $cache = new authorize\subscription\cache(['subscription_id'=>'local','cipher'=>'opaque']);
        $this->assertSame('local',$cache->subscription_id); $this->assertSame('opaque',$cache->cipher);
    }
    public function testSubscriptionErrorsRetainTheirIdentifiersAndLegacySpellings()
    {
        $cancelled = new authorize\subscription\cancelled('sub-1');
        $this->assertSame('sub-1',$cancelled->get_subscriptionId());
        $this->assertInstanceOf(authorize\subscription\canceled::class,$cancelled);
        $this->assertSame('ARB is cancelled (subscriptionId: sub-1)',$cancelled->getMessage());
        $missing = new authorize\subscription\not_found('sub-2');
        $this->assertSame('sub-2',$missing->get_subscriptionId());
        $this->assertSame('subscription not found using id: sub-2',$missing->getMessage());
    }
    public function testSubscriptionPaymentAdaptersCopyFieldsIntoRealSdkDtos()
    {
        $card = new authorize\subscription\payment\creditCard(['cardNumber'=>'test-card','expirationDate'=>'2030-01','cardCode'=>'test-code']);
        $this->assertSame('creditCard',$card::get_data_type());
        $dto = new authorize\subscription\payment\creditCard_Subscription($card);
        $this->assertInstanceOf(\AuthorizeNet_Subscription::class,$dto);
        $this->assertSame('test-card',$dto->creditCardCardNumber);
        $bank = new authorize\subscription\payment\bankAccount(['accountNumber'=>'test-account','routingNumber'=>'test-route']);
        $this->assertSame('bankAccount',$bank::get_data_type());
        $dto = new authorize\subscription\payment\bankAccount_Subscription($bank);
        $this->assertSame('test-account',$dto->bankAccountAccountNumber);
        $this->assertSame('12.34',(new authorize\subscription\amount_Subscription('12.34'))->amount);
    }
    private function sdkResponse()
    {
        // The test intentionally exercises the real legacy SDK DTO. Suppress
        // only its file-load deprecation notices, preserving all other errors.
        $this->loadLegacyAuthorizeClass(\AuthorizeNetAIM_Response::class);
        $response = new \AuthorizeNetAIM_Response(null,',','',[]);
        $response->authorization_code = 'local-auth'; $response->response_reason_text = 'local decline';
        $response->response_reason_code = '7'; $response->response_subcode = '2';
        return $response;
    }
    /** @dataProvider statuses */
    public function testResponseSubclassCanSupplyStatusInteger($code,$expected)
    {
        $response = new TransactionSuccessFixture(['status_int'=>$code,'transactionResponse'=>$this->sdkResponse()]);
        $this->assertSame($expected,$response->status);
        $this->assertSame('local-auth',$response->authorization_code);
        $response = new TransactionSuccessFixture(['status'=>'custom','status_int'=>$code,'authorization_code'=>'explicit']);
        $this->assertSame('custom',$response->status);
        $this->assertSame('explicit',$response->authorization_code);
    }
    public function statuses() { return [[1,'APPROVED'],[2,'DECLINED'],[3,'ERROR'],[4,'HELD'],[9,null],[null,null]]; }
    public function testFailureDetailsComeFromSdkUnlessAlreadyProvided()
    {
        $failure = new TransactionFailureFixture(['transactionResponse'=>$this->sdkResponse()]);
        $this->assertSame('local decline',$failure->reason);
        $this->assertSame('7',$failure->reason_code);
        $this->assertSame('2',$failure->response_subcode);
        $failure = new TransactionFailureFixture(['reason'=>'explicit','reason_code'=>'8','response_subcode'=>'3']);
        $this->assertSame('explicit',$failure->reason);
        $this->assertSame('8',$failure->reason_code);
        $this->assertSame('3',$failure->response_subcode);
        $this->assertNull((new TransactionSuccessFixture)->authorization_code);
    }
    public function testUnexpectedResponseRetainsOptionalDetails()
    {
        $error = new authorize\unexpected_response('missing payment id');
        $this->assertSame('missing payment id',$error->get_details());
        $this->assertSame('got an unexpected response from server: missing payment id.',$error->getMessage());
        $this->assertSame('got an unexpected response from server',(new authorize\unexpected_response)->getMessage());
    }
    /** @dataProvider sdkStatuses */
    public function testFactoryMapsActualSdkCodesToResponseKindsAndStatus($code,$kind,$status)
    {
        $response = $this->sdkResponse(); $response->response_code = (string)$code;
        $mapped = authorize\customer\transaction\response::load($response);
        $this->assertInstanceOf($kind,$mapped); $this->assertSame($status,$mapped->status);
    }
    public function sdkStatuses()
    {
        $success = authorize\customer\transaction\response\success::class;
        $failure = authorize\customer\transaction\response\failure::class;
        return [[1,$success,'APPROVED'],[4,$success,'HELD'],[2,$failure,'DECLINED'],[3,$failure,'ERROR']];
    }
    public function testUnknownSdkResponseCodeStillReturnsNull()
    {
        $response = $this->sdkResponse(); $response->response_code = '99';
        $this->assertNull(authorize\customer\transaction\response::load($response));
    }
}
