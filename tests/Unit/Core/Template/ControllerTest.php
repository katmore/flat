<?php
namespace flat\test\Unit\Core\Template;
use PHPUnit\Framework\TestCase;
use flat\core\controller\tmpl;
use flat\tmpl\data;
class TemplateController extends tmpl
{
    public static $root;
    public static function get_design_root_dir(): string { return self::$root; }
}
class BareController extends TemplateController { const DESIGN_PREFIX = ''; }
class BasedController extends BareController implements \flat\tmpl\design_base
{
    public static function get_design_base() { return 'base'; }
}
class ExplicitController extends TemplateController implements \flat\tmpl\design
{
    public function get_design() { return 'explicit'; }
}
class IgnoredController extends TemplateController implements \flat\tmpl\ignore {}
class ProviderController extends ExplicitController implements \flat\tmpl\data_provider
{
    public function get_data() { return ['title'=>'provided']; }
}
class IgnoredProvider extends ProviderController implements \flat\tmpl\ignore {}
class OptionalProvider extends ProviderController implements \flat\tmpl\ignorable
{
    public $ignored = true;
    public function is_ignored() { return $this->ignored; }
}
class OptionalController extends ExplicitController implements \flat\tmpl\ignorable
{
    public $ignored = true;
    public function is_ignored() { return $this->ignored; }
}
class OutputDesign implements \flat\tmpl\output
{
    public function get_output($data, $checkOnly = false): ?string { return $checkOnly ? null : '<b>'.$data['title']."</b>\n"; }
}
class ResolvedDesign implements \flat\tmpl\resolvable_design
{
    public function resolvable_design_output(data $data): void { echo 'resolved:'.$data['title']; }
}
class DisplayClass extends \flat\tmpl\display
{
    public $checkOnly;
    public function __construct($design,$check_only=false) { $this->checkOnly = $check_only; }
    public function get_output($data): ?string { return $this->checkOnly ? null : 'display class'; }
}
class TemplateIo
{
    public static $mode;
    public static $handles = [];
}
class ControllerTest extends TestCase
{
    private $root;
    private $saved = [];
    private $bufferLevel;
    protected function setUp(): void
    {
        $this->bufferLevel = ob_get_level();
        TemplateIo::$mode = null;
        foreach ((new \ReflectionClass(tmpl::class))->getProperties(\ReflectionProperty::IS_STATIC) as $p) {
            $p->setAccessible(true); $this->saved[$p->getName()] = $p->getValue();
        }
        $this->root = sys_get_temp_dir().'/flat-template-'.bin2hex(random_bytes(8));
        mkdir($this->root); mkdir($this->root.'/tmpl'); mkdir($this->root.'/base');
        TemplateController::$root = $this->root;
        tmpl::set_design_class_prefix(''); tmpl::disable_design_logger();
    }
    protected function tearDown(): void
    {
        while (ob_get_level() > $this->bufferLevel) ob_end_clean();
        TemplateIo::$mode = null;
        foreach (TemplateIo::$handles as $handle) if (is_resource($handle)) fclose($handle);
        TemplateIo::$handles = [];
        foreach ($this->saved as $name=>$value) {
            $p = new \ReflectionProperty(tmpl::class, $name); $p->setAccessible(true); $p->setValue(null, $value);
        }
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->root, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) { if ($file->isDir()) rmdir($file->getPathname()); else unlink($file->getPathname()); }
        rmdir($this->root);
    }
    private function file($name, $contents) { file_put_contents($this->root.'/'.$name, $contents); }
    private function render($design, $values = null, $class = TemplateController::class)
    {
        ob_start(); $class::display($design, $values); return ob_get_clean();
    }
    public function testFailingTemplatesCloseTheirOutputBuffers()
    {
        $this->file('tmpl/throws.md.php', '<?php throw new \\RuntimeException("template failure");');
        $level = ob_get_level();
        ob_start(); TemplateController::display('throws'); $output = ob_get_clean();
        $this->assertSame('', $output);
        $this->assertSame($level, ob_get_level());
        $this->file('tmpl/failure.html', 'small');
        TemplateIo::$mode = 'contents';
        try { TemplateController::format('failure'); $this->fail('expected a read failure'); }
        catch (\flat\core\controller\tmpl\exception\system_err $e) { $this->assertSame($level, ob_get_level()); }
    }
    public function testDesignRootUsesConfigurationOrItsDocumentedFallback()
    {
        $saved = [];
        foreach ((new \ReflectionClass(\flat\core\config::class))->getProperties(\ReflectionProperty::IS_STATIC) as $p) {
            $p->setAccessible(true); $saved[$p->getName()] = $p->getValue();
            $p->setValue(null,$p->getName() === 'base_dir' ? null : []);
        }
        try {
            $this->assertStringEndsWith('/app/Resources/design/tmpl',tmpl::get_design_root_dir());
            foreach (['base_dir'=>$this->root,'searched'=>['design/root_dir'=>true],'value'=>['design/root_dir'=>$this->root]] as $name=>$value) {
                $p = new \ReflectionProperty(\flat\core\config::class,$name); $p->setAccessible(true); $p->setValue(null,$value);
            }
            $this->assertSame($this->root,tmpl::get_design_root_dir());
        } finally {
            foreach ($saved as $name=>$value) { $p = new \ReflectionProperty(\flat\core\config::class,$name); $p->setAccessible(true); $p->setValue(null,$value); }
        }
    }
    public function testMarkdownReadFailureIsCaughtByTemplateResolver()
    {
        $this->file('tmpl/failed.md','# content'); TemplateIo::$mode = 'markdown';
        $this->assertSame('',$this->render('failed'));
    }
    public function testAbsolutePhpFileAndDisplayClass()
    {
        $this->file('absolute.php', '<?php echo "absolute";');
        $this->assertSame('absolute',$this->render($this->root.'/absolute.php'));
        $this->assertSame('display class',$this->render('\\'.DisplayClass::class));
        $this->assertTrue(TemplateController::check_design('\\'.DisplayClass::class));
    }
    /** @dataProvider fileFailures */
    public function testFileReadFailuresReportLibraryExceptions($mode,$large,$message)
    {
        $this->file('tmpl/failure.html',$large ? str_repeat('x',120000) : 'small');
        TemplateIo::$mode = $mode;
        $this->expectException(\flat\core\controller\tmpl\exception\system_err::class);
        $this->expectExceptionMessage($message);
        ob_start();
        try { TemplateController::display('failure'); } finally { ob_end_clean(); }
    }
    public function fileFailures()
    {
        return [['open',true,'could not open'], ['read',true,'could not read'],
            ['close',true,'failed to close'], ['contents',false,'could not read string']];
    }
    public function testPhpAndPlainHtmlFilesRenderAndCheckWithoutOutput()
    {
        $this->file('tmpl/php.php', '<?php echo "hello:".$data["title"];');
        $this->assertSame('hello:world', $this->render('php', ['title'=>'world']));
        $this->assertSame('hello:typed', $this->render('php', new data(['title'=>'typed'])));
        $this->assertTrue(TemplateController::check_design('php'));
        $this->file('tmpl/plain.html', '<p>plain</p>');
        $this->assertSame('<p>plain</p>', $this->render('plain'));
        $this->assertTrue(TemplateController::check_design('plain'));
        $this->file('tmpl/alternate.htm', 'alternate');
        $this->assertSame('alternate', $this->render('alternate'));
        $this->file('tmpl/large.html', str_repeat('0123456789', 12000));
        $this->assertSame(str_repeat('0123456789', 12000), $this->render('large'));
        $this->assertTrue(TemplateController::check_design('large'));
        $this->assertFalse(TemplateController::check_design('missing'));
    }
    public function testMarkdownAndGeneratedMarkdownFiles()
    {
        $this->file('tmpl/readme.md', '# Readme');
        $this->assertSame('<h1>Readme</h1>', $this->render('readme'));
        $this->assertTrue(TemplateController::check_design('readme'));
        $this->file('tmpl/text.txt', '**Text**');
        $this->assertSame('<p><strong>Text</strong></p>', $this->render('text'));
        foreach (['md.php', 'txt.php'] as $extension) {
            $this->file('tmpl/generated.'.$extension, '<?php echo "# Generated";');
            $this->assertSame('<h1>Generated</h1>', $this->render('generated'));
            $this->assertTrue(TemplateController::check_design('generated'));
            unlink($this->root.'/tmpl/generated.'.$extension);
        }
        $this->file('tmpl/throws.md.php', '<?php throw new \\RuntimeException("template failure");');
        $this->assertSame('', $this->render('throws'));
    }
    public function testObjectAndClassDesignsReceiveDataAndCheckOnlyFlag()
    {
        $output = new OutputDesign;
        $this->assertSame("<b>value</b>\n", $this->render($output, ['title'=>'value']));
        $this->assertTrue(TemplateController::check_design($output));
        $resolved = new ResolvedDesign;
        $this->assertSame('resolved:value', $this->render($resolved, ['title'=>'value']));
        $this->assertTrue(TemplateController::check_design($resolved));
        tmpl::set_design_class_prefix(__NAMESPACE__);
        $this->assertSame('resolved:class', $this->render('tmplResolvedDesign', ['title'=>'class']));
        $this->assertTrue(TemplateController::check_design('tmplResolvedDesign'));
        $this->assertSame('resolved:typed', $this->render('tmplResolvedDesign', new data(['title'=>'typed'])));
    }
    /** @dataProvider invalidDesigns */
    public function testInvalidDesignsAreRejected($design)
    {
        $this->expectException(\flat\core\controller\tmpl\exception\bad_design::class);
        TemplateController::display($design);
    }
    public function invalidDesigns() { return [[42], [null], [new \stdClass], ['absent']]; }
    public function testDesignBasesPrefixesAndSeparators()
    {
        $this->file('base/item.php', '<?php echo "based";');
        $this->assertSame('based', $this->render('item', null, BasedController::class));
        $this->assertSame('based', $this->render('base/item', null, BasedController::class));
        $this->file('tmpl/item.php', '<?php echo "prefixed";');
        $this->assertSame('prefixed', $this->render('/item'));
    }
    public function testFormattingFlagsReplacementsAndOutput()
    {
        $object = new OutputDesign;
        $this->assertSame("new&lt;br /&gt;\n", tmpl::format($object, ['no_display','strip_tags','nl2br','htmlescape'], ['data'=>['title'=>'old'],'replace'=>['old'=>'new']]));
        ob_start(); tmpl::format($object, [], ['data'=>['title'=>'echo']]);
        $this->assertSame("<b>echo</b>\n", ob_get_clean());
        $this->assertSame("<b>safe</b>\n", tmpl::format($object, ['silent_fail','no_display'], ['data'=>['title'=>'safe']]));
        $this->assertSame('', TemplateController::format('missing', ['silent_fail','no_display']));
    }
    public function testLoggerWritesDesignKindAndNormalizedName()
    {
        $path = $this->root.'/design.log';
        TemplateController::enable_design_logger(3, $path);
        $this->render(new OutputDesign, ['title'=>'logged']);
        $this->assertStringContainsString('tmpl design (object):', file_get_contents($path));
        TemplateController::disable_design_logger();
        $before = file_get_contents($path);
        $this->render(new ResolvedDesign, ['title'=>'quiet']);
        $this->assertSame($before, file_get_contents($path));
    }
    public function testConstructorAndPreparedInterfacesControlDisplayTiming()
    {
        $seen = [];
        tmpl::set_display_handler(function ($design, $data, $controller) use (&$seen) { $seen[] = [$design,$data,$controller]; });
        $explicit = new ExplicitController;
        $this->assertSame('explicit', $seen[0][0]);
        $this->assertInstanceOf(data::class, $seen[0][1]);
        $this->assertSame($explicit, $seen[0][2]);
        new IgnoredController; $this->assertCount(1, $seen);
        $provider = new ProviderController; $this->assertCount(1, $seen);
        $provider->set_prepared_on(); $this->assertSame(['title'=>'provided'], $seen[1][1]);
        (new IgnoredProvider)->set_prepared_on(); $this->assertCount(2, $seen);
        $optional = new OptionalProvider; $optional->set_prepared_on(); $this->assertCount(2, $seen);
        $optional->ignored = false; $optional->set_prepared_on(); $this->assertCount(3, $seen);
        $plain = new OptionalController; $plain->set_prepared_on(); $this->assertCount(3, $seen);
        $plain->ignored = false; $plain->set_prepared_on(); $this->assertCount(4, $seen);
        new TemplateController; $this->assertSame(__NAMESPACE__, $seen[4][0]);
        $explicit->set_prepared_on(); $this->assertCount(5, $seen);
    }
    public function testDefaultDisplayCallbackDelegatesToTemplateRendering()
    {
        $p = new \ReflectionProperty(tmpl::class, 'display_handler'); $p->setAccessible(true); $p->setValue(null, null);
        ob_start(); call_user_func(tmpl::get_display_handler(), new OutputDesign, ['title'=>'default']);
        $this->assertSame("<b>default</b>\n", ob_get_clean());
    }
}

namespace flat\core\controller;
function fopen($path,$mode)
{
    if (\flat\test\Unit\Core\Template\TemplateIo::$mode === 'open') return false;
    $handle = \fopen($path,$mode);
    if (\flat\test\Unit\Core\Template\TemplateIo::$mode !== null) \flat\test\Unit\Core\Template\TemplateIo::$handles[] = $handle;
    return $handle;
}
function fread($handle,$length)
{
    if (\flat\test\Unit\Core\Template\TemplateIo::$mode === 'read') return false;
    return \fread($handle,$length);
}
function fclose($handle)
{
    $result = \fclose($handle);
    return \flat\test\Unit\Core\Template\TemplateIo::$mode === 'close' ? false : $result;
}
function file_get_contents($path)
{
    if (\flat\test\Unit\Core\Template\TemplateIo::$mode === 'contents') return false;
    return \file_get_contents($path);
}

namespace flat\core\md\convert;
function file_get_contents($path)
{
    if (\flat\test\Unit\Core\Template\TemplateIo::$mode === 'markdown') return false;
    return \file_get_contents($path);
}
