<?php
namespace flat\test\Unit\Cloud;
use PHPUnit\Framework\TestCase;
use flat\cloud\aws;
/** The client class of these fixtures is a plain PHP class: flat\cloud\aws only checks that a client is an instance of the class it names. */
class AwsFixture extends aws
{
    protected function _get_client_class() { return \ArrayObject::class; }
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
    public function get_s3_bucket() { return 'provided-bucket'; }
    public function get_aws_client() { throw new \LogicException('these tests make no request'); }
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
        $fixture = new AwsFixture; $client = new \ArrayObject;
        $fixture->supply($client); $this->assertSame($client,$fixture->client());
        $this->expectException(aws\bad_client::class); $fixture->supply(new \stdClass);
    }
    public function testProviderClientIsResolvedOnce()
    {
        $fixture = new ProviderFixture; $fixture->provided = new \ArrayObject;
        $this->assertSame($fixture->provided,$fixture->client());
        $this->assertSame($fixture->provided,$fixture->client()); $this->assertSame(1,$fixture->lookups);
    }
    public function testMissingClientReportsUnavailable()
    {
        $this->expectException(aws\client_unavailable::class); (new AwsFixture)->client();
    }
    /** @dataProvider invalidFiles */
    public function testFileUploadRejectsInvalidPathsBeforeAnyRequest($kind,$parameter)
    {
        // The checks come first, so no client is needed: any value would do, and a client that was used would fail the test.
        $path = tempnam(sys_get_temp_dir(),'flat-s3-');
        try {
            $bucket = $kind === 'bucket' ? '' : 'bucket';
            if ($kind === 'missing') { unlink($path); }
            if ($kind === 'unreadable') chmod($path,0000);
            $this->expectException(aws\s3\bad_param::class); $this->expectExceptionMessage($parameter);
            aws\s3::file_upload(null,$bucket,$path,'key');
        } finally { if (is_file($path)) { chmod($path,0600); unlink($path); } }
    }
    public function invalidFiles() { return [['bucket','bucket'], ['missing','file'], ['unreadable','file']]; }
    public function testS3UrlsAndTheStandardClientClass()
    {
        $object = new PublicS3(['key'=>'/key','prefix'=>'/folder']);
        $this->assertSame('\\AWS\\S3\\S3Client',$object->standardClientClass());
        $this->assertSame('https://provided-bucket.s3.amazonaws.com/folder/key',$object->get_url());
        $this->assertNull($object->get()); // Existing read method is a stub.
        $this->assertSame('https://example.com/provided-bucket/key',(new CustomUrlS3(['key'=>'key']))->get_url());
    }
    /**
     * @group known-defect
     * KD-115: \flat\core\mapper::args_to_properties() gives a parameter that was left out of an array of named arguments the value at the same position
     * of the array. Here "prefix" (the third parameter) takes the third value, the operation.
     */
    public function testAParameterLeftOutTakesTheValueAtItsPosition()
    {
        $object = new PublicS3(['key'=>'key','file'=>'file','operation'=>'not-an-operation']);
        $this->assertSame('https://provided-bucket.s3.amazonaws.com/not-an-operation/key',$object->get_url());
    }
}
