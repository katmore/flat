<?php
namespace flat\test\Support;

/**
 * The S3 emulator of the test stack (Adobe's S3Mock, Compose service s3mock, address in FLAT_TEST_S3_ENDPOINT) and a real AWS SDK client
 * for it. Each instance creates its own buckets under random names and removes them, with their objects, in close(). The callers set
 * AWS_SUPPRESS_PHP_DEPRECATION_WARNING first (the approved exception in AGENTS.md): the SDK raises a deprecation notice when it builds a client.
 */
class S3Mock
{
    const key = 'local-key';
    const secret = 'local-secret';
    private $endpoint;
    private $client;
    private $buckets = [];

    public function __construct()
    {
        $this->endpoint = rtrim((string) getenv('FLAT_TEST_S3_ENDPOINT'), '/');
        if ($this->endpoint === '') {
            throw new \RuntimeException('FLAT_TEST_S3_ENDPOINT is not set: run these tests with docker/bin/test (all or services)');
        }
    }

    /** A real SDK client for the emulator. */
    public function client()
    {
        if ($this->client === null) {
            $this->waitUntilReady();
            $this->client = $this->newClient();
        }
        return $this->client;
    }

    /** Another client for the same emulator, for actions taken outside the code under test. */
    public function newClient()
    {
        return new \Aws\S3\S3Client([
            'version' => '2006-03-01', 'region' => 'us-east-1', 'retries' => 0,
            'endpoint' => $this->endpoint, 'use_path_style_endpoint' => true,
            'credentials' => ['key' => self::key, 'secret' => self::secret],
        ]);
    }

    /** Creates a bucket with a random name and returns the name. */
    public function bucket()
    {
        $name = 'flat-'.bin2hex(random_bytes(8));
        $this->client()->createBucket(['Bucket' => $name]);
        return $this->buckets[] = $name;
    }

    /** A bucket name that does not exist. */
    public function absentBucket()
    {
        return 'flat-absent-'.bin2hex(random_bytes(8));
    }

    /** The emulator starts in a few seconds and has no health check, so wait until it answers (cURL: a refused connection is no PHP warning). */
    private function waitUntilReady()
    {
        $deadline = microtime(true) + 60;
        do {
            $curl = curl_init($this->endpoint.'/');
            curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5]);
            curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE); curl_close($curl);
            if ($status >= 200 && $status < 500) return;
            usleep(250000);
        } while (microtime(true) < $deadline);
        throw new \RuntimeException('the S3 emulator at '.$this->endpoint.' did not answer within 60 seconds');
    }

    /** Removes the buckets this instance created, with their objects and unfinished uploads. */
    public function close()
    {
        foreach ($this->buckets as $bucket) {
            $client = $this->client();
            $client->deleteMatchingObjects($bucket, '', '/.*/');
            $uploads = $client->listMultipartUploads(['Bucket' => $bucket]);
            foreach ($uploads['Uploads'] ?? [] as $upload) {
                $client->abortMultipartUpload(['Bucket' => $bucket, 'Key' => $upload['Key'], 'UploadId' => $upload['UploadId']]);
            }
            $client->deleteBucket(['Bucket' => $bucket]);
        }
        $this->buckets = [];
    }
}
