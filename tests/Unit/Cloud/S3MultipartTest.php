<?php
namespace flat\test\Unit\Cloud {
    class MultipartFileMetadata
    {
        public static $path;
        public static $size;
    }
}
namespace flat\cloud\aws {
    function filesize($path)
    {
        if (\flat\test\Unit\Cloud\MultipartFileMetadata::$path !== null && $path === \flat\test\Unit\Cloud\MultipartFileMetadata::$path) return \flat\test\Unit\Cloud\MultipartFileMetadata::$size;
        return \filesize($path);
    }
}
namespace flat\test\Unit\Cloud {
    use PHPUnit\Framework\TestCase;
    use flat\cloud\aws\s3;
    class S3MultipartTest extends TestCase
    {
        private $file;
        private $commands = [];
        private $deprecationSetting;
        protected function setUp(): void
        {
            $this->deprecationSetting = getenv('AWS_SUPPRESS_PHP_DEPRECATION_WARNING');
            putenv('AWS_SUPPRESS_PHP_DEPRECATION_WARNING=true');
            $this->file = tempnam(sys_get_temp_dir(),'flat-multipart-'); file_put_contents($this->file,'local multipart body');
            // Control only the framework routing boundary. The actual SDK reads
            // the small real file, so no huge sparse fixture or network is needed.
            MultipartFileMetadata::$path = $this->file; MultipartFileMetadata::$size = s3::multipart_min_size;
        }
        protected function tearDown(): void
        {
            MultipartFileMetadata::$path = MultipartFileMetadata::$size = null; unlink($this->file);
            putenv($this->deprecationSetting === false ? 'AWS_SUPPRESS_PHP_DEPRECATION_WARNING' : 'AWS_SUPPRESS_PHP_DEPRECATION_WARNING='.$this->deprecationSetting);
        }
        private function client(array $replies)
        {
            $mock = new \Aws\MockHandler($replies);
            return new \Aws\S3\S3Client(['version'=>'2006-03-01','region'=>'us-east-1','retries'=>0,
                'credentials'=>['key'=>'local-key','secret'=>'local-secret'],
                'handler'=>function ($command,$request) use ($mock) {
                    $this->commands[] = ['name'=>$command->getName(),'params'=>$command->toArray()];
                    return $mock($command,$request);
                }]);
        }
        private function names() { return array_column($this->commands,'name'); }
        public function testThresholdRoutesToRealSdkMultipartWorkflow()
        {
            $client = $this->client([new \Aws\Result(['UploadId'=>'local-upload']),new \Aws\Result(['ETag'=>'local-part']),new \Aws\Result(['ETag'=>'local-object'])]);
            $this->assertNull(s3::file_upload($client,'local-bucket',$this->file,'/object','/prefix'));
            $this->assertSame(['CreateMultipartUpload','UploadPart','CompleteMultipartUpload'],$this->names());
            foreach ($this->commands as $command) {
                $this->assertSame('local-bucket',$command['params']['Bucket']);
                $this->assertSame('prefix/object',$command['params']['Key']);
            }
        }
        public function testMultipartUploadKeepsTheContentTypeAndThresholdStaysBelowTheSinglePutLimit()
        {
            $this->assertSame(100 * 1024 * 1024, s3::multipart_min_size);
            $this->assertLessThan(5 * 1000 * 1000 * 1000, s3::multipart_min_size);
            $client = $this->client([new \Aws\Result(['UploadId'=>'local-upload']),new \Aws\Result(['ETag'=>'local-part']),new \Aws\Result(['ETag'=>'local-object'])]);
            s3::file_upload($client,'local-bucket',$this->file,'object','','video/mp4');
            $this->assertSame('video/mp4',$this->commands[0]['params']['ContentType']);
            $this->assertArrayNotHasKey('ContentType',$this->commands[1]['params']);
        }
        public function testPartFailureAbortsTheStartedUploadAndWrapsSdkFailure()
        {
            $failure = new \Aws\S3\Exception\S3Exception('local rejection',new \Aws\Command('UploadPart'),['code'=>'AccessDenied']);
            $client = $this->client([new \Aws\Result(['UploadId'=>'local-upload']),$failure,new \Aws\Result]);
            try { s3::file_upload($client,'local-bucket',$this->file,'object'); }
            catch (s3\upload_failure $error) {
                $this->assertInstanceOf(\Aws\Exception\MultipartUploadException::class,$error->get_exception());
                $this->assertSame(['CreateMultipartUpload','UploadPart','AbortMultipartUpload'],$this->names());
                $this->assertSame('local-upload',$this->commands[2]['params']['UploadId']);
                return;
            }
            $this->fail('Expected wrapped multipart failure');
        }
        public function testInitiationFailureDoesNotAttemptAbortWithoutAnUploadId()
        {
            $failure = new \Aws\S3\Exception\S3Exception('local rejection',new \Aws\Command('CreateMultipartUpload'),['code'=>'AccessDenied']);
            $client = $this->client([$failure]);
            try { s3::file_upload($client,'local-bucket',$this->file,'object'); }
            catch (s3\upload_failure $error) {
                $this->assertInstanceOf(\Aws\Exception\MultipartUploadException::class,$error->get_exception());
                $this->assertSame(['CreateMultipartUpload'],$this->names());
                return;
            }
            $this->fail('Expected wrapped initiation failure');
        }
    }
}
