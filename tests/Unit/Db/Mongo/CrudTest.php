<?php
namespace flat\test\Unit\Db\Mongo;
use PHPUnit\Framework\TestCase;
use flat\db\driver\mongo;
class MongoFixture extends mongo\crud implements mongo\client\server_string, mongo\client\options, mongo\db\explicit
{
    public static $database;
    public function get_client_server_string() { return getenv('FLAT_TEST_MONGODB_URI') ?: 'mongodb://127.0.0.1:27017'; }
    public function get_client_options() { return ['connect'=>true,'serverSelectionTimeoutMS'=>1000,'connectTimeoutMS'=>1000]; }
    public function get_client_db() { return self::$database; }
}
class ExplicitCollection extends MongoFixture implements mongo\collection\explicit
{
    public function get_mongo_collection() { return 'records'; }
}
class ShortCollection extends MongoFixture implements mongo\collection\shortname {}
class MongoRow extends \flat\data { public $_id; public $name; public $number; }
/**
 * @group services
 * @group mongo
 */
class CrudTest extends TestCase
{
    public function testNonDuplicateServerInsertFailureIsRethrownUnchanged()
    {
        $this->driver->getMongoDBClient()->selectDatabase($this->database)->createCollection('strict',[
            'validator'=>['name'=>['$type'=>'string']],
        ]);
        $this->driver->set_collection_name('strict');
        try { $this->driver->create(['name'=>42]); }
        catch (\MongoCursorException $error) {
            $this->assertSame(121,$error->getCode());
            $this->assertStringContainsString('validation',$error->getMessage());
            return;
        }
        $this->fail('Expected the native document validation error');
    }
    private $driver;
    private $database;
    private $saved = [];
    protected function setUp(): void
    {
        foreach (['_client','_client_ping'] as $name) {
            $p = new \ReflectionProperty(mongo::class,$name); $p->setAccessible(true);
            $this->saved[$name] = $p->getValue(); $p->setValue(null,[]);
        }
        $this->database = MongoFixture::$database = 'flat_unit_'.bin2hex(random_bytes(6));
        $this->driver = new MongoFixture; $this->driver->set_collection_name('records');
        $this->driver->getMongoDBCollection()->insertMany([
            ['name'=>'alpha','number'=>1,'created'=>1], ['name'=>'beta','number'=>2,'created'=>2], ['name'=>'alphabet','number'=>3,'created'=>3],
        ]);
    }
    protected function tearDown(): void
    {
        if ($this->driver && strpos($this->database,'flat_unit_') === 0) $this->driver->getMongoDBClient()->dropDatabase($this->database);
        foreach ($this->saved as $name=>$value) { $p = new \ReflectionProperty(mongo::class,$name); $p->setAccessible(true); $p->setValue(null,$value); }
    }
    public function testLegacyAndModernCollectionsUseTheSameDatabase()
    {
        $this->assertInstanceOf(\MongoClient::class,$this->driver->get_client());
        $this->assertSame($this->driver->get_client(),$this->driver->get_client());
        $this->assertInstanceOf(\MongoCollection::class,$this->driver->get_collection());
        $this->assertSame(3,$this->driver->get_collection()->count());
        $this->assertSame(3,$this->driver->getMongoDBCollection('records')->countDocuments());
        $this->assertSame('records.extra',$this->driver->get_ns_collection_name('extra'));
        $this->assertSame('records',$this->driver->get_ns_collection_name());
        $this->assertSame('records.extra',$this->driver->get_ns_collection('extra')->getName());
        $this->assertSame($this->database,$this->driver->get_database_name());
        $this->assertSame($this->database,(string)$this->driver->get_db($this->database,$this->driver->get_client()));
        $this->assertSame(1.0,(float)$this->driver->command(['data'=>['ping'=>1]])['ok']);
        $this->assertSame(1.0,(float)$this->driver->command(['data'=>['ping'=>1],'options'=>['socketTimeoutMS'=>1000]])['ok']);
    }
    public function testReadsSupportShapesSortingSkippingAndCountOnly()
    {
        $rows = $this->driver->read(['sort'=>['number'=>1]]);
        $this->assertSame(['alpha','beta','alphabet'],array_column($rows,'name'));
        $this->assertIsString($rows[0]['_id']);
        $this->assertSame('beta',$this->driver->read(['key'=>['number'=>2]])['name']);
        $this->assertSame('alpha',$this->driver->read(['find'=>['number'=>1],'record'=>'stdClass'])->name);
        $this->assertSame('beta',$this->driver->read(['sort'=>['number'=>1],'skip'=>1,'limit'=>1])['name']);
        $this->assertSame(3,$this->driver->read(['count_only'=>true]));
        $this->assertSame(1,$this->driver->read(['count_only'=>true,'skip'=>1,'limit'=>1]));
        $this->assertSame(0,$this->driver->read(['find'=>['name'=>'missing'],'count_only'=>true]));
    }
    public function testClassAndCloneRecordsAndCallbacks()
    {
        foreach ([MongoRow::class,new MongoRow] as $record) {
            $rows = $this->driver->read(['record'=>$record,'sort'=>['number'=>1]]);
            $this->assertCount(3,$rows); $this->assertInstanceOf(MongoRow::class,$rows[0]); $this->assertSame('alpha',$rows[0]->name);
            $one = $this->driver->read(['record'=>$record,'find'=>['number'=>2]]); $this->assertSame('beta',$one->name);
        }
        $objects = $this->driver->read(['result'=>'stdClass','sort'=>['number'=>1]]); $this->assertSame('alpha',$objects[0]->name);
        $seen=[]; $result=$this->driver->read(['callback'=>function ($row) use (&$seen) { $seen[]=$row['name']; },'sort'=>['number'=>1]]);
        $this->assertNull($result); $this->assertSame(['alpha','beta','alphabet'],$seen);
        $one=null; $this->assertNull($this->driver->read(['find'=>['number'=>2],'callback'=>function ($row) use (&$one) { $one=$row; }]));
        $this->assertSame('beta',$one['name']);
    }
    public function testPrefixReadsAndNamespacedCollections()
    {
        $rows=$this->driver->read(['starts_with'=>['name'=>'alph'],'sort'=>['number'=>1]]);
        $this->assertSame(['alpha','alphabet'],array_column($rows,'name'));
        $nested = ['name'=>'nested','number'=>9]; $this->driver->get_ns_collection('nested')->insert($nested);
        $this->assertSame('nested',$this->driver->read(['collection_ns'=>'nested','key'=>['number'=>9]])['name']);
    }
    public function testModernClientOptionAliasesAndExplicitDatabaseNames()
    {
        $uri = $this->driver->get_uri();
        foreach ([['uri'=>$uri,'uriOptions'=>['serverSelectionTimeoutMS'=>1000],'driverOptions'=>[]],
            ['server_string'=>$uri,'client_options'=>['serverSelectionTimeoutMS'=>1000],'driver_options'=>[]]] as $options) {
            $client = $this->driver->getMongoDBClient($options);
            $this->assertSame(1.0,(float)$client->selectDatabase($this->database)->command(['ping'=>1])->toArray()[0]['ok']);
        }
        $this->assertInstanceOf(\MongoClient::class,$this->driver->get_client(42));
        $this->assertInstanceOf(\MongoClient::class,$this->driver->connect(['server'=>$uri,'client_options'=>['connect'=>true],'driver_options'=>[]]));
    }
    public function testClientOptionsChooseTheServer()
    {
        // An unreachable server in the options must win over the driver's own URI.
        $client = $this->driver->getMongoDBClient(['uri'=>'mongodb://127.0.0.1:1','uriOptions'=>['serverSelectionTimeoutMS'=>100,'connectTimeoutMS'=>100],'driverOptions'=>[]]);
        $this->expectException(\MongoDB\Driver\Exception\Exception::class);
        $client->selectDatabase($this->database)->command(['ping'=>1]);
    }
    public function testClientOptionsAreKeptWhenSelectingAClient()
    {
        $uri = $this->driver->get_uri();
        $plain = $this->driver->get_client($uri);
        $tuned = $this->driver->get_client($uri,['client_options'=>['connect'=>false,'serverSelectionTimeoutMS'=>77]]);
        $this->assertNotSame($plain,$tuned);
        $this->assertSame($tuned,$this->driver->get_client($uri,['client_options'=>['connect'=>false,'serverSelectionTimeoutMS'=>77]]));
    }
    public function testExpiredCachedPingUsesTheRealDatabaseAndKeepsTheClient()
    {
        $client = $this->driver->get_client();
        $p = new \ReflectionProperty(mongo::class,'_client_ping'); $p->setAccessible(true);
        $pings = $p->getValue(); foreach ($pings as &$ping) $ping = time()-mongo::client_ping_expire-1; unset($ping); $p->setValue(null,$pings);
        $this->assertSame($client,$this->driver->get_client());
        // Missing verification metadata now triggers a real ping, not an
        // unnecessary replacement based on the adapter's empty getConnections.
        $p->setValue(null,[]); $this->assertSame($client,$this->driver->get_client());
    }
    public function testCreateUpdateAndDeleteRoundTrip()
    {
        $id=$this->driver->create(['name'=>'created','number'=>4]);
        $this->assertNotEmpty((string)$id);
        $this->assertSame('created',$this->driver->read(['find'=>['number'=>4]])['name']);
        $this->driver->update(['key'=>['number'=>4],'data'=>['name'=>'changed']]);
        $this->assertSame('changed',$this->driver->read(['find'=>['number'=>4]])['name']);
        $this->assertSame(1,$this->driver->delete(['key'=>['number'=>4],'count'=>true]));
        $this->assertSame(0,$this->driver->read(['find'=>['number'=>4],'count_only'=>true]));
    }
    public function testCreateSubcollectionsUpsertsAndDuplicateKeys()
    {
        $id = $this->driver->create(new MongoRow(['name'=>'typed','number'=>4]));
        $this->assertNotEmpty((string)$id);
        $this->driver->create(['name'=>'nested','number'=>5],['sub_collection'=>'archive']);
        $this->assertSame('nested',$this->driver->read(['collection_ns'=>'archive','key'=>['number'=>5]])['name']);
        $this->driver->create(['name'=>'upserted','number'=>6],['upsert'=>['number'=>6]]);
        $this->assertSame('upserted',$this->driver->read(['key'=>['number'=>6]])['name']);
        $this->driver->create(['name'=>'updated','number'=>6],['upsert'=>['number'=>6]]);
        $this->assertSame('updated',$this->driver->read(['key'=>['number'=>6]])['name']);
        $this->driver->getMongoDBCollection()->createIndex(['number'=>1],['unique'=>true]);
        $this->expectException(mongo\collection\duplicate_key::class);
        $this->driver->create(['name'=>'duplicate','number'=>1]);
    }
    public function testDocumentedCollectionNamespaceAliasForCreate()
    {
        $this->driver->create(['name'=>'alias','number'=>7],['collection_ns'=>'archive']);
        $this->assertSame('alias',$this->driver->read(['collection_ns'=>'archive','key'=>['number'=>7]])['name']);
    }
    public function testReplacementOperatorAndNamespacedWrites()
    {
        $this->driver->update(['key'=>['number'=>1],'record'=>['name'=>'replacement','number'=>1]]);
        $this->assertSame('replacement',$this->driver->read(['key'=>['number'=>1]])['name']);
        $this->driver->update(['key'=>['number'=>1],'op'=>['$inc'=>['number'=>10]]]);
        $this->assertSame('replacement',$this->driver->read(['key'=>['number'=>11]])['name']);
        $this->driver->create(['name'=>'nested','number'=>9],['sub_collection'=>'archive']);
        $this->driver->update(['collection_ns'=>'archive','key'=>['number'=>9],'data'=>['name'=>'changed']]);
        $this->assertSame('changed',$this->driver->read(['collection_ns'=>'archive','key'=>['number'=>9]])['name']);
        $this->assertNull($this->driver->delete(['collection_ns'=>'archive','key'=>['number'=>9]]));
        $this->expectException(mongo\record\not_modified::class);
        $this->driver->update(['key'=>['number'=>1]]);
    }
    public function testUpdatedRecordCountUsesTheRealAdapterWriteResult()
    {
        $this->assertSame(1,$this->driver->update(['key'=>['number'=>1],'data'=>['name'=>'counted'],'count'=>true]));
    }
    public function testUpdateCountModesForReplacementAndOperators()
    {
        $this->assertSame(1,$this->driver->update(['key'=>['number'=>1],'record'=>['name'=>'record','number'=>1],'count'=>true]));
        $this->assertSame(1,$this->driver->update(['key'=>['number'=>1],'record'=>['name'=>'record2','number'=>1],'not_modified_exception'=>true]));
        $this->assertSame(1,$this->driver->update(['key'=>['number'=>1],'data'=>['name'=>'data'],'not_modified_exception'=>true]));
        $this->assertSame(1,$this->driver->update(['key'=>['number'=>1],'op'=>['$inc'=>['number'=>1]],'count'=>true]));
        $this->assertSame(1,$this->driver->update(['key'=>['name'=>'data'],'op'=>['$inc'=>['number'=>1]],'not_modified_exception'=>true]));
    }
    public function testInvalidDocumentWritePreservesTheDriverException()
    {
        $this->expectException(\MongoException::class);
        $this->driver->create(['$invalid'=>'document']);
    }
    public function testLoadDefaultsAliasesAndStartWindow()
    {
        $rows = $this->driver->load([],['record'=>'assoc','limit'=>2,'sort'=>['number'=>1]]);
        $this->assertSame(['alpha','beta'],array_column($rows,'name'));
        $rows = $this->driver->load([],['result'=>'assoc','limit'=>1,'skip'=>1,'sort'=>['number'=>1]]);
        $this->assertSame('beta',$rows['name']); // Existing read limit=1 returns a single record.
        $rows = $this->driver->load([],['record'=>'assoc','limit'=>1,'start'=>['number'=>2],'sort'=>['number'=>1]],null,10,2,null,2);
        $this->assertSame(['beta'],array_column($rows,'name'));
        $rows = $this->driver->load([],['record'=>'assoc','limit'=>1,'start'=>['number'=>1]],null,10,2,['number'=>1],1);
        $this->assertSame(['alpha'],array_column($rows,'name'));
        $rows = $this->driver->load([],['record'=>'assoc','start'=>['number'=>999]],null,10,100,['number'=>1],1);
        $this->assertSame([],$rows);
        $this->assertCount(3,$this->driver->load([],null,new MongoRow));
    }
    /** @dataProvider invalidLoadOptions */
    public function testLoadOptionsAreValidated($options)
    {
        $this->expectException(mongo\db\exception\bad_param::class); $this->driver->load([],$options);
    }
    public function invalidLoadOptions()
    {
        return [[['limit'=>'two']], [['limit'=>-1]], [['limit'=>101]], [['skip'=>'one']], [['skip'=>-1]],
            [['start'=>'wrong']], [['start'=>['one'=>1,'two'=>2]]], [['start'=>[1]]], [['sort'=>'wrong']]];
    }
    /** @dataProvider invalidReads */
    public function testInvalidReadParametersFail($parameters)
    {
        $this->expectException(mongo\record\bad_param::class); $this->driver->read($parameters);
    }
    public function invalidReads()
    {
        return [[['starts_with'=>'text']], [['starts_with'=>['name'=>'']]], [['limit'=>-1]], [['limit'=>10001]], [['skip'=>-1]], [['skip'=>'one']]];
    }
    public function testMissingRecordsThrow()
    {
        $this->expectException(mongo\record\not_found::class); $this->driver->read(['find'=>['name'=>'missing']]);
    }
    public function testMissingDeletesAndInvalidWriteParameters()
    {
        $this->assertNull($this->driver->delete([]));
        $this->assertNull($this->driver->update([]));
        $this->expectException(mongo\record\not_modified::class); $this->driver->delete(['key'=>['name'=>'missing']]);
    }
    /** @dataProvider invalidWrites */
    public function testWriteParameterValidation($method,$parameters)
    {
        $this->expectException(mongo\record\bad_param::class); $this->driver->$method($parameters);
    }
    public function invalidWrites()
    {
        return [['create','not document'], ['delete',['key'=>'wrong']], ['update',['key'=>'wrong']],
            ['update',['key'=>['number'=>1],'data'=>'wrong']], ['update',['key'=>['number'=>1],'op'=>'wrong']]];
    }
}
