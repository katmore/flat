<?php
namespace flat\test\Sandbox;
require_once __DIR__.'/SandboxSupport.php';
require_once __DIR__.'/../Support/LegacyAuthorizeSdk.php';
use PHPUnit\Framework\TestCase;
use flat\cloud\authorize\response_error;

/**
 * flat's payment-method calls against the sandbox (or the documentation-based mock): add, change and remove a
 * payment method. Test card number 4111111111111111 is Authorize.Net's documented Visa test number; no real
 * card is ever used. The SDK's own client is used only to read the result back.
 *
 * @group sandbox
 * @group anet
 * @group services
 */
class PaymentMethodTest extends TestCase
{
    use SandboxSupport;
    use \flat\test\Support\LegacyAuthorizeSdk;

    private $created = [];

    protected function setUp(): void
    {
        foreach ([\flat\cloud\authorize\AuthorizeNetCIM::class, \AuthorizeNetPaymentProfile::class, \AuthorizeNetCIM_Response::class] as $class) {
            $this->loadLegacyAuthorizeClass($class);
        }
    }

    protected function tearDown(): void
    {
        // Deleting a customer profile removes its payment methods too.
        if ($this->created) {
            $api = new SandboxApi($this->sandboxLoginId(), $this->sandboxTransactionKey());
            foreach ($this->created as $id) { $api->deleteCustomerProfile($id); }
        }
    }

    /** @return array [flat customer wrapper, customer profile id] */
    private function customerProfile()
    {
        $run = $this->sandboxRunId();
        $customer = new SandboxCustomer($this->sandboxLoginId(), $this->sandboxTransactionKey());
        $profileId = $customer->create(['merchantCustomerId'=>$run, 'description'=>"flat sandbox test $run", 'email'=>"$run@example.com"]);
        $this->created[] = $profileId;
        return [$customer, $profileId];
    }

    /** @return array [flat customer wrapper, customer profile id, payment profile id] */
    private function customerWithACard()
    {
        list($customer, $profileId) = $this->customerProfile();
        $paymentId = $customer->create_paymethod([
            'customerProfileId'=>$profileId,
            'payment'=>[
                'billTo'=>['firstName'=>'Flat', 'lastName'=>'Sandbox'],
                'payment'=>['creditCard'=>['cardNumber'=>'4111111111111111', 'expirationDate'=>'2035-12']],
            ],
        ]);
        $this->assertMatchesRegularExpression('/^\d+$/', (string) $paymentId);
        return [$customer, $profileId, $paymentId];
    }

    public function testAPaymentMethodCanBeAddedChangedAndRemoved()
    {
        list($customer, $profileId, $paymentId) = $this->customerWithACard();

        $added = $customer->client()->getCustomerPaymentProfile($profileId, $paymentId, true);
        $this->assertFalse($added->isError(), $added->getMessageText());
        $this->assertStringEndsWith('1111', (string) $added->xml->paymentProfile->payment->creditCard->cardNumber);
        $this->assertSame('2035-12', (string) $added->xml->paymentProfile->payment->creditCard->expirationDate);
        $this->assertSame('Flat', (string) $added->xml->paymentProfile->billTo->firstName);

        $this->assertNull($customer->update_paymethod([
            'customerProfileId'=>$profileId, 'customerPaymentProfileId'=>$paymentId,
            'payment'=>['billTo'=>['firstName'=>'Flat', 'lastName'=>'Sandbox'], 'payment'=>['cardNumber'=>'4111111111111111', 'expirationDate'=>'2036-12']],
        ]));
        $changed = $customer->client()->getCustomerPaymentProfile($profileId, $paymentId, true);
        $this->assertSame('2036-12', (string) $changed->xml->paymentProfile->payment->creditCard->expirationDate);

        $this->assertNull($customer->delete_paymethod(['customerProfileId'=>$profileId, 'customerPaymentProfileId'=>$paymentId]));
        $this->assertTrue($customer->client()->getCustomerPaymentProfile($profileId, $paymentId)->isError(), 'the payment method should be gone');
    }

    public function testAnUnknownPaymentMethodIsAResponseError()
    {
        list($customer, $profileId) = $this->customerProfile();
        $this->expectException(response_error::class);
        $customer->delete_paymethod(['customerProfileId'=>$profileId, 'customerPaymentProfileId'=>'1']);
    }
}
