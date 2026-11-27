<?php
namespace flat\test\Unit\Cloud;
use PHPUnit\Framework\TestCase;
use flat\cloud\ipinfodb;
use flat\test\Support\HttpMock;
class IpCacheDouble
{
    public $record;
    public $calls = [];
    public function read($params)
    {
        $this->calls[] = ['read', $params];
        if (!$this->record) throw new \flat\db\driver\mongo\record\not_found;
        return $this->record;
    }
    public function create($data, $options) { $this->calls[] = ['create', $data, $options]; }
}
class IpLookupFixture extends ipinfodb
{
    public static $cache;
    public static $baseUrl;
    protected function _get_api_base_url() { return self::$baseUrl; }
    protected function _get_api_key() { return 'local-test-key'; }
    protected function _get_mongo_crud() { return self::$cache; }
}
class IpLookupDefaultFixture extends ipinfodb
{
    protected function _get_api_key() { return 'local-test-key'; }
    protected function _get_mongo_crud() { return null; }
}
/**
 * The IP lookup against a real HTTP server (the WireMock server of the test stack, through HttpMock) in place of api.ipinfodb.com.
 *
 * @group services
 * @group http
 */
class IpInfoDbTest extends TestCase
{
    private $http;
    private $url;
    private $query = '?key=local-test-key&ip=192.0.2.1';
    protected function setUp(): void
    {
        $this->http = new HttpMock;
        IpLookupFixture::$cache = new IpCacheDouble;
        IpLookupFixture::$baseUrl = $this->http->url('/ip-city');
        $this->url = IpLookupFixture::$baseUrl.$this->query;
    }
    protected function tearDown(): void
    {
        $this->http->close(); IpLookupFixture::$cache = null; IpLookupFixture::$baseUrl = null;
    }
    public function testCacheHitUsesStoredRecordWithoutHttp()
    {
        $stored = new ipinfodb\result(['ip'=>'192.0.2.1','city'=>'cached city','created'=>date('c')]);
        IpLookupFixture::$cache->record = $stored;
        $lookup = new IpLookupFixture('192.0.2.1');
        $this->assertSame($stored, $lookup->result());
        $this->assertSame('cached city', $lookup->city);
        $this->assertSame('', $lookup->missing);
        $this->assertSame([], $this->http->requests('/ip-city'));
        $stored->created = null;
        $this->assertSame($stored, (new IpLookupFixture('192.0.2.1'))->result());
    }
    public function testCachedRecordExpiresExactlyAtItsTimeToLive()
    {
        $raw = 'OK;;192.0.2.1;US;United States;Region;City;12345;1.2;3.4;-07:00';
        // Younger than the ttl: served from the cache, no HTTP.
        IpLookupFixture::$cache->record = new ipinfodb\result(['ip'=>'192.0.2.1','created'=>date('c',time() - ipinfodb::location_ttl + 30)]);
        new IpLookupFixture('192.0.2.1');
        $this->assertSame([], $this->http->requests('/ip-city'));
        // Exactly the ttl old: refreshed. (A second ticking mid-check only makes it older,
        // so retry until the whole check fits in one second.)
        $this->http->stub('/ip-city', ['body'=>$raw]);
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $asked = count($this->http->requests('/ip-city'));
            $now = time();
            IpLookupFixture::$cache->record = new ipinfodb\result(['ip'=>'192.0.2.1','created'=>date('c',$now - ipinfodb::location_ttl)]);
            new IpLookupFixture('192.0.2.1');
            if (time() === $now) { $this->assertCount($asked + 1, $this->http->requests('/ip-city')); return; }
        }
        $this->fail('the clock advanced during every attempt');
    }
    /** @dataProvider lookupCases */
    public function testMissingOrExpiredCacheFetchesMapsAndStoresResponse($expired, $raw, $location)
    {
        if ($expired) IpLookupFixture::$cache->record = new ipinfodb\result(['created'=>'2000-01-01']);
        $this->http->stub('/ip-city', ['body'=>$raw]);
        $lookup = new IpLookupFixture('192.0.2.1');
        $this->assertSame($raw, $lookup->result()->raw);
        $this->assertSame($raw, $lookup->result()->description);
        $this->assertSame($location, $lookup->result()->location);
        $create = IpLookupFixture::$cache->calls[1];
        $this->assertSame('create', $create[0]);
        $this->assertSame($lookup->result(), $create[1]);
        $this->assertSame(['upsert'=>['ip'=>$lookup->result()->ip]], $create[2]);
        $requests = $this->http->requests('/ip-city');
        $this->assertCount(1, $requests);
        $this->assertSame($this->http->path('/ip-city').$this->query, $requests[0]['url']);
    }
    public function lookupCases()
    {
        return [[false,'OK;;192.0.2.1;US;United States;Region;City;12345;1.2;3.4;-07:00','City, Region, US'],
            [true,'OK;;192.0.2.1;US;United States;Region;City;12345;1.2;3.4;-07:00','City, Region, US'],
            [false,'OK;;192.0.2.1;;;;;;;;',null]];
    }
    public function testHttpFailureCarriesStatusContext()
    {
        $this->http->stub('/ip-city', ['body'=>'failure','status'=>503]);
        try { new IpLookupFixture('192.0.2.1'); }
        catch (ipinfodb\api_error $error) {
            $this->assertSame('status not ok', $error->get_details());
            $this->assertSame(503, $error->get_data()->status_code);
            $this->assertCount(1, IpLookupFixture::$cache->calls);
            return;
        }
        $this->fail('Expected status failure');
    }
    public function testTransportFailureCarriesCurlContext()
    {
        $this->http->stub('/ip-city', ['fault'=>'CONNECTION_RESET_BY_PEER']);
        try { new IpLookupFixture('192.0.2.1'); }
        catch (ipinfodb\api_error $error) {
            $this->assertSame('failure', $error->get_details());
            $this->assertSame(56, $error->get_data()->http_client_errno);
            $this->assertStringContainsString('reset', $error->get_data()->http_client_error);
            $this->assertSame($this->url, $error->get_data()->api_url);
            return;
        }
        $this->fail('Expected transport failure');
    }
    public function testTheApiBaseUrlDefaultsToTheRealApi()
    {
        $lookup = (new \ReflectionClass(IpLookupDefaultFixture::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod(IpLookupDefaultFixture::class, '_get_api_base_url');
        $method->setAccessible(true);
        $this->assertSame('https://api.ipinfodb.com/v3/ip-city', $method->invoke($lookup));
    }
    public function testRetiredStaticLoadFailsClearlyWithoutTouchingTheCache()
    {
        try {
            IpLookupFixture::load('192.0.2.1');
            $this->fail('load() should be retired');
        } catch (\BadMethodCallException $e) {
            $this->assertStringContainsString('construct the lookup class and call result()', $e->getMessage());
        }
        $this->assertSame([], IpLookupFixture::$cache->calls);
    }
}
