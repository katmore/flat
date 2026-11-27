<?php
namespace flat\test\Unit\Core\Curl;
use flat\test\Support\HttpMock;
use PHPUnit\Framework\TestCase;

/**
 * response.php throws missing_curlinfo when curl_getinfo() returns false for a finished transfer, which real cURL never does. The
 * test makes it happen with CurlInfoFailure, in a fresh PHP process: PHP remembers which function a call site resolved to, so an
 * override defined after other tests have run the call would not be seen.
 *
 * @group services
 * @group http
 */
class ResponseTransferInfoTest extends TestCase
{
    use \flat\test\Support\PhpSubprocess;
    private $http;
    protected function setUp(): void { $this->http = new HttpMock; }
    protected function tearDown(): void { $this->http->close(); }

    public function testMissingTransferInfoRaisesItsDeclaredException()
    {
        $this->http->stub('/page', ['body'=>'text']);
        $body = '\flat\test\Support\CurlInfoFailure::$active = true;'
            .' try { new \flat\core\curl\response(new \flat\core\curl\config(new \flat\core\curl\request('.var_export($this->http->url('/page'), true).'), "GET", "UnitBot"), []); echo "no exception"; }'
            .' catch (\Throwable $e) { echo get_class($e), ": ", $e->getMessage(); }';
        list($status, $out, $err) = $this->subprocess($body);
        $this->assertSame(0, $status); $this->assertSame('', $err);
        $this->assertSame('flat\core\curl\exception\missing_curlinfo: curl_getinfo returned no transfer information', $out);
    }
}
