<?php
namespace flat\test\Unit\Cloud;
use PHPUnit\Framework\TestCase;
use flat\cloud\aws;
use flat\cloud\aws\s3;
class UploadingS3 extends aws\s3 implements aws\s3\bucket_provider
{
    public static $useBucket;
    public static $useClient;
    public function get_s3_bucket() { return self::$useBucket; }
    public function get_aws_client() { return self::$useClient; }
}
/**
 * flat\cloud\aws\s3 against a real S3 emulator (Adobe's S3Mock) with the real AWS SDK: single uploads, the multipart upload of a file of
 * the threshold size, and the failures the emulator itself reports.
 *
 * @group services
 * @group s3
 */
class S3MultipartTest extends TestCase
{
    private $mock;
    private $files = [];
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
        $this->mock = new \flat\test\Support\S3Mock;
    }
    protected function tearDown(): void
    {
        $this->mock->close();
        foreach ($this->files as $file) { if (is_file($file)) { chmod($file,0600); unlink($file); } }
        putenv($this->deprecationSetting === false ? 'AWS_SUPPRESS_PHP_DEPRECATION_WARNING' : 'AWS_SUPPRESS_PHP_DEPRECATION_WARNING='.$this->deprecationSetting);
    }
    private function file($contents)
    {
        $path = tempnam(sys_get_temp_dir(),'flat-s3-'); file_put_contents($path,$contents);
        return $this->files[] = $path;
    }
    /** A file of the multipart threshold size that takes no disk space: the SDK reads its zeros like any other bytes. */
    private function thresholdFile()
    {
        $path = $this->file(''); $handle = fopen($path,'r+'); ftruncate($handle,s3::multipart_min_size); fclose($handle);
        $this->assertSame(s3::multipart_min_size,filesize($path));
        return $path;
    }
    /** @dataProvider prefixes */
    public function testBodyUploadCanonicalizesKeysAndWaitsForTheUploadedObject($prefix,$key,$expected)
    {
        $client = $this->mock->client(); $bucket = $this->mock->bucket();
        s3::body_upload($client,$bucket,'body',$key,$prefix,'text/plain');
        $object = $client->getObject(['Bucket'=>$bucket,'Key'=>$expected]);
        $this->assertSame('body',(string) $object['Body']); $this->assertSame('text/plain',$object['ContentType']);
        $this->assertSame([$expected],array_column($client->listObjects(['Bucket'=>$bucket])['Contents'],'Key'));
    }
    public function prefixes() { return [[null,'key','key'], ['folder','/key','folder/key'], ['/folder/','/key','folder/key'], ['/','key','key'], [42,'key','key']]; }
    public function testSmallFileUploadUsesFileAndOptionalContentType()
    {
        $client = $this->mock->client(); $bucket = $this->mock->bucket(); $path = $this->file('data');
        s3::file_upload($client,$bucket,$path,'plain');
        s3::file_upload($client,$bucket,$path,'typed',null,'application/octet-stream');
        $this->assertSame('data',(string) $client->getObject(['Bucket'=>$bucket,'Key'=>'plain'])['Body']);
        $this->assertSame('data',(string) $client->getObject(['Bucket'=>$bucket,'Key'=>'typed'])['Body']);
        $this->assertSame('application/octet-stream',$client->headObject(['Bucket'=>$bucket,'Key'=>'typed'])['ContentType']);
    }
    public function testS3ObjectMapsItsParametersAndUploadsThroughTheExplicitClient()
    {
        $client = UploadingS3::$useClient = $this->mock->client(); UploadingS3::$useBucket = $this->mock->bucket(); $path = $this->file('from a file');
        $object = new UploadingS3(['key'=>'/key','prefix'=>'/folder','client'=>$client]);
        $object->set_contentType('text/plain'); $object->set('0');
        $this->assertSame('0',(string) $client->getObject(['Bucket'=>UploadingS3::$useBucket,'Key'=>'folder/key'])['Body']);
        $this->assertSame('text/plain',$client->headObject(['Bucket'=>UploadingS3::$useBucket,'Key'=>'folder/key'])['ContentType']);
        $other = $this->mock->bucket();
        new UploadingS3(['key'=>'upload','file'=>$path,'operation'=>'upload','bucket'=>$other,'client'=>$client]);
        $this->assertSame('from a file',(string) $client->getObject(['Bucket'=>$other,'Key'=>'upload'])['Body']);
        $again = new UploadingS3(['key'=>'again','file'=>$path,'bucket'=>$other,'client'=>$client]); $again->set();
        $this->assertSame('from a file',(string) $client->getObject(['Bucket'=>$other,'Key'=>'again'])['Body']);
    }
    public function testThresholdFileIsUploadedInPartsAndKeepsTheContentType()
    {
        $this->assertSame(100 * 1024 * 1024, s3::multipart_min_size);
        $this->assertLessThan(5 * 1000 * 1000 * 1000, s3::multipart_min_size);
        $client = $this->mock->client(); $bucket = $this->mock->bucket();
        $this->assertNull(s3::file_upload($client,$bucket,$this->thresholdFile(),'/object','/prefix','video/mp4'));
        $head = $client->headObject(['Bucket'=>$bucket,'Key'=>'prefix/object']);
        $this->assertSame(s3::multipart_min_size,(int) $head['ContentLength']);
        $this->assertSame('video/mp4',$head['ContentType']);
        $this->assertMatchesRegularExpression('/^"[0-9a-f]{32}-4"$/',$head['ETag']); // the ETag of a multipart object ends in its number of parts
        $this->assertSame([],$client->listMultipartUploads(['Bucket'=>$bucket])['Uploads'] ?? []);
    }
    public function testInitiationFailureDoesNotAttemptAbortWithoutAnUploadId()
    {
        $client = $this->mock->client();
        try { s3::file_upload($client,$this->mock->absentBucket(),$this->thresholdFile(),'object'); }
        catch (s3\upload_failure $error) {
            $this->assertInstanceOf(\Aws\Exception\MultipartUploadException::class,$error->get_exception());
            $this->assertNull($error->get_exception()->getState()->getId()['UploadId']); // no upload was started, so there was nothing to abort
            $this->assertSame('NoSuchBucket',$error->get_exception()->getPrevious()->getAwsErrorCode());
            return;
        }
        $this->fail('Expected wrapped initiation failure');
    }
    public function testPartFailureAbortsTheStartedUploadAndWrapsSdkFailure()
    {
        // The first part is sent with a part number S3 does not allow (the highest is 10,000), so the emulator refuses it.
        $client = $this->mock->client(); $bucket = $this->mock->bucket(); $commands = [];
        $client->getHandlerList()->prependBuild(\Aws\Middleware::tap(function ($command) use (&$commands) {
            $commands[] = $command->getName();
            if ($command->getName() === 'UploadPart' && count(array_keys($commands,'UploadPart')) === 1) $command['PartNumber'] = 10001;
        }));
        try { s3::file_upload($client,$bucket,$this->thresholdFile(),'object'); }
        catch (s3\upload_failure $error) {
            $this->assertInstanceOf(\Aws\Exception\MultipartUploadException::class,$error->get_exception());
            $this->assertSame(['CreateMultipartUpload','UploadPart','UploadPart','UploadPart','UploadPart','AbortMultipartUpload'],$commands);
            $this->assertSame([],$client->listMultipartUploads(['Bucket'=>$bucket])['Uploads'] ?? []); // the abort took effect
            $this->assertSame([],$client->listObjects(['Bucket'=>$bucket])['Contents'] ?? []);
            $this->assertStringContainsString('Part number must be an integer between 1 and 10000',$error->get_exception()->getMessage()); // the emulator's own answer
            return;
        }
        $this->fail('Expected wrapped part failure');
    }
    public function testAFailedAbortDoesNotReplaceTheUploadFailure()
    {
        // KD-116: the upload is aborted from outside just before its first part is sent, so flat's own abort fails too (NoSuchUpload).
        $client = $this->mock->client(); $bucket = $this->mock->bucket(); $outside = $this->mock->newClient(); $parts = 0; $commands = [];
        $client->getHandlerList()->appendSign(\Aws\Middleware::tap(function ($command) use ($outside,&$parts,&$commands) {
            $commands[] = $command->getName();
            if ($command->getName() === 'UploadPart' && ++$parts === 1) {
                $outside->abortMultipartUpload(['Bucket'=>$command['Bucket'],'Key'=>$command['Key'],'UploadId'=>$command['UploadId']]);
            }
        }));
        try { s3::file_upload($client,$bucket,$this->thresholdFile(),'object'); }
        catch (s3\upload_failure $error) {
            $this->assertInstanceOf(\Aws\Exception\MultipartUploadException::class,$error->get_exception());
            $this->assertStringContainsString('NoSuchUpload',$error->get_exception()->getMessage()); // the original failure
            $this->assertSame('AbortMultipartUpload',end($commands)); // the abort was tried
            return;
        }
        $this->fail('Expected the upload failure');
    }
}
