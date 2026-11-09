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
    /**
     * @dataProvider directMethods
     * @group known-defect
     */
    public function testNonemptyConstructorArrayStillCannotActAsADriver($method)
    {
        $orm=new \flat\data\orm(['driver'=>'unresolved configuration']);
        $this->expectException(\Error::class); $this->expectExceptionMessage('on array');
        if ($method==='get') $orm->get('key'); else $orm->set('key',new \flat\data\generic);
    }
    public function directMethods() { return [['get'],['set']]; }
    /** @group known-defect */
    public function testDefaultFacadeUsesRealNativeSessionsWhileRetainingItsVoidGetResult()
    {
        $directory=sys_get_temp_dir().'/flat-legacy-orm-'.bin2hex(random_bytes(6)); mkdir($directory,0700);
        try {
            $body='session_save_path('.var_export($directory,true).'); session_id("flatlegacyorm"); '
                .'\flat\data\orm\driver\session\handler\native::set_ormkey("flatlegacyorm"); '
                .'$data=new \flat\data\generic(["value"=>"persisted"]); '
                .'$set=\flat\data\orm::set_data("key",$data); $get=\flat\data\orm::get_data("key"); '
                .'$native=new \flat\data\orm\driver\session; '
                .'echo json_encode([$set,$get,$native->get_data("key")->value]); session_write_close();';
            list($status,$out,$err)=$this->subprocess($body);
            $this->assertSame(0,$status,$err); $this->assertSame('',$err); $this->assertSame([null,null,'persisted'],json_decode($out,true));
        } finally { foreach (glob($directory.'/*') as $file) unlink($file); rmdir($directory); }
    }
}
