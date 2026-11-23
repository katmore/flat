<?php
namespace flat\test\Sandbox;
require_once __DIR__.'/SandboxSupport.php';
require_once __DIR__.'/../Support/LegacyAuthorizeSdk.php';
use PHPUnit\Framework\TestCase;
use flat\cloud\authorize\response_error;
use flat\cloud\authorize\customer\transaction\response\success;

/**
 * flat's customer transactions (a saved payment method charged through the Customer Information Manager)
 * against the sandbox, or the documentation-based mock. Test card number 4111111111111111 is Authorize.Net's
 * documented Visa test number; no real card is ever used. A billing ZIP code of 46282 is Authorize.Net's
 * documented way to ask the sandbox for a decline.
 *
 * @group sandbox
 * @group anet
 * @group services
 */
class TransactionTest extends TestCase
{
    use SandboxSupport;
    use \flat\test\Support\LegacyAuthorizeSdk;

    private $created = [];

    protected function setUp(): void
    {
        foreach ([\flat\cloud\authorize\AuthorizeNetCIM::class, \AuthorizeNetCIM_Response::class, \AuthorizeNetAIM_Response::class] as $class) {
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

    /** @return array [flat customer wrapper, customer profile id, payment profile id] */
    private function customerWithACard($zip = '46201')
    {
        $run = $this->sandboxRunId();
        $customer = new SandboxCustomer($this->sandboxLoginId(), $this->sandboxTransactionKey());
        $profileId = $customer->create(['merchantCustomerId'=>$run, 'description'=>"flat sandbox test $run", 'email'=>"$run@example.com"]);
        $this->created[] = $profileId;
        $paymentId = $customer->create_paymethod([
            'customerProfileId'=>$profileId,
            'payment'=>[
                'billTo'=>['firstName'=>'Flat', 'lastName'=>'Sandbox', 'zip'=>$zip],
                'payment'=>['creditCard'=>['cardNumber'=>'4111111111111111', 'expirationDate'=>'2035-12']],
            ],
        ]);
        return [$customer, $profileId, $paymentId];
    }

    private function charge($kind, $profileId, $paymentId, array $more = [])
    {
        return ['transaction'=>['transactionType'=>$kind, 'amount'=>'5.00', 'customerProfileId'=>$profileId, 'customerPaymentProfileId'=>$paymentId] + $more];
    }

    public function testAnAuthorizationAndCaptureIsApproved()
    {
        list($customer, $profileId, $paymentId) = $this->customerWithACard();
        $response = $customer->create_transaction($this->charge('authcapture', $profileId, $paymentId));
        $this->assertInstanceOf(success::class, $response);
        $this->assertSame('APPROVED', $response->status);
        $this->assertNotEmpty($response->authorization_code);
    }

    public function testAnAuthorizationOnlyIsApproved()
    {
        list($customer, $profileId, $paymentId) = $this->customerWithACard();
        $response = $customer->create_transaction($this->charge('authonly', $profileId, $paymentId));
        $this->assertInstanceOf(success::class, $response);
        $this->assertSame('APPROVED', $response->status);
    }

    public function testACaptureOnlyIsRefusedByTheProcessorAsAResponseError()
    {
        // Authorize.Net has withdrawn the Capture Only type (its transaction-types article: "no longer available"); the
        // sandbox refuses it with E00027 (checked 2026-11-23). The request
        // itself is well formed (a malformed one would be a schema error), and flat raises the refusal as a response_error.
        list($customer, $profileId, $paymentId) = $this->customerWithACard();
        try { $customer->create_transaction($this->charge('captureonly', $profileId, $paymentId, ['approvalCode'=>'ABC123'])); }
        catch (response_error $refused) {
            $this->assertSame('E00027', $refused->get_message_code());
            $this->assertStringContainsString('Capture Only', $refused->getMessage());
            return;
        }
        $this->fail('the processor should have refused Capture Only');
    }

    public function testADeclinedTransactionIsAResponseError()
    {
        // For a customer transaction Authorize.Net documents E00027 ("The transaction was unsuccessful."), and
        // flat raises every error answer as a response_error.
        list($customer, $profileId, $paymentId) = $this->customerWithACard('46282');
        try { $customer->create_transaction($this->charge('authcapture', $profileId, $paymentId)); }
        catch (response_error $declined) {
            $this->assertSame('E00027', $declined->get_message_code());
            return;
        }
        $this->fail('the transaction should have been declined');
    }
}
