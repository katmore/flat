<?php
namespace flat\test\Unit\Cloud;
use PHPUnit\Framework\TestCase;
use flat\cloud\aws;
class ClientDouble
{
    public $calls = [];
    public function putObject(array $options) { $this->calls[] = ['putObject',$options]; return []; }
    public function waitUntil($waiter,array $options) { $this->calls[] = [$waiter,$options]; }
}
class AwsFixture extends aws
{
    protected function _get_client_class() { return ClientDouble::class; }
    public function client() { return $this->_get_client(); }
    public function supply($client) { $this->_set_client($client); }
}
class ProviderFixture extends AwsFixture implements aws\client_provider
{
    public $provided;
    public $lookups = 0;
    public function get_aws_client() { $this->lookups++; return $this->provided; }
}
class PublicS3 extends aws\s3 implements aws\s3\bucket_provider
{
    public $file;
    public $key;
    public $bucket;
    public $contentType;
    public $prefix;
    protected function _get_client_class() { return ClientDouble::class; }
    public function get_s3_bucket() { return 'provided-bucket'; }
    public function get_aws_client() { return new ClientDouble; }
    public function standardClientClass() { return parent::_get_client_class(); }
}
class CustomUrlS3 extends PublicS3 implements aws\s3\baseurl_provider
{
    public function get_s3_baseurl($bucket,$prefix=null) { return 'https://example.com/'.$bucket; }
}
class AwsTest extends TestCase
{
    public function testExplicitClientIsCheckedAndCached()
    {
        $fixture = new AwsFixture; $client = new ClientDouble;
        $fixture->supply($client); $this->assertSame($client,$fixture->client());
        $this->expectException(aws\bad_client::class); $fixture->supply(new \stdClass);
    }
    public function testProviderClientIsResolvedOnce()
    {
        $fixture = new ProviderFixture; $fixture->provided = new ClientDouble;
        $this->assertSame($fixture->provided,$fixture->client());
        $this->assertSame($fixture->provided,$fixture->client()); $this->assertSame(1,$fixture->lookups);
    }
    public function testMissingClientReportsUnavailable()
    {
        $this->expectException(aws\client_unavailable::class); (new AwsFixture)->client();
    }
    /** @dataProvider prefixes */
    public function testBodyUploadCanonicalizesKeysAndWaitsForTheUploadedObject($prefix,$key,$expected)
    {
        $client = new ClientDouble; aws\s3::body_upload($client,'bucket','body',$key,$prefix,'text/plain');
        $this->assertSame(['putObject',['Bucket'=>'bucket','Key'=>$expected,'Body'=>'body','ContentType'=>'text/plain']],$client->calls[0]);
        $this->assertSame(['ObjectExists',['Bucket'=>'bucket','Key'=>$expected]],$client->calls[1]);
    }
    public function prefixes() { return [[null,'key','key'], ['folder','/key','folder/key'], ['/folder/','/key','folder/key'], ['/','key','key'], [42,'key','key']]; }
    public function testSmallFileUploadUsesFileAndOptionalContentType()
    {
        $path = tempnam(sys_get_temp_dir(),'flat-s3-'); file_put_contents($path,'data');
        try {
            $client = new ClientDouble; aws\s3::file_upload($client,'bucket',$path,'key');
            $this->assertSame(['Bucket'=>'bucket','Key'=>'key','SourceFile'=>$path],$client->calls[0][1]);
            aws\s3::file_upload($client,'bucket',$path,'key',null,'application/octet-stream');
            $this->assertSame('application/octet-stream',$client->calls[2][1]['ContentType']);
        } finally { unlink($path); }
    }
    /** @dataProvider invalidFiles */
    public function testFileUploadRejectsInvalidPathsBeforeClientCalls($kind,$parameter)
    {
        $path = tempnam(sys_get_temp_dir(),'flat-s3-');
        try {
            $bucket = $kind === 'bucket' ? '' : 'bucket';
            if ($kind === 'missing') { unlink($path); }
            if ($kind === 'unreadable') chmod($path,0000);
            $this->expectException(aws\s3\bad_param::class); $this->expectExceptionMessage($parameter);
            aws\s3::file_upload(new ClientDouble,$bucket,$path,'key');
        } finally { if (is_file($path)) { chmod($path,0600); unlink($path); } }
    }
    public function invalidFiles() { return [['bucket','bucket'], ['missing','file'], ['unreadable','file']]; }
    public function testS3ParameterMappingUrlsAndBodySetterUsingAnExplicitClientBoundary()
    {
        $client = new ClientDouble;
        // Widening field visibility is a legal subclass boundary; no SDK class is fabricated.
        $object = new PublicS3(['key'=>'/key','prefix'=>'/folder','client'=>$client]);
        $this->assertSame('\\AWS\\S3\\S3Client',$object->standardClientClass());
        $this->assertSame('https://provided-bucket.s3.amazonaws.com/folder/key',$object->get_url());
        $object->set_contentType('text/plain'); $object->set('0');
        $this->assertSame('0',$client->calls[0][1]['Body']);
        $this->assertSame('text/plain',$client->calls[0][1]['ContentType']);
        $this->assertNull($object->get()); // Existing read method is a stub.
        $this->assertSame('https://example.com/provided-bucket/key',(new CustomUrlS3(['key'=>'key']))->get_url());
    }
    public function testConstructorUploadAndFileSetterUseTheSameBoundary()
    {
        $path = tempnam(sys_get_temp_dir(),'flat-s3-'); file_put_contents($path,'data');
        try {
            $client = new ClientDouble;
            $object = new PublicS3(['key'=>'key','file'=>$path,'operation'=>'upload','bucket'=>'explicit','client'=>$client]);
            $this->assertSame('explicit',$client->calls[0][1]['Bucket']);
            $object->set(); $this->assertCount(4,$client->calls);
        } finally { unlink($path); }
    }
}
