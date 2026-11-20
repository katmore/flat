<?php
namespace flat\test\Unit\Data\Orm;
require_once __DIR__.'/../../../Support/ConfigSandbox.php';
require_once __DIR__.'/../../../Support/PhpSubprocess.php';
require_once __DIR__.'/../../../Support/NativeSessionBoundary.php';
use PHPUnit\Framework\TestCase;
use flat\data\orm\driver\session as SessionDriver;
use flat\data\orm\driver\session\handler\native as NativeHandler;
class MemorySessionHandler extends \flat\data\orm\driver\session\handler
{
    public $parameters;
    private $records = [];
    public function __construct(array $params = null) { $this->parameters = $params; }
    public static function destroy(array $options = null) {}
    public function get($key,array $options = null) { return $this->records[$key] ?? null; }
    public function set($key,\flat\data $data,array $options = null) { $this->records[$key] = $data; return 'stored'; }
}
class NativeHandlerProbe extends NativeHandler
{
    public static function key(array $options = null) { return parent::_get_ormkey($options); }
    public static function initialize(array $options = null) { return parent::_init_session($options); }
}
class SessionDriverTest extends TestCase
{
    use \flat\test\Support\ConfigSandbox;
    use \flat\test\Support\PhpSubprocess;
    private $sessionDirectory;
    private $sessionId;
    protected function setUp(): void
    {
        $this->openConfigSandbox([]); $root = $this->configSandboxDirectory(); mkdir($root.'/data/orm/driver',0700,true);
        file_put_contents($root.'/data/orm/driver/session.php','<?php return '.var_export(['handler'=>MemorySessionHandler::class,'ormkey'=>'configured'],true).';');
        $this->sessionDirectory = sys_get_temp_dir().'/flat-native-session-'.bin2hex(random_bytes(6)); mkdir($this->sessionDirectory,0700);
        $this->sessionId = 'flatunit'.bin2hex(random_bytes(8));
    }
    protected function tearDown(): void
    {
        \flat\test\Support\NativeSessionBoundary::$disabled = false; NativeHandler::set_ormkey(NativeHandler::fallback_ormkey);
        $root = $this->configSandboxDirectory(); unlink($root.'/data/orm/driver/session.php');
        foreach (['data/orm/driver','data/orm','data'] as $directory) rmdir($root.'/'.$directory);
        $this->closeConfigSandbox();
        foreach (glob($this->sessionDirectory.'/*') as $file) unlink($file);
        rmdir($this->sessionDirectory);
    }
    public function testHandlerObjectIsUsedDirectlyAndReturnsItsResults()
    {
        $handler = new MemorySessionHandler; $driver = new SessionDriver(['handler'=>$handler]);
        $data = new \flat\data\generic(['value'=>'object identity']);
        $this->assertSame('stored',$driver->set_data('key',$data));
        $this->assertSame($data,$driver->get_data('key')); $this->assertNull($driver->get_data('missing'));
        $this->assertInstanceOf(\flat\data\session\driver::class,$driver);
        $this->assertInstanceOf(\flat\data\session\driver\session::class,$driver);
    }
    public function testHandlerClassReceivesItsConstructorParameters()
    {
        $driver = new SessionDriver(['handler'=>MemorySessionHandler::class,'handler_param'=>['local'=>'parameter']]);
        $data = new \flat\data\generic(['value'=>42]); $driver->set_data('key',$data);
        $this->assertSame($data,$driver->get_data('key'));
        // Verify the actual handler constructor, not only the wrapper's type.
        $property = new \ReflectionProperty(\flat\data\session\driver\session::class,'_h'); $property->setAccessible(true);
        $this->assertSame(['local'=>'parameter'],$property->getValue($driver)->parameters);
    }
    /** @dataProvider fallbackHandlers */
    public function testInvalidExplicitHandlersFallBackToConfiguration($handler)
    {
        $driver = new SessionDriver(['handler'=>$handler,'handler_param'=>'ignored non-array']);
        $data = new \flat\data\generic(['value'=>'configured']); $driver->set_data('key',$data);
        $this->assertSame($data,$driver->get_data('key'));
    }
    public function fallbackHandlers() { return [['AbsentSessionHandler'],[\stdClass::class],[new \stdClass],[42],[null]]; }
    public function testNativeOrmKeyOptionsAndConfiguration()
    {
        NativeHandler::set_ormkey('fallback');
        $this->assertSame('explicit',NativeHandlerProbe::key(['ormkey'=>'explicit']));
        $this->assertSame('configured',NativeHandlerProbe::key());
    }
    /** @dataProvider invalidKeys */
    public function testInvalidKeysThrowTheDeclaredParameterError($key)
    {
        $this->expectException(\flat\data\orm\driver\session\exception\bad_param::class);
        NativeHandler::set_ormkey($key);
    }
    public function invalidKeys() { return [[''],[0],[['nested']],[new \stdClass]]; }
    public function testDisabledSessionStatusUsesItsFrameworkFailure()
    {
        \flat\test\Support\NativeSessionBoundary::$disabled = true;
        $this->expectException(\flat\data\orm\driver\session\exception\failure::class);
        NativeHandlerProbe::initialize();
    }
    private function sessionPrefix()
    {
        return 'session_save_path('.var_export($this->sessionDirectory,true).'); session_id('.var_export($this->sessionId,true).'); \\flat\\data\\orm\\driver\\session\\handler\\native::set_ormkey("flatunitnative"); ';
    }
    public function testRealNativeSessionPersistsAcrossModernAndLegacyDriverNames()
    {
        $body = $this->sessionPrefix().'$driver=new \\flat\\data\\orm\\driver\\session(["handler"=>"missing"]); $driver->set_data("key",new \\flat\\data\\generic(["value"=>"persisted"])); session_write_close(); echo "written";';
        list($status,$out,$err) = $this->subprocess($body);
        $this->assertSame(0,$status,$out.$err); $this->assertSame('written',$out); $this->assertSame('',$err);
        $body = $this->sessionPrefix().'$driver=new \\flat\\data\\session\\driver\\session; echo json_encode([$driver->get_data("key")->value,$driver->get_data("missing")]); session_write_close();';
        list($status,$out,$err) = $this->subprocess($body);
        $this->assertSame(0,$status,$out.$err); $this->assertSame('',$err); $this->assertSame(['persisted',null],json_decode($out,true));
    }
    public function testDestroyEndsTheSessionAndRemovesItsData()
    {
        $body = $this->sessionPrefix().'$h=new \\flat\\data\\orm\\driver\\session\\handler\\native; $h->set("key",new \\flat\\data\\generic(["value"=>"local"])); session_write_close(); session_start(); $warnings=[]; set_error_handler(function($n,$m)use(&$warnings){$warnings[]=$m;return true;}); try { $h::destroy(); } finally { restore_error_handler(); } echo json_encode([session_status(),$warnings,count(glob(session_save_path()."/sess_*")),$_SESSION]);';
        list($status,$out,$err) = $this->subprocess($body); $result = json_decode($out,true);
        $this->assertSame(0,$status,$out.$err); $this->assertSame('',$err);
        $this->assertSame(PHP_SESSION_NONE,$result[0]);
        $this->assertSame([],$result[1]);
        $this->assertSame(0,$result[2]); // no session data file remains
        $this->assertSame([],$result[3]);
    }
}
