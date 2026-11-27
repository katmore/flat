<?php
namespace flat\test\Unit\Core\Curl;
use flat\core\curl\request;
use flat\core\curl\config;
use flat\core\curl\response;
use flat\test\Support\HttpMock;
use PHPUnit\Framework\TestCase;

/**
 * flat\core\curl\response against a real HTTP server (the WireMock server of the test stack, through HttpMock): real cURL, real
 * requests, real failures. Each test says what its server answers.
 *
 * @group services
 * @group http
 */
class ResponseTest extends TestCase
{
    const PAGE = '<html><body><p>text</p></body></html>';
    private $http;
    private $libxml;
    protected function setUp(): void
    {
        $this->http = new HttpMock;
        $this->libxml = libxml_use_internal_errors(); libxml_clear_errors();
    }
    protected function tearDown(): void
    {
        $this->http->close();
        libxml_clear_errors(); libxml_use_internal_errors($this->libxml);
    }
    private function page($body = self::PAGE, array $response = [])
    {
        $this->http->stub('/page', ['body'=>$body] + $response);
    }
    private function config($path = '/page') { return new config(new request($this->http->url($path)), 'GET', 'UnitBot'); }
    private function cookieFiles() { return glob(sys_get_temp_dir().'/flat_curl_cookie_*'); }

    public function testDefaultRequestsSendTheFlatUserAgent()
    {
        $this->page();
        $response = new response(new config(new request($this->http->url('/page'))), []);
        $this->assertTrue($response->is_status_ok());
        $requests = $this->http->requests('/page');
        $this->assertCount(1, $requests);
        $this->assertSame('GET', $requests[0]['method']);
        $this->assertSame('flat/curl', $requests[0]['headers']['user-agent']);
    }
    public function testFacadeAcceptsUrlsAndConfigurationObjects()
    {
        $this->page();
        foreach ([$this->http->url('/page'), $this->config()] as $configuration) {
            $response = \flat\core\curl::exec($configuration, []);
            $this->assertInstanceOf(response::class, $response);
            $this->assertTrue($response->is_status_ok());
            $this->assertStringContainsString('<p>text</p>', $response->get_response_data());
        }
        $this->assertCount(2, $this->http->requests('/page'));
    }
    public function testResponseDataAndTheUserAgentOfTheConfiguration()
    {
        $this->page();
        $response = new response($this->config(), []);
        $this->assertTrue($response->is_status_ok());
        $this->assertSame(200, $response->status_code);
        $this->assertSame(self::PAGE, $response->get_response_data());
        $this->assertSame($this->http->url('/page'), $response->request->url_string);
        $this->assertSame('UnitBot', $this->http->requests('/page')[0]['headers']['user-agent']);
    }
    public function statuses() { return [[200, true], [201, true], [204, true], [300, false], [404, false], [500, false]]; }
    /** @dataProvider statuses */
    public function testTheServersStatusDecidesWhetherTheResponseIsOk($status, $ok)
    {
        $this->page('', ['status'=>$status]);
        $response = new response($this->config(), []);
        $this->assertSame($status, $response->status_code);
        $this->assertSame($ok, $response->is_status_ok());
    }
    public function testStatusZeroIsNotOk()
    {
        $this->page();
        $response = new response($this->config(), []);
        $response->status_code = 0;
        $this->assertFalse($response->is_status_ok());
    }
    public function testScalarUrlConfiguration()
    {
        $this->page();
        $this->assertTrue((new response($this->http->url('/page'), []))->is_status_ok());
    }
    public function testRequestDataIsSentAsTheBody()
    {
        $this->http->stub('/post', ['body'=>'ok'], 'POST');
        $response = new response(['url'=>$this->http->url('/post'), 'request_method'=>'POST', 'data'=>['value'=>1, 'text'=>'a b'], 'user_agent'=>'UnitBot'], []);
        $this->assertSame(200, $response->status_code);
        $requests = $this->http->requests('/post');
        $this->assertCount(1, $requests);
        $this->assertSame('POST', $requests[0]['method']);
        $this->assertSame('value=1&text=a+b', $requests[0]['body']);
        $this->assertSame('application/x-www-form-urlencoded', $requests[0]['headers']['content-type']);
        $this->assertSame('16', $requests[0]['headers']['content-length']);
    }
    public function testJsonRequestDataIsSentWithItsContentTypeAndMethod()
    {
        $this->http->stub('/put', ['body'=>'ok'], 'PUT');
        $request = new request($this->http->url('/put'), ['a'=>1], 'json');
        $response = new response(new config($request, 'PUT', 'UnitBot'), []);
        $this->assertSame(200, $response->status_code);
        $requests = $this->http->requests('/put');
        $this->assertSame('PUT', $requests[0]['method']);
        $this->assertSame('{"a":1}', $requests[0]['body']);
        $this->assertSame('application/json', $requests[0]['headers']['content-type']);
    }
    /** @dataProvider bodylessMethods */
    public function testExplicitMethodsAreSentWithoutABody($method)
    {
        $this->http->stub('/method', ['body'=>'server body'], $method);
        $response = new response(new config(new request($this->http->url('/method')), $method), []);
        $this->assertSame(200, $response->status_code);
        $this->assertSame($method, $this->http->requests('/method')[0]['method']);
        if ($method === 'HEAD') $this->assertSame('', $response->get_response_data());
    }
    public function bodylessMethods() { return [['DELETE'], ['HEAD'], ['PUT'], ['POST']]; }
    /** @dataProvider falsyBodies */
    public function testFalsyBodiesReachTheServer($value, $encoding, $expected, $method)
    {
        $sentMethod = $method ?? 'POST';
        $this->http->stub('/body', ['body'=>'ok'], $sentMethod);
        $request = new request($this->http->url('/body'), $value, $encoding, 'text/plain');
        $response = new response(new config($request, $method), []);
        $this->assertSame(200, $response->status_code);
        $received = $this->http->requests('/body')[0];
        $this->assertSame($sentMethod, $received['method']);
        $this->assertSame($expected, $received['body']);
        $this->assertSame($encoding === 'json' ? 'application/json' : 'text/plain', $received['headers']['content-type']);
    }
    public function falsyBodies()
    {
        return [[0, 'json', '0', 'POST'], ['0', 'none', '0', 'PUT'], ['', 'none', '', null],
            [false, 'json', 'false', null], ['', 'json', '""', null]];
    }
    public function testLazyDomIsInitializedOnceForOrdinaryHtml()
    {
        $this->page();
        $response = new response($this->config(), []);
        $dom = $response->get_dom();
        $this->assertInstanceOf(\DOMDocument::class, $dom);
        $this->assertSame('text', $dom->getElementsByTagName('p')->item(0)->textContent);
        $this->assertSame($dom, $response->get_dom());
    }
    public function testFiletimeIsTheServersLastModifiedTimeOrMinusOne()
    {
        $this->page(self::PAGE, ['headers'=>['Last-Modified'=>'Wed, 21 Oct 2015 07:28:00 GMT']]);
        $this->assertSame(1445412480, (new response($this->config(), []))->filetime);
        $this->page();
        $this->assertSame(-1, (new response($this->config(), []))->filetime);
    }
    public function testRedirectsAreFollowed()
    {
        $this->http->stub('/old', ['status'=>302, 'headers'=>['Location'=>$this->http->url('/page')]]);
        $this->page();
        $response = new response($this->config('/old'), []);
        $this->assertSame(200, $response->status_code);
        $this->assertSame(self::PAGE, $response->get_response_data());
    }
    public function testFilesAndTemporaryCleanup()
    {
        $this->page();
        $response = new response($this->config(), []); $file = $response->get_file();
        $this->assertSame($response->get_response_data(), file_get_contents($file));
        $this->assertSame($file, $response->get_file());
        $copy = tempnam(sys_get_temp_dir(), 'flat-copy-');
        try { $this->assertSame($copy, $response->get_file($copy)); $this->assertSame(file_get_contents($file), file_get_contents($copy)); }
        finally { unlink($copy); }
        unset($response); $this->assertFileDoesNotExist($file);
    }
    public function testCallerProvidedFilePersists()
    {
        $this->page();
        $path = tempnam(sys_get_temp_dir(), 'flat-save-');
        try {
            $response = new response($this->config(), []);
            $this->assertSame($path, $response->get_file($path));
            $this->assertSame($response->get_response_data(), file_get_contents($path));
            unset($response); $this->assertFileExists($path);
        } finally { unlink($path); }
    }
    public function testSaveFlagsAndArraySavePaths()
    {
        $this->page();
        $response = new response($this->config(), ['save','use_dom']);
        $file = $response->get_file(); $this->assertFileExists($file);
        $this->assertSame(self::PAGE, file_get_contents($file));
        $this->assertNull($response->get_response_data());
        unset($response); $this->assertFileDoesNotExist($file);
        foreach (['save', 'save_file'] as $key) {
            $path = tempnam(sys_get_temp_dir(), 'flat-save-');
            try {
                $response = new response(['url'=>$this->http->url('/page'), $key=>$path], []);
                $this->assertSame($path, $response->get_file());
                $this->assertSame(self::PAGE, file_get_contents($path));
                unset($response); $this->assertFileExists($path);
            } finally { unlink($path); }
        }
    }
    public function testCookiesTheServerSetsAreSavedAndThoseInTheFileAreSent()
    {
        $this->page('', ['headers'=>['Set-Cookie'=>'flat_session=abc123; Path=/']]);
        $path = tempnam(sys_get_temp_dir(), 'flat-cookie-');
        // Netscape cookie file format: domain, subdomains flag, path, secure flag, expiry (0: session), name, value.
        file_put_contents($path, "anet-mock\tFALSE\t/\tFALSE\t0\tprior\tfromfile\n");
        try {
            $response = new response($this->config(), ['accept_cookies','save_cookies','provide_cookies'], $path);
            $this->assertSame('prior=fromfile', $this->http->requests('/page')[0]['headers']['cookie']);
            unset($response);
            $this->assertStringContainsString("flat_session\tabc123", file_get_contents($path));
        } finally { unlink($path); }
    }
    public function testAutomaticCookieFileIsRemovedWithTheResponse()
    {
        $this->page();
        $before = $this->cookieFiles();
        $response = new response($this->config(), ['accept_cookies','save_cookies','provide_cookies']);
        $created = array_values(array_diff($this->cookieFiles(), $before));
        $this->assertCount(1, $created);
        unset($response);
        $this->assertFileDoesNotExist($created[0]);
    }
    /** @dataProvider invalidCookieFiles */
    public function testInvalidCookieFile($path)
    {
        $this->expectException(\flat\core\curl\exception\bad_param::class);
        new response($this->config(), ['accept_cookies'], $path);
    }
    public function invalidCookieFiles() { return [[123], ['/no/such/flat-cookie-path']]; }
    public function testDomAndXPathResults()
    {
        $this->page();
        $response = new response($this->config(), ['use_dom']);
        $this->assertInstanceOf(\DOMDocument::class, $response->get_dom());
        $this->assertSame('text', (string) $response->simplexpath('//p')[0]);
        $this->assertSame([], $response->simplexpath('//missing', false));
        $this->expectException(\flat\core\curl\exception\no_xpath_results::class);
        $response->simplexpath('//missing');
    }
    public function testInvalidXPathReportsQuery()
    {
        $this->page();
        $response = new response($this->config(), ['use_dom']);
        try { $response->simplexpath('//['); $this->fail('Expected XPath error'); }
        catch (\flat\core\curl\exception\xpath_error $error) {
            $this->assertSame('//[', $error->get_query()); $this->assertNotEmpty($error->get_details());
        }
    }
    /** @dataProvider libxmlModes */
    public function testDomAndXPathPreserveCallerLibxmlStateAndBufferedErrors($internal)
    {
        $this->page();
        libxml_use_internal_errors($internal);
        if ($internal) (new \DOMDocument)->loadXML('<prior>');
        $before = libxml_get_errors();
        $response = new response($this->config(), ['use_dom']);
        $this->assertSame('text', (string) $response->simplexpath('//p')[0]);
        $this->assertSame($internal, libxml_use_internal_errors());
        $this->assertEquals($before, libxml_get_errors());
        try { $response->simplexpath('//['); $this->fail('Expected XPath error'); }
        catch (\flat\core\curl\exception\xpath_error $error) {
            $this->assertStringContainsString('Invalid expression', $error->get_details());
            $this->assertSame($internal, libxml_use_internal_errors());
            if ($internal) {
                $after = libxml_get_errors();
                $this->assertEquals($before, array_slice($after, 0, count($before)));
                $this->assertStringContainsString('Invalid expression', end($after)->message);
            } else $this->assertSame([], libxml_get_errors());
        }
    }
    public function libxmlModes() { return [[false], [true]]; }
    /** @dataProvider libxmlModes */
    public function testXPathArgumentTypeErrorRestoresCallerLibxmlState($internal)
    {
        $this->page();
        libxml_use_internal_errors($internal);
        $response = new response($this->config(), ['use_dom']);
        try { $response->simplexpath([]); $this->fail('Expected XPath argument TypeError'); }
        catch (\TypeError $error) {
            $this->assertStringContainsString('SimpleXMLElement::xpath(): Argument #1 ($expression) must be of type string, array given', $error->getMessage());
            $this->assertSame($internal, libxml_use_internal_errors());
        }
    }
    public function testHtmlParserWarningReachesCallerAndLeavesReportingEnabled()
    {
        libxml_use_internal_errors(false);
        $this->page('<html><body><widget>text</widget></body></html>');
        set_error_handler(function ($level, $message, $file, $line) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        });
        try {
            try { new response($this->config(), ['use_dom']); $this->fail('Expected HTML parser warning'); }
            catch (\ErrorException $error) {
                $this->assertSame(E_WARNING, $error->getSeverity());
                $this->assertStringContainsString('DOMDocument::loadHTML(): Tag widget invalid', $error->getMessage());
                $this->assertFalse(libxml_use_internal_errors());
            }
        } finally { restore_error_handler(); }
    }
    public function testAServerThatIsNotListeningIsAConnectError()
    {
        $url = 'http://127.0.0.1:1/page';
        try { new response(new config(new request($url), 'GET', 'UnitBot'), []); $this->fail('Expected transfer error'); }
        catch (\flat\core\curl\exception\curlexec_error $error) {
            $this->assertSame(7, $error->get_curl_errno());
            $this->assertStringContainsString('127.0.0.1 port 1', $error->get_curl_error());
            $this->assertSame($url, $error->get_url());
        }
    }
    public function faults()
    {
        return [['CONNECTION_RESET_BY_PEER', 56, 'reset'], ['EMPTY_RESPONSE', 52, 'Empty reply'], ['MALFORMED_RESPONSE_CHUNK', 56, 'chunked']];
    }
    /** @dataProvider faults */
    public function testTransferFailureRetainsErrorDetails($fault, $errno, $text)
    {
        $this->http->stub('/page', ['fault'=>$fault]);
        try { new response($this->config(), []); $this->fail('Expected transfer error'); }
        catch (\flat\core\curl\exception\curlexec_error $error) {
            $this->assertSame($errno, $error->get_curl_errno());
            $this->assertStringContainsString($text, $error->get_curl_error());
            $this->assertSame($this->http->url('/page'), $error->get_url());
        }
    }
    public function testErrorAfterFileTransferStillFails()
    {
        $this->http->stub('/page', ['fault'=>'MALFORMED_RESPONSE_CHUNK']);
        $path = tempnam(sys_get_temp_dir(), 'flat-partial-');
        try {
            $this->expectException(\flat\core\curl\exception\curlexec_error::class);
            new response(['url'=>$this->http->url('/page'),'save_file'=>$path], []);
        } finally { unlink($path); }
    }
    /** @dataProvider robotsCases */
    public function testRobotsRules($body, $path, $code, $allowed)
    {
        $this->http->stubRoot('/robots.txt', ['body'=>$body,'status'=>$code]);
        $this->assertSame($allowed, response::is_robots_allowed($this->http->rootUrl($path), 'UnitBot'));
        $this->assertCount(1, $this->http->rootRequests('/robots.txt'));
    }
    public function robotsCases()
    {
        return [['', '/page',404,true], ['', '/page',200,true],
            ["User-agent: *\nDisallow:\n", '/page',200,true],
            ["User-agent: *\nAllow: /private\nDisallow:\n", '/private',200,true],
            ["User-agent: *\n# comment\nCrawl-delay: 5\nDisallow: /private # reason\n", '/private',200,false],
            ["User-agent: *\nDisallow: /\n", '',200,false],
            ["User-agent:*\nDisallow:/private\n", '/private',200,false],
            ["User-agent: OtherBot\nDisallow: /\nUser-agent: UnitBot\nDisallow: /private\nAllow: /private/public\n", '/private/public',200,true],
            ["User-agent: *\n\nDisallow: /private\n", '/private/a',200,false],
            ["User-agent: *\nDisallow: /tie\nAllow: /tie\nDisallow: /tie\n", '/tie',200,true],
            ["User-agent: *\nDisallow: /private\n", '/other',200,true]];
    }
    public function testRobotsCheckCanDenyOrAllowConstructor()
    {
        $this->page();
        $this->http->stubRoot('/robots.txt', ['body'=>'','status'=>404]);
        $this->assertTrue((new response($this->config(), ['use_robots']))->is_status_ok());
        $this->http->stubRoot('/robots.txt', ['body'=>"User-agent: *\nDisallow: /",'status'=>200]);
        $this->expectException(\flat\core\curl\exception\robots_denied::class);
        new response($this->config(), ['use_robots']);
    }
    public function testRobotsRulesForAnotherAgentIgnoreTheUserAgentLabel()
    {
        $this->http->stubRoot('/robots.txt', ['body'=>"User-agent: other\nDisallow: /",'status'=>200]);
        // An agent named "agent" must not match the label "User-agent" itself.
        $this->assertTrue(response::is_robots_allowed($this->http->rootUrl('/page'),'agent'));
        $this->assertFalse(response::is_robots_allowed($this->http->rootUrl('/page'),'other'));
    }
    public function testNoCookieFileIsCreatedUnlessCookiesAreAccepted()
    {
        $this->page();
        $before = $this->cookieFiles();
        $response = new response($this->config(), []);
        $this->assertSame($before, $this->cookieFiles());
        unset($response);
    }
    public function testFalsyTextBodyInitializesDomOnDemand()
    {
        $this->page('0');
        $response = new response($this->config(), []);
        $this->assertInstanceOf(\DOMDocument::class, $response->get_dom());
        $this->assertStringContainsString('0', $response->get_dom()->textContent);
    }
    public function testUnavailableSavePathRaisesSystemErrorAfterWarning()
    {
        $this->page();
        $path = sys_get_temp_dir().'/flat-missing-'.bin2hex(random_bytes(8)).'/response';
        $warnings = [];
        set_error_handler(function ($number, $message) use (&$warnings, $path) {
            if ($number === E_WARNING && strpos($message, $path) !== false) { $warnings[] = $message; return true; }
            return false;
        });
        try {
            try { new response(['url'=>$this->http->url('/page'),'save_file'=>$path], []); $this->fail('Expected file failure'); }
            catch (\flat\core\curl\exception\system_error $error) { $this->assertStringContainsString($path, $error->getMessage()); }
        } finally { restore_error_handler(); }
        $this->assertCount(1, $warnings);
    }
}
