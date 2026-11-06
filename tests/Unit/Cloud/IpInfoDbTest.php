<?php
namespace flat\test\Unit\Cloud;
require_once __DIR__.'/../../Support/CurlDouble.php';
use PHPUnit\Framework\TestCase;
use flat\cloud\ipinfodb;
use flat\test\Unit\Core\Curl\CurlDouble;
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
    protected function _get_api_key() { return 'local-test-key'; }
    protected function _get_mongo_crud() { return self::$cache; }
}
class IpInfoDbTest extends TestCase
{
    private $url;
    protected function setUp(): void
    {
        CurlDouble::$active = true;
        CurlDouble::$handles = CurlDouble::$urls = CurlDouble::$options = CurlDouble::$responses = [];
        IpLookupFixture::$cache = new IpCacheDouble;
        $this->url = ipinfodb::api_base_url.'?key=local-test-key&ip=192.0.2.1';
    }
    protected function tearDown(): void
    {
        foreach (CurlDouble::$handles as $handle) if (is_resource($handle)) \curl_close($handle);
        CurlDouble::$handles = []; CurlDouble::$active = false; IpLookupFixture::$cache = null;
    }
    public function testCacheHitUsesStoredRecordWithoutHttp()
    {
        $stored = new ipinfodb\result(['ip'=>'192.0.2.1','city'=>'cached city','created'=>date('c')]);
        IpLookupFixture::$cache->record = $stored;
        $lookup = new IpLookupFixture('192.0.2.1');
        $this->assertSame($stored, $lookup->result());
        $this->assertSame('cached city', $lookup->city);
        $this->assertSame('', $lookup->missing);
        $this->assertSame([], CurlDouble::$handles);
        $stored->created = null;
        $this->assertSame($stored, (new IpLookupFixture('192.0.2.1'))->result());
    }
    /** @dataProvider lookupCases */
    public function testMissingOrExpiredCacheFetchesMapsAndStoresResponse($expired, $raw, $location)
    {
        if ($expired) IpLookupFixture::$cache->record = new ipinfodb\result(['created'=>'2000-01-01']);
        CurlDouble::$responses[$this->url] = ['body'=>$raw,'code'=>200];
        $lookup = new IpLookupFixture('192.0.2.1');
        $this->assertSame($raw, $lookup->result()->raw);
        $this->assertSame($raw, $lookup->result()->description);
        $this->assertSame($location, $lookup->result()->location);
        $create = IpLookupFixture::$cache->calls[1];
        $this->assertSame('create', $create[0]);
        $this->assertSame($lookup->result(), $create[1]);
        $this->assertSame(['upsert'=>['ip'=>$lookup->result()->ip]], $create[2]);
        $this->assertSame([$this->url], array_values(CurlDouble::$urls));
    }
    public function lookupCases()
    {
        return [[false,'OK;;192.0.2.1;US;United States;Region;City;12345;1.2;3.4;-07:00','City, Region, US'],
            [true,'OK;;192.0.2.1;US;United States;Region;City;12345;1.2;3.4;-07:00','City, Region, US'],
            [false,'OK;;192.0.2.1;;;;;;;;',null]];
    }
    public function testHttpFailureCarriesStatusContext()
    {
        CurlDouble::$responses[$this->url] = ['body'=>'failure','code'=>503];
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
        CurlDouble::$responses[$this->url] = ['fail'=>true,'errno'=>7];
        try { new IpLookupFixture('192.0.2.1'); }
        catch (ipinfodb\api_error $error) {
            $this->assertSame('failure', $error->get_details());
            $this->assertSame(7, $error->get_data()->http_client_errno);
            $this->assertSame('controlled transfer error', $error->get_data()->http_client_error);
            $this->assertSame($this->url, $error->get_data()->api_url);
            return;
        }
        $this->fail('Expected transport failure');
    }
    public function testStaticLoadCurrentlyDoesNothing()
    {
        $this->assertNull(IpLookupFixture::load('192.0.2.1'));
        $this->assertSame([], IpLookupFixture::$cache->calls);
    }
}
