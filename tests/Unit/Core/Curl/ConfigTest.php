<?php
namespace flat\test\Unit\Core\Curl;
use flat\core\curl\config;
use flat\core\curl\request;
use PHPUnit\Framework\TestCase;
class ConfigTest extends TestCase
{
    private function request() { return new request('https://example.com/path'); }
    public function testExplicitConfigurationAndDefaults()
    {
        $request = $this->request();
        $config = new config($request, 'POST', 'custom-agent', 'https://ref.example/', 0, null, 12, 3);
        $this->assertSame($request, $config->request);
        $this->assertSame('POST', $config->request_method); $this->assertSame('custom-agent', $config->user_agent);
        $this->assertSame('https://ref.example/', $config->referrer);
        $this->assertSame(12, $config->timeout); $this->assertSame(3, $config->connect_timeout);
        $default = new config($request);
        $this->assertSame(300, $default->timeout); $this->assertSame(5, $default->connect_timeout);
        // Text defaults are kept; the request method stays unset so curl picks
        // GET without a body and POST with one.
        $this->assertSame('flat/curl', $default->user_agent); $this->assertNull($default->request_method);
    }
    public function testChromeSelectionProvidesTheChromeUserAgent()
    {
        $config = new config($this->request(), null, config::user_agent_chrome);
        $this->assertSame(str_replace('%chrome_version%', config::chrome_latest_version_str, config::chrome_user_agent_str), $config->user_agent);
        $this->assertStringContainsString('Chrome/', $config->user_agent);
    }
    /** @dataProvider invalidSettings */
    public function testInvalidSettingsAreRejected(array $args)
    {
        $this->expectException(\flat\core\curl\exception\bad_param::class);
        new config($this->request(), ...$args);
    }
    public function invalidSettings()
    {
        return [[[12]], [[null, 12]], [[null, null, 12]], [[null, null, null, null, 12]],
            [[null, null, null, null, null, '12']], [[null, null, null, '12']], [[null, null, null, null, null, null, '3']]];
    }
    public function testCachingUsesWritableTemporaryDirectoryByDefault()
    {
        $config = new config($this->request(), 'GET', null, null, 60);
        $this->assertSame(sys_get_temp_dir(), $config->cache_dir);
        $this->assertSame(60, $config->cache_ttl);
    }
    public function testUnwritableCacheDirectoryIsRejected()
    {
        $this->expectException(\flat\core\curl\exception\bad_param::class);
        new config($this->request(), 'GET', null, null, 60, sys_get_temp_dir().'/flat-missing-cache-'.bin2hex(random_bytes(8)));
    }
}
