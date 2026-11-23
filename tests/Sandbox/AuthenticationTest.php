<?php
namespace flat\test\Sandbox;
require_once __DIR__.'/SandboxSupport.php';
require_once __DIR__.'/../Support/LegacyAuthorizeSdk.php';
use PHPUnit\Framework\TestCase;
use flat\cloud\authorize\response_error;

/**
 * @group sandbox
 * @group anet
 * @group services
 */
class AuthenticationTest extends TestCase
{
    use SandboxSupport;
    use \flat\test\Support\LegacyAuthorizeSdk;

    /** Needs no real account: Authorize.Net's own answer to made-up credentials, through flat's handler. */
    public function testInvalidCredentialsAreAnAuthorizeNetResponseError()
    {
        // The login ID is at most 25 characters in Authorize.Net's schema; a longer one is E00003, not E00007.
        $api = new SandboxApi('flat-bad-'.bin2hex(random_bytes(3)), 'invalid-key');
        try { $api->authenticate(); }
        catch (response_error $error) {
            $this->assertSame('E00007', $error->get_message_code());
            $this->assertStringContainsString('authentication', strtolower($error->get_message_text()));
            return;
        }
        $this->fail('Authorize.Net should have refused made-up credentials');
    }

    public function testTheSandboxAccountAuthenticatesThroughTheCurrentApi()
    {
        $api = new SandboxApi($this->sandboxLoginId(), $this->sandboxTransactionKey());
        $response = $api->authenticate();
        $this->assertSame('Ok', $response->getMessages()->getResultCode());
    }

    /** The legacy SDK classes flat's wrappers use must talk to the sandbox, never to production. */
    public function testTheLegacyClientTargetsTheSandboxOnly()
    {
        $this->loadLegacyAuthorizeClass(\flat\cloud\authorize\AuthorizeNetCIM::class);
        $client = (new SandboxCustomer('flat-invalid', 'invalid'))->client();
        $property = new \ReflectionProperty(\AuthorizeNetRequest::class, '_sandbox');
        $property->setAccessible(true);
        $this->assertTrue($property->getValue($client), 'the legacy client must be in sandbox mode');
    }
}
