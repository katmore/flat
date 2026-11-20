<?php
namespace flat\test\Unit\Data\Orm;
require_once __DIR__.'/../../../Support/PhpSubprocess.php';
use PHPUnit\Framework\TestCase;
class DispatchOrmFixture extends \flat\data\orm
{
    public static $constructed = 0;
    private $records=[];
    public function __construct(array $driver=null) { self::$constructed++; }
    public function get($key) { return $this->records[$key] ?? null; }
    public function set($key,\flat\data $data) { $this->records[$key]=$data; return 'stored'; }
}
class ConfiguredOrmDriver
{
    public $config; public $stored = [];
    public function __construct(array $param=null) { $this->config = $param; }
    public function get_data($key) { return isset($this->stored[$key]) ? $this->stored[$key] : null; }
    public function set_data($key,\flat\data $data) { $this->stored[$key] = $data; return 'stored'; }
}
class GetOnlyOrmDriver { public function get_data($key) {} }
class SetOnlyOrmDriver { public function set_data($key,\flat\data $data) {} }
class LegacyOrmTest extends TestCase
{
    use \flat\test\Support\PhpSubprocess;
    private $cached;
    protected function setUp(): void
    {
        $p=new \ReflectionProperty(\flat\data\orm::class,'_s'); $p->setAccessible(true); $this->cached=$p->getValue(); $p->setValue(null,null);
        DispatchOrmFixture::$constructed=0;
    }
    protected function tearDown(): void
    {
        $p=new \ReflectionProperty(\flat\data\orm::class,'_s'); $p->setAccessible(true); $p->setValue(null,$this->cached);
    }
    public function testStaticFacadeCachesALegalSubclassAndReturnsItsResults()
    {
        $data=new \flat\data\generic(['value'=>'local']);
        $this->assertSame('stored',DispatchOrmFixture::set_data('key',$data,['driver'=>'currently ignored']));
        $this->assertSame($data,DispatchOrmFixture::get_data('key'));
        $this->assertNull(DispatchOrmFixture::get_data('missing')); $this->assertSame(1,DispatchOrmFixture::$constructed);
    }
    public function testConstructorArrayConfiguresTheDriver()
    {
        $orm=new \flat\data\orm(['driver'=>ConfiguredOrmDriver::class,'option'=>'value']);
        $data=new \flat\data\generic(['value'=>'kept']);
        $this->assertSame('stored',$orm->set('key',$data));
        $this->assertSame($data,$orm->get('key'));
        $driver=new ConfiguredOrmDriver(['given'=>true]);
        $this->assertNull((new \flat\data\orm(['driver'=>$driver]))->get('missing'));
        $this->assertSame(['given'=>true],$driver->config);
        $p=new \ReflectionProperty(\flat\data\orm::class,'_driver'); $p->setAccessible(true);
        $this->assertSame(['option'=>'value'],$p->getValue($orm)->config);
        $this->expectException(\flat\data\orm\exception\bad_param::class);
        new \flat\data\orm(['driver'=>'unresolved configuration']);
    }
    public function testADriverNeedsBothGetAndSet()
    {
        foreach ([GetOnlyOrmDriver::class, SetOnlyOrmDriver::class] as $class) {
            try { new \flat\data\orm(['driver'=>$class]); $this->fail("$class is not a complete driver"); }
            catch (\flat\data\orm\exception\bad_param $e) { $this->assertStringContainsString("'driver' option",$e->getMessage()); }
        }
    }
    public function testDefaultFacadeUsesRealNativeSessionsAndReturnsStoredData()
    {
        $directory=sys_get_temp_dir().'/flat-legacy-orm-'.bin2hex(random_bytes(6)); mkdir($directory,0700);
        try {
            $body='session_save_path('.var_export($directory,true).'); session_id("flatlegacyorm"); '
                .'\flat\data\orm\driver\session\handler\native::set_ormkey("flatlegacyorm"); '
                .'$data=new \flat\data\generic(["value"=>"persisted"]); '
                .'\flat\data\orm::set_data("key",$data); $get=\flat\data\orm::get_data("key"); '
                .'$native=new \flat\data\orm\driver\session; '
                .'echo json_encode([$get->value,$native->get_data("key")->value]); session_write_close();';
            list($status,$out,$err)=$this->subprocess($body);
            $this->assertSame(0,$status,$err); $this->assertSame('',$err); $this->assertSame(['persisted','persisted'],json_decode($out,true));
        } finally { foreach (glob($directory.'/*') as $file) unlink($file); rmdir($directory); }
    }
}
