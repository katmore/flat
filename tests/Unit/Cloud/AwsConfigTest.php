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
        // This suite deliberately verifies the maintained PHP 7.2 baseline.
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
