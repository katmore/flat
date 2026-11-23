<?php
namespace flat\test\Sandbox;
require_once __DIR__.'/SandboxSupport.php';
require_once __DIR__.'/../Support/LegacyAuthorizeSdk.php';
use PHPUnit\Framework\TestCase;
use flat\cloud\authorize\response_error;

/**
 * flat's payment-method calls against the sandbox (or the documentation-based mock).
 *
 * flat cannot create a payment method itself yet (KD-100), so each test makes one with the legacy SDK's own
 * objects and then tests what flat can do with it: remove it, and report an unknown one. Test card number
 * 4111111111111111 is Authorize.Net's documented Visa test number; no real card is ever used.
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

        $card = new \AuthorizeNetPaymentProfile;
        $card->customerType = 'individual';
        $card->billTo->firstName = 'Flat';
        $card->billTo->lastName = 'Sandbox';
        $card->payment->creditCard->cardNumber = '4111111111111111';
        $card->payment->creditCard->expirationDate = (date('Y') + 3) . '-12';
        $response = $customer->client()->createCustomerPaymentProfile($profileId, $card, 'none');
        $this->assertFalse($response->isError(), 'setting up the payment method: ' . $response->getMessageCode() . ' ' . $response->getMessageText());
        return [$customer, $profileId, $response->getPaymentProfileId()];
    }

    public function testAPaymentMethodCanBeRemoved()
    {
        list($customer, $profileId, $paymentId) = $this->customerWithACard();

        $before = $customer->client()->getCustomerPaymentProfile($profileId, $paymentId);
        $this->assertFalse($before->isError(), $before->getMessageText());
        $this->assertStringEndsWith('1111', (string) $before->xml->paymentProfile->payment->creditCard->cardNumber);

        $this->assertNull($customer->delete_paymethod(['customerProfileId'=>$profileId, 'customerPaymentProfileId'=>$paymentId]));

        $after = $customer->client()->getCustomerPaymentProfile($profileId, $paymentId);
        $this->assertTrue($after->isError(), 'the payment method should be gone');
    }

    public function testAnUnknownPaymentMethodIsAResponseError()
    {
        list($customer, $profileId) = $this->customerProfile();
        $this->expectException(response_error::class);
        $customer->delete_paymethod(['customerProfileId'=>$profileId, 'customerPaymentProfileId'=>'1']);
    }
}
