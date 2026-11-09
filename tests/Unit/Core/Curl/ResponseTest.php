<?php
namespace flat\test\Unit\Core\Curl {
    require_once __DIR__.'/../../../Support/CurlDouble.php';
    use flat\core\curl\request;
    use flat\core\curl\config;
    use flat\core\curl\response;
    use PHPUnit\Framework\TestCase;
    class ResponseTest extends TestCase
    {
        private $libxml;
        protected function setUp(): void
        {
            CurlDouble::$active = true; CurlDouble::$handles = CurlDouble::$urls = CurlDouble::$options = CurlDouble::$responses = [];
            $this->libxml = libxml_use_internal_errors(); libxml_clear_errors();
        }
        protected function tearDown(): void
        {
            foreach (CurlDouble::$handles as $handle) if (is_resource($handle)) \curl_close($handle);
            CurlDouble::$handles = []; CurlDouble::$active = false;
            libxml_clear_errors(); libxml_use_internal_errors($this->libxml);
        }
        private function config($path = '/page') { return new config(new request('https://example.com'.$path), 'GET', 'UnitBot'); }
        public function testFacadeAcceptsUrlsAndConfigurationObjects()
        {
            foreach (['https://example.com/page',$this->config()] as $configuration) {
                $response = \flat\core\curl::exec($configuration,[]);
                $this->assertInstanceOf(response::class,$response);
                $this->assertTrue($response->is_status_ok());
                $this->assertStringContainsString('<p>text</p>',$response->get_response_data());
            }
        }
        public function testResponseDataStatusesAndTransferOptions()
        {
            $response = new response($this->config(), []);
            $this->assertTrue($response->is_status_ok());
            $this->assertStringContainsString('<p>text</p>', $response->get_response_data());
            $this->assertSame(123, $response->filetime);
            $this->assertSame('https://example.com/page', $response->request->url_string);
            foreach ([201=>true, 204=>true, 300=>false, 500=>false, 0=>false] as $code=>$expected) {
                $response->status_code = $code; $this->assertSame($expected, $response->is_status_ok());
            }
            $options = CurlDouble::$options[(int) CurlDouble::$handles[0]];
            $this->assertSame('UnitBot', $options[CURLOPT_USERAGENT]);
            $this->assertTrue($options[CURLOPT_RETURNTRANSFER]);
        }
        public function testArrayConfigurationAndScalarUrl()
        {
            $this->assertTrue((new response('https://example.com/page', []))->is_status_ok());
            $response = new response(['url'=>'https://example.com/page','request_method'=>'POST','data'=>['value'=>1],'user_agent'=>'UnitBot'], []);
            $this->assertTrue($response->is_status_ok());
            $options = CurlDouble::$options[(int) end(CurlDouble::$handles)];
            $this->assertSame('POST', $options[CURLOPT_CUSTOMREQUEST]);
            $this->assertContains('Content-Length: 7', $options[CURLOPT_HTTPHEADER]);
        }
        public function testFilesAndTemporaryCleanup()
        {
            $response = new response($this->config(), []); $file = $response->get_file();
            $this->assertSame($response->get_response_data(), file_get_contents($file));
            $this->assertSame($file, $response->get_file());
            $copy = tempnam(sys_get_temp_dir(), 'flat-copy-');
            try { $this->assertSame($copy, $response->get_file($copy)); $this->assertSame(file_get_contents($file), file_get_contents($copy)); }
            finally { unlink($copy); }
            unset($response); $this->assertFileNotExists($file);
        }
        public function testCallerProvidedFilePersists()
        {
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
            $response = new response($this->config(), ['save','use_dom']);
            $file = $response->get_file(); $this->assertFileExists($file);
            $this->assertNull($response->get_response_data());
            unset($response); $this->assertFileNotExists($file);
            foreach (['save', 'save_file'] as $key) {
                $path = tempnam(sys_get_temp_dir(), 'flat-save-');
                try {
                    $response = new response(['url'=>'https://example.com/page', $key=>$path], []);
                    $this->assertSame($path, $response->get_file());
                    $this->assertStringContainsString('<p>text</p>', file_get_contents($path));
                    unset($response); $this->assertFileExists($path);
                } finally { unlink($path); }
            }
        }
        public function testCookieOptionsAndAutomaticCookieCleanup()
        {
            $response = new response($this->config(), ['accept_cookies','save_cookies','provide_cookies']);
            $options = CurlDouble::$options[(int) end(CurlDouble::$handles)];
            $path = $options[CURLOPT_COOKIEJAR];
            $this->assertSame($path, $options[CURLOPT_COOKIEFILE]); $this->assertFileExists($path);
            unset($response); $this->assertFileNotExists($path);
            $path = tempnam(sys_get_temp_dir(), 'flat-cookie-');
            try { $response = new response($this->config(), ['accept_cookies'], $path); unset($response); $this->assertFileExists($path); }
            finally { unlink($path); }
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
            $response = new response($this->config(), ['use_dom']);
            $this->assertInstanceOf(\DOMDocument::class, $response->get_dom());
            $this->assertSame('text', (string) $response->simplexpath('//p')[0]);
            $this->assertSame([], $response->simplexpath('//missing', false));
            $this->expectException(\flat\core\curl\exception\no_xpath_results::class);
            $response->simplexpath('//missing');
        }
        public function testInvalidXPathReportsQuery()
        {
            $response = new response($this->config(), ['use_dom']);
            try { $response->simplexpath('//['); $this->fail('Expected XPath error'); }
            catch (\flat\core\curl\exception\xpath_error $error) {
                $this->assertSame('//[', $error->get_query()); $this->assertNotEmpty($error->get_details());
            }
        }
        public function testTransferFailureRetainsErrorDetails()
        {
            CurlDouble::$responses['https://example.com/page'] = ['fail'=>true,'errno'=>7];
            try { new response($this->config(), []); $this->fail('Expected transfer error'); }
            catch (\flat\core\curl\exception\curlexec_error $error) {
                $this->assertSame(7, $error->get_curl_errno());
                $this->assertSame('controlled transfer error', $error->get_curl_error());
                $this->assertSame('https://example.com/page', $error->get_url());
            }
        }
        public function testErrorAfterFileTransferStillFails()
        {
            CurlDouble::$responses['https://example.com/page'] = ['body'=>'partial','errno'=>7];
            $path = tempnam(sys_get_temp_dir(), 'flat-partial-');
            try {
                $this->expectException(\flat\core\curl\exception\curlexec_error::class);
                new response(['url'=>'https://example.com/page','save_file'=>$path], []);
            } finally { unlink($path); }
        }
        /** @dataProvider robotsCases */
        public function testRobotsRules($body, $path, $code, $allowed)
        {
            CurlDouble::$responses['https://example.com/robots.txt'] = ['body'=>$body,'code'=>$code];
            $this->assertSame($allowed, response::is_robots_allowed('https://example.com'.$path, 'UnitBot'));
        }
        public function robotsCases()
        {
            return [['', '/page',404,true], ['', '/page',200,true],
                ["User-agent: OtherBot\nDisallow: /\nUser-agent: UnitBot\nDisallow: /private\nAllow: /private/public\n", '/private/public',200,true],
                ["User-agent: *\n\nDisallow: /private\n", '/private/a',200,false],
                ["User-agent: *\nDisallow: /tie\nAllow: /tie\nDisallow: /tie\n", '/tie',200,true],
                ["User-agent: *\nDisallow: /private\n", '/other',200,true]];
        }
        public function testRobotsCheckCanDenyOrAllowConstructor()
        {
            CurlDouble::$responses['https://example.com/robots.txt'] = ['body'=>'','code'=>404];
            $this->assertTrue((new response($this->config(), ['use_robots']))->is_status_ok());
            CurlDouble::$responses['https://example.com/robots.txt'] = ['body'=>"User-agent: *\nDisallow: /",'code'=>200];
            $this->expectException(\flat\core\curl\exception\robots_denied::class);
            new response($this->config(), ['use_robots']);
        }

        public function testFalsyTextBodyInitializesDomOnDemand()
        {
            CurlDouble::$responses['https://example.com/page'] = ['body'=>'0','code'=>200];
            $response = new response($this->config(), []);
            $this->assertInstanceOf(\DOMDocument::class, $response->get_dom());
            $this->assertStringContainsString('0', $response->get_dom()->textContent);
        }
        /** @group known-defect */
        public function testCurrentMissingInfoExceptionClassFailure()
        {
            CurlDouble::$responses['https://example.com/page'] = ['body'=>'text','missing_info'=>true];
            $this->expectException(\Error::class);
            $this->expectExceptionMessage('flat\\core\\curl\\exception\\missing_curlinfo');
            new response($this->config(), []);
        }
        public function testUnavailableSavePathRaisesSystemErrorAfterWarning()
        {
            $path = sys_get_temp_dir().'/flat-missing-'.bin2hex(random_bytes(8)).'/response';
            $warnings = [];
            set_error_handler(function ($number, $message) use (&$warnings, $path) {
                if ($number === E_WARNING && strpos($message, $path) !== false) { $warnings[] = $message; return true; }
                return false;
            });
            try {
                try { new response(['url'=>'https://example.com/page','save_file'=>$path], []); $this->fail('Expected file failure'); }
                catch (\flat\core\curl\exception\system_error $error) { $this->assertStringContainsString($path, $error->getMessage()); }
            } finally { restore_error_handler(); }
            $this->assertCount(1, $warnings);
        }
    }
}
