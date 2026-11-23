<?php
namespace flat\test\Sandbox;
require_once __DIR__.'/SandboxSupport.php';
require_once __DIR__.'/../Support/LegacyAuthorizeSdk.php';
use PHPUnit\Framework\TestCase;
use flat\cloud\authorize\response_error;

/**
 * flat's subscription (ARB) wrapper against the sandbox (or the documentation-based mock). One wrapper makes
 * every call of a test, as an application would. Test card number 4111111111111111 is Authorize.Net's
 * documented Visa test number; no real card is ever used.
 *
 * @group sandbox
 * @group anet
 * @group services
 */
class SubscriptionTest extends TestCase
{
    use SandboxSupport;
    use \flat\test\Support\LegacyAuthorizeSdk;

    private $active = [];
    private $service;

    protected function setUp(): void
    {
        foreach ([\flat\cloud\authorize\AuthorizeNetARB::class, \AuthorizeNetARB_Response::class,
                  \AuthorizeNetGetSubscriptionList::class, \AuthorizeNetSubscriptionListPaging::class] as $class) {
            $this->loadLegacyAuthorizeClass($class);
        }
        $this->service = new SandboxSubscription($this->sandboxLoginId(), $this->sandboxTransactionKey());
    }

    protected function tearDown(): void
    {
        // Leave the sandbox tidy: cancel what a test left running.
        foreach ($this->active as $id) { $this->service->client()->cancelSubscription($id); }
    }

    private function create($run)
    {
        $id = $this->service->create([
            'amount'=>5,
            'name'=>"flat $run",
            'payment'=>[
                'cardNumber'=>'4111111111111111',
                'expirationDate'=>(date('Y') + 3) . '-12',
                'schedule'=>['interval'=>['unit'=>'months', 'length'=>1], 'startDate'=>date('Y-m-d', strtotime('+3 days')), 'totalOccurrences'=>3],
            ],
            'billTo'=>['firstName'=>'Flat', 'lastName'=>'Sandbox'],
        ]);
        $this->active[] = $id;
        return $id;
    }

    public function testASubscriptionCanBeCreatedChangedAndCancelled()
    {
        $run = $this->sandboxRunId();
        $id = $this->create($run);
        $this->assertMatchesRegularExpression('/^\d+$/', (string) $id);
        $this->assertSame('active', $this->service->get_status(['subscriptionId'=>$id]));
        $this->assertTrue($this->service->is_updatable(['subscriptionId'=>$id]));
        $this->assertSame("flat $run", $this->service->get_info(['subscriptionId'=>$id])->name);

        $this->assertNull($this->service->update_amount(['subscriptionId'=>$id, 'amount'=>7]));
        $this->assertNull($this->service->update_paymethod(['subscriptionId'=>$id, 'payment'=>['cardNumber'=>'4111111111111111', 'expirationDate'=>(date('Y') + 4) . '-12']]));
        $this->assertSame('active', $this->service->get_status(['subscriptionId'=>$id]));

        $this->assertNull($this->service->cancel(['subscriptionId'=>$id]));
        $this->active = [];
        $this->assertSame('canceled', $this->service->get_status(['subscriptionId'=>$id]));
        $this->assertFalse($this->service->is_updatable(['subscriptionId'=>$id]));
    }

    public function testAnUnknownSubscriptionIsAResponseError()
    {
        $this->expectException(response_error::class);
        $this->service->get_status(['subscriptionId'=>'1']);
    }
}
