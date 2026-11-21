<?php
namespace flat\test\Unit\Cloud;
require_once __DIR__.'/../../Support/ConfigSandbox.php';
use PHPUnit\Framework\TestCase;
class AwsConfigTest extends TestCase
{
    use \flat\test\Support\ConfigSandbox;
    private $deprecationSetting;
    protected function setUp(): void
    {
        $this->deprecationSetting = getenv('AWS_SUPPRESS_PHP_DEPRECATION_WARNING');
        /*
         * APPROVED EXCEPTION to prime directive 2 (AGENTS.md, "Approved exceptions"; approved
         * 2026-11-21). The locked AWS SDK emits an E_USER_DEPRECATED notice whenever a client is
         * built on a PHP version below 8.1. That cause cannot be fixed before the php-8.1 phase,
         * so this test switches the notice off with the SDK's own variable. Remove it in the
         * php-8.1 phase. Do not copy this pattern.
         */
        putenv('AWS_SUPPRESS_PHP_DEPRECATION_WARNING=true');
        $this->openConfigSandbox(['s3'=>['key'=>'local-key','secret'=>'local-secret','region'=>'us-east-1','bucket'=>'local-bucket']]);
    }
    protected function tearDown(): void
    {
        $this->closeConfigSandbox();
        putenv($this->deprecationSetting === false ? 'AWS_SUPPRESS_PHP_DEPRECATION_WARNING' : 'AWS_SUPPRESS_PHP_DEPRECATION_WARNING='.$this->deprecationSetting);
    }
    public function testS3ClientConfigurationUsesRealSdkWithoutMakingRequests()
    {
        $provider = new \flat\cloud\aws\client_provider\from_config\s3('services/s3');
        $client = $provider->get_client();
        $this->assertInstanceOf(\Aws\S3\S3Client::class,$client);
        $this->assertSame($client,$provider->get_client());
        $this->assertSame('us-east-1',$client->getRegion());
        $credentials = $client->getCredentials()->wait();
        $this->assertSame('local-key',$credentials->getAccessKeyId());
        $this->assertSame('local-secret',$credentials->getSecretKey());
        $this->assertSame('local-bucket',(new \flat\cloud\aws\s3\bucket_provider\from_config('services/s3'))->get_bucket());
    }
}
