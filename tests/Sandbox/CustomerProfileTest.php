<?php
namespace flat\test\Sandbox;
use PHPUnit\Framework\TestCase;
use flat\cloud\authorize\response_error;

/**
 * flat's legacy customer wrapper against the real sandbox: the Customer Information Manager XML API, through
 * the old SDK classes flat is built on.
 *
 * @group sandbox
 * @group anet
 * @group services
 */
class CustomerProfileTest extends TestCase
{
    use SandboxSupport;
    use \flat\test\Support\LegacyAuthorizeSdk;

    private $created = [];

    protected function setUp(): void
    {
        $this->loadLegacyAuthorizeClass(\flat\cloud\authorize\AuthorizeNetCIM::class);
    }

    protected function tearDown(): void
    {
        // Leave the sandbox tidy: delete what a test made, through the current API.
        if ($this->created) {
            $api = new SandboxApi($this->sandboxLoginId(), $this->sandboxTransactionKey());
            foreach ($this->created as $id) { $api->deleteCustomerProfile($id); }
        }
    }

    /** Needs no real account: a real round trip through the legacy SDK and flat's wrapper, ending in Authorize.Net's own error. */
    public function testInvalidCredentialsComeBackThroughTheLegacyWrapperAsAResponseError()
    {
        $customer = new SandboxCustomer('flat-bad-'.bin2hex(random_bytes(3)), 'invalid-key');
        try { $customer->get(['customerProfileId'=>'1']); }
        catch (response_error $error) {
            $this->assertSame('E00007', $error->get_message_code());
            return;
        }
        $this->fail('Authorize.Net should have refused made-up credentials');
    }

    public function testACustomerProfileCanBeCreatedReadAndUpdated()
    {
        $run = $this->sandboxRunId();
        $customer = new SandboxCustomer($this->sandboxLoginId(), $this->sandboxTransactionKey());

        $id = $customer->create(['merchantCustomerId'=>$run, 'description'=>"flat sandbox test $run", 'email'=>"$run@example.com"]);
        $this->created[] = $id;
        $this->assertMatchesRegularExpression('/^\d+$/', (string) $id);

        $profile = $customer->get(['customerProfileId'=>$id]);
        $this->assertSame($run, (string) $profile->merchantCustomerId);
        $this->assertSame("$run@example.com", (string) $profile->email);

        $customer->update(['customerProfileId'=>$id, 'email'=>"changed-$run@example.com"]);
        $this->assertSame("changed-$run@example.com", (string) $customer->get(['customerProfileId'=>$id])->email);
    }

    public function testAnUnknownCustomerProfileIsAResponseError()
    {
        $customer = new SandboxCustomer($this->sandboxLoginId(), $this->sandboxTransactionKey());
        $this->expectException(response_error::class);
        $customer->get(['customerProfileId'=>'1']);
    }
}
