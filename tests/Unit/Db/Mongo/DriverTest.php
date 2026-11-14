<?php
namespace flat\test\Unit\Db\Mongo;
use PHPUnit\Framework\TestCase;
use flat\db\driver\mongo;
class NoContextDriver extends mongo
{
    public function connect(array $param=null) {} // Pure context validation has no connection requirement.
}
class TrivialDriver extends NoContextDriver implements mongo\client\trivial_connection
{
    public function get_client_host() { return 'localhost'; }
    public function get_client_port() { return 1234; }
}
class OptionDriver extends NoContextDriver implements mongo\client\options, mongo\client\driver_options
{
    public $uriOptions = ['connect'=>false,'serverSelectionTimeoutMS'=>10];
    public $driverOptions = ['context'=>'fixture'];
    public function get_client_options() { return $this->uriOptions; }
    public function get_client_driver_options() { return $this->driverOptions; }
}
class BadDatabase extends NoContextDriver implements mongo\db\explicit { public function get_client_db() { return null; } }
class NameOnlyCollection extends NoContextDriver implements mongo\collection\explicit
{
    public function get_mongo_collection() { return 'records'; }
}
class NameOnlyShortCollection extends NoContextDriver implements mongo\collection\shortname {}
class AffectedDriver extends NoContextDriver
{
    public $reply;
    public function command(array $param=null) { return $this->reply; }
}
class InvalidCollectionDatabaseDouble
{
    public function selectCollection($name) { return new \stdClass; }
}
class InvalidCollectionDriver extends NoContextDriver
{
    public function get_db($name = '', \MongoClient $client = null) { return new InvalidCollectionDatabaseDouble; }
}
class DriverTest extends TestCase
{
    private $saved = [];
    protected function setUp(): void
    {
        foreach (['_client','_client_ping'] as $name) { $p = new \ReflectionProperty(mongo::class,$name); $p->setAccessible(true); $this->saved[$name]=$p->getValue(); $p->setValue(null,[]); }
    }
    protected function tearDown(): void
    {
        foreach ($this->saved as $name=>$value) { $p = new \ReflectionProperty(mongo::class,$name); $p->setAccessible(true); $p->setValue(null,$value); }
    }
    public function testUriAndOptionsDerivationDoesNotNeedADatabase()
    {
        $plain = new NoContextDriver;
        $this->assertSame('',$plain->get_uri());
        $this->assertSame('mongodb://localhost:1234',(new TrivialDriver)->get_uri());
        $this->assertSame(['connect'=>true],$plain->get_uri_options());
        $this->assertSame(['custom'=>true],$plain->get_uri_options(['client_options'=>['custom'=>true]]));
        $this->assertSame([],$plain->get_driver_options());
        $this->assertSame(['custom'=>true],$plain->get_driver_options(['driver_options'=>['custom'=>true]]));
        $provided = new OptionDriver;
        $this->assertSame(['connect'=>false,'serverSelectionTimeoutMS'=>10],$provided->get_uri_options());
        $this->assertSame(['context'=>'fixture'],$provided->get_driver_options());
        $provided->uriOptions = null; $provided->driverOptions = null;
        $this->assertSame(['connect'=>true],$provided->get_uri_options()); $this->assertSame([],$provided->get_driver_options());
    }
    public function testCollectionNamesAndDatabaseFallbacks()
    {
        $plain = new NoContextDriver; $plain->set_collection_name('chosen');
        $this->assertSame('chosen',$plain->get_collection_name());
        $this->assertSame('fallback',$plain->get_database_name('fallback'));
        $this->assertSame('NameOnlyShortCollection',(new NameOnlyShortCollection)->get_collection_name());
        $this->assertSame('records',(new NameOnlyCollection)->get_collection_name());
    }
    /** @dataProvider badCollectionNames */
    public function testInvalidCollectionNamesFail($name)
    {
        $this->expectException(mongo\collection\bad_param::class); (new NoContextDriver)->set_collection_name($name);
    }
    public function badCollectionNames() { return [[''], [42]]; }
    public function testCollectionNameMustBeAvailable()
    {
        $this->expectException(mongo\collection\missing::class); (new NoContextDriver)->get_collection_name();
    }
    public function testDatabaseMustBeAvailableWithoutInterfaceOrFallback()
    {
        $this->expectException(mongo\db\exception\bad_param::class); (new NoContextDriver)->get_db();
    }
    public function testDatabaseNameMustHaveFallback()
    {
        $this->expectException(mongo\db\exception\bad_param::class); (new NoContextDriver)->get_database_name();
    }
    /** @dataProvider badDatabaseAccessors */
    public function testInvalidDatabaseProviderFailsWithAUsableException($method)
    {
        $this->expectException(mongo\db\exception\bad_interface::class); (new BadDatabase)->$method();
    }
    public function badDatabaseAccessors() { return [['get_db'],['get_database_name']]; }
    /** @dataProvider invalidCommands */
    public function testInvalidCommandsFailBeforeConnectionAccess($param)
    {
        $this->expectException(mongo\db\exception\bad_param::class); (new NoContextDriver)->command($param);
    }
    public function invalidCommands() { return [[[]], [['data'=>'wrong']], [['data'=>['ping'=>1],'options'=>'wrong']]]; }
    /** @dataProvider affectedResponses */
    public function testAffectedRecordFailuresPreserveCurrentPolicy($reply,$message)
    {
        $driver = new AffectedDriver; $driver->reply = $reply;
        $this->expectException(mongo\record\not_modified::class); $this->expectExceptionMessage($message);
        $driver->get_affected_records(['count_only'=>true]);
    }
    public function affectedResponses()
    {
        return [[[],'no update data'], [['updatedExisting'=>false],'not updatedExisting'],
            [['updatedExisting'=>true],"missing 'n'"], [['updatedExisting'=>true,'n'=>0],'no records updated']];
    }
    public function testAffectedRecordCountIsAnInteger()
    {
        $driver = new AffectedDriver; $driver->reply = ['updatedExisting'=>true,'n'=>'2'];
        $this->assertSame(2,$driver->get_affected_records());
    }
    public function testInvalidCollectionNameFailsBeforeDatabaseAccess()
    {
        $this->expectException(mongo\collection\bad_param::class); (new NoContextDriver)->get_collection(42);
    }
    public function testExplicitLazyClientDefersNetworkFailureUntilTheFirstCommand()
    {
        $client = mongo::load_client('mongodb://127.0.0.1:1',['client_options'=>['connect'=>false,'serverSelectionTimeoutMS'=>5,'connectTimeoutMS'=>5]]);
        $this->assertInstanceOf(\MongoClient::class,$client);
        $reply = $client->selectDB('flat_unit_probe')->command(['ping'=>1]);
        $this->assertSame(0.0,(float)$reply['ok']);
        $this->assertNotEmpty($reply['errmsg']);
    }
    public function testEagerConnectionRetriesRealNetworkFailuresAndDoesNotCacheThem()
    {
        try {
            mongo::load_client('mongodb://127.0.0.1:1',['connect_attempts'=>3,'client_options'=>['connect'=>true,'serverSelectionTimeoutMS'=>5,'connectTimeoutMS'=>5]]);
            $this->fail('Expected eager connection failure');
        } catch (mongo\db\exception\connect_failure $error) { $this->assertStringContainsString('failed after 3 attempts',$error->getMessage()); }
        foreach (['_client','_client_ping'] as $name) {
            $p=new \ReflectionProperty(mongo::class,$name); $p->setAccessible(true); $this->assertSame([],$p->getValue());
        }
    }
    public function testLazyClientsAreReusedWithoutRecordingASuccessfulPing()
    {
        $options=['client_options'=>['connect'=>false]];
        $client=mongo::load_client('mongodb://127.0.0.1:1',$options);
        $this->assertSame($client,mongo::load_client('mongodb://127.0.0.1:1',$options));
        $p=new \ReflectionProperty(mongo::class,'_client_ping'); $p->setAccessible(true); $this->assertSame([],$p->getValue());
    }
    public function testCollectionInterfaceRejectsManualNameChanges()
    {
        $this->expectException(mongo\collection\illegal::class); (new NameOnlyCollection)->set_collection_name('other');
    }
    public function testShortnameInterfaceRejectsManualNameChanges()
    {
        $this->expectException(mongo\collection\illegal::class); (new NameOnlyShortCollection)->set_collection_name('other');
    }
    public function testInvalidDatabaseCollectionFactoryIsRejected()
    {
        $this->expectException(mongo\collection\not_found::class);
        (new InvalidCollectionDriver)->get_collection('records');
    }
    /**
     * @group services
     * @group mongo
     */
    public function testClientLoadsWithoutOptionsAndWithoutWarnings()
    {
        $warnings = [];
        set_error_handler(function ($level,$message) use (&$warnings) { $warnings[] = $message; return true; });
        try { $client = mongo::load_client(getenv('FLAT_TEST_MONGODB_URI')); }
        finally { restore_error_handler(); }
        $this->assertInstanceOf(\MongoClient::class,$client);
        $this->assertSame([],$warnings);
    }
    /**
     * @group services
     * @group mongo
     */
    public function testPingOffsetIsSanitizedAndStoredWithClientCache()
    {
        $before = time();
        $client = mongo::load_client(getenv('FLAT_TEST_MONGODB_URI'),['ping_offset'=>'60','client_options'=>['connect'=>true]]);
        $this->assertInstanceOf(\MongoClient::class,$client);
        $property = new \ReflectionProperty(mongo::class,'_client_ping'); $property->setAccessible(true);
        $timestamps = $property->getValue();
        $this->assertCount(1,$timestamps);
        $this->assertGreaterThanOrEqual($before+60,reset($timestamps));
    }
    /**
     * @group services
     * @group mongo
     */
    public function testPingTimestampWithoutAnOffsetIsNow()
    {
        $before = time();
        mongo::load_client(getenv('FLAT_TEST_MONGODB_URI'),['client_options'=>['connect'=>true,'serverSelectionTimeoutMS'=>2500]]);
        $after = time();
        $property = new \ReflectionProperty(mongo::class,'_client_ping'); $property->setAccessible(true);
        $timestamp = max($property->getValue());
        $this->assertGreaterThanOrEqual($before,$timestamp);
        $this->assertLessThanOrEqual($after,$timestamp);
    }
    /**
     * @group services
     * @group mongo
     */
    public function testRefreshedPingKeepsTheConfiguredOffset()
    {
        $uri = getenv('FLAT_TEST_MONGODB_URI');
        $options = ['ping_offset'=>'60','client_options'=>['connect'=>true,'serverSelectionTimeoutMS'=>1500]];
        $client = mongo::load_client($uri,$options);
        $property = new \ReflectionProperty(mongo::class,'_client_ping'); $property->setAccessible(true);
        $property->setValue(null,array_map(function () { return time() - mongo::client_ping_expire - 100; },$property->getValue()));
        $before = time();
        $this->assertSame($client,mongo::load_client($uri,$options));
        $this->assertGreaterThanOrEqual($before + 60,max($property->getValue()));
    }
}
