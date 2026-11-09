<?php
namespace flat\test\Unit\Asset;
require_once __DIR__.'/../../Support/AssetIoBoundary.php';
use PHPUnit\Framework\TestCase;
use flat\asset;
class LocalAsset extends asset implements \flat\asset\base, \flat\asset\system_base
{
    public static $root;
    public function _get_base() { return ' https://example.com/static/'; }
    public function _get_system_base() { return self::$root; }
}
class UncheckedSystemAsset extends LocalAsset
{
    public function get_system(int $flags = null) { return self::$root.'/'.$this->get_resource_uri(); }
}
class EmptyBaseAsset extends LocalAsset { public function _get_system_base() { return ''; } }
class TransformAsset extends LocalAsset implements \flat\asset\resource\transform
{
    public function get_resource_transform($resource) { return str_replace('old', 'new', $resource); }
}
class UnchangedAsset extends LocalAsset implements \flat\asset\resource\transform
{
    public function get_resource_transform($resource) { return null; }
}
class BuiltinAsset extends LocalAsset
{
    public static $first = 0;
    protected static function on_first_instantiation(): void { self::$first++; }
    public static function get_builtin_dependencies(): array { return ['builtin.js', new LocalAsset('external.css'), 42]; }
}
class UnknownTypeAsset extends LocalAsset { public function get_resource_type(): int { return asset::RESOURCE_UNKNOWN; } }
class BrokenUrlAsset extends asset { public function get_url() { throw new \RuntimeException('broken url'); } }
class FacebookProfileAsset extends \flat\asset\facebook { protected function _get_facebook_username() { return 'local-user'; } }
class TwitterProfileAsset extends \flat\asset\twitter { protected function _get_twitter_username() { return 'local-user'; } }
class AssetTest extends TestCase
{
    private $saved = [];
    private $root;
    private $bufferLevel;
    protected function setUp(): void
    {
        $this->bufferLevel = ob_get_level();
        foreach ([asset::class, \flat\core\controller\asset::class] as $class) {
            foreach ((new \ReflectionClass($class))->getProperties(\ReflectionProperty::IS_STATIC) as $p) {
                $p->setAccessible(true); $this->saved[$class][$p->getName()] = $p->getValue();
                $p->setValue(null, $class === asset::class ? [] : null);
            }
        }
        $this->root = sys_get_temp_dir().'/flat-assets-'.bin2hex(random_bytes(8)); mkdir($this->root);
        LocalAsset::$root = $this->root; BuiltinAsset::$first = 0;
        file_put_contents($this->root.'/app.js', '  var app = 1;  ');
        file_put_contents($this->root.'/app.css', '  body {color: red;}  ');
        file_put_contents($this->root.'/unknown.bin', "\x00\x01\x02");
    }
    protected function tearDown(): void
    {
        \flat\test\Support\AssetIoBoundary::$failedPath = null;
        while (ob_get_level() > $this->bufferLevel) ob_end_clean();
        foreach ($this->saved as $class=>$values) foreach ($values as $name=>$value) {
            $p = new \ReflectionProperty($class, $name); $p->setAccessible(true); $p->setValue(null, $value);
        }
        foreach (glob($this->root.'/*') as $file) { chmod($file,0700); if (is_dir($file)) rmdir($file); else unlink($file); }
        rmdir($this->root);
    }
    private function output(callable $callback) { ob_start(); $callback(); return ob_get_clean(); }
    public function testSocialProfileAssetsResolveUserBasesAndIgnoreResources()
    {
        $facebook = new FacebookProfileAsset('ignored'); $twitter = new TwitterProfileAsset('ignored');
        $this->assertSame('https://www.facebook.com/local-user',$facebook->_get_base());
        $this->assertSame('https://twitter.com/local-user',$twitter->_get_base());
        $this->assertSame('',$facebook->get_resource_transform('other'));
        $this->assertSame('',$twitter->get_resource_transform('other'));
    }
    private function registered($filter = 0)
    {
        $seen = [];
        asset::each_registered_asset(function ($asset, $position, $parent = null) use (&$seen) { $seen[] = [$asset,$position,$parent]; }, $filter);
        return $seen;
    }
    public function testUrlsResourcesHashesAndInvocation()
    {
        $asset = new LocalAsset('/folder\\app.js');
        $this->assertSame('folder/app.js', $asset->get_resource_uri());
        $this->assertSame('https://example.com/static/folder/app.js', $asset->get_url());
        $this->assertSame($asset->get_url(), (string) $asset);
        $this->assertSame(asset::RESOURCE_JS, $asset->get_resource_type());
        $this->assertSame('js', $asset->get_pathinfo(PATHINFO_EXTENSION));
        $this->assertSame(asset::RESOURCE_JS.'.'.LocalAsset::class.'.folder/app.js', $asset->get_hash());
        $this->assertTrue($asset->is_equal(new LocalAsset('folder/app.js')));
        $this->assertFalse($asset->is_equal(new asset('folder/app.js')));
        $this->assertSame('https://example.com/static/app.css', $asset('app.css'));
        $this->assertSame('https://example.com/static/app.css', $asset('app.css'));
        $this->assertSame('https://example.com/static/', (new LocalAsset)->get_url());
        $this->assertSame('relative.png', (new asset('relative.png'))->get_url());
        $this->assertSame(asset::RESOURCE_UNKNOWN, (new asset('relative.png'))->get_resource_type());
        $this->assertSame(asset::RESOURCE_UNKNOWN, (new asset)->get_resource_type());
        $this->assertSame('new.js', (new TransformAsset('old.js'))->get_resource_uri());
        $this->assertSame('old.js', (new UnchangedAsset('old.js'))->get_resource_uri());
    }
    public function testExplicitHashesAndInlineScriptSource()
    {
        $script = 'window.value = 0;';
        $asset = new LocalAsset($script, ['type'=>asset::RESOURCE_JS_SOURCE]);
        $this->assertSame(sha1($script), $asset->get_hash());
        $this->assertSame($script, (string) $asset);
        $invoked = new LocalAsset('app.js');
        $this->assertSame($script, $invoked($script, ['type'=>asset::RESOURCE_JS_SOURCE]));
        $this->assertStringContainsString('<script>'.$script.'</script>', $this->output(function () use ($asset) { $asset->print_tag(); }));
        $this->assertStringContainsString($script, $this->output(function () use ($invoked) { $invoked->print_contents(asset::RESOURCE_JS_SOURCE); }));
        $fixed = new LocalAsset('app.js', ['hash'=>'fixed']); $this->assertSame('fixed', $fixed->get_hash());
        $resolved = LocalAsset::asset('app.js', ['hash'=>'resolved']); $this->assertSame('resolved', $resolved->get_hash());
        $prefix = LocalAsset::asset('app.js', ['hash_handle_prefix'=>'prefix']); $this->assertStringStartsWith('prefix.'.LocalAsset::class.'.', $prefix->get_hash());
        $this->assertSame('app.js', asset::asset('app.js', 'ignored')->get_url());
    }
    public function testFilesystemPathsExistenceAndDataUris()
    {
        $asset = new LocalAsset('app.js');
        $this->assertSame($this->root.'/app.js', $asset->get_system());
        $this->assertSame(realpath($this->root.'/app.js'), $asset->get_system(asset::SYSTEM_PATH_FILE|asset::SYSTEM_PATH_ABSOLUTE));
        $this->assertTrue($asset->exists_on_system());
        $this->assertFalse((new LocalAsset('absent'))->exists_on_system());
        $this->assertNull((new asset('app.js'))->get_system());
        $this->assertSame('data:text/plain;base64,'.base64_encode('  var app = 1;  '), $this->output(function () use ($asset) { $asset->print_data_uri('text/plain'); }));
        $this->assertStringContainsString(';base64,', $this->output(function () use ($asset) { $asset->print_data_uri(); }));
    }
    /** @dataProvider invalidSystemPaths */
    public function testFilesystemPathFailures($kind, $flags, $message)
    {
        $asset = new LocalAsset('absent');
        if ($kind === 'no-base') $asset = new asset;
        if ($kind === 'empty-base') $asset = new EmptyBaseAsset;
        if ($kind === 'directory') { mkdir($this->root.'/dir'); $asset = new LocalAsset('dir'); }
        if ($kind === 'unreadable') { chmod($this->root.'/app.js',0000); $asset = new LocalAsset('app.js'); }
        $this->expectException(\flat\lib\exception\app_error::class); $this->expectExceptionMessage($message);
        $asset->get_system($flags);
    }
    public function invalidSystemPaths()
    {
        return [['no-base',asset::SYSTEM_PATH_EXISTS,'must be instance'], ['empty-base',0,'empty value'],
            ['missing',asset::SYSTEM_PATH_EXISTS,'does not exist'], ['directory',asset::SYSTEM_PATH_FILE,'not a file'],
            ['unreadable',asset::SYSTEM_PATH_EXISTS,'permission is denied']];
    }
    public function testLinkedTagsFileContentsAndDeprecatedAliases()
    {
        $js = new LocalAsset('app.js'); $css = new LocalAsset('app.css');
        $this->assertStringContainsString('<script src="https://example.com/static/app.js"></script>', $this->output(function () use ($js) { $js->print_tag(); }));
        $this->assertStringContainsString('rel="stylesheet"', $this->output(function () use ($css) { $css->print_tag(); }));
        $this->assertStringContainsString('var app = 1;', $this->output(function () use ($js) { $js->print_contents(); }));
        $this->assertStringContainsString('<style>', $this->output(function () use ($css) { $css->print_contents(); }));
        $this->assertStringContainsString('script src', $this->output(function () { (new UnknownTypeAsset('app.js'))->print_tag(); }));
        $this->assertSame('', $this->output(function () { (new asset('unknown.bin'))->print_tag(); (new asset)->print_contents(); (new asset)->print_contents_script_tag(); }));
        foreach (['script_tag','style_link','print_script','print_style'] as $method) {
            $this->assertStringContainsString('app.js', $this->output(function () use ($js,$method) { $js->$method(); }));
        }
    }
    public function testInvalidFileContentTypeIsRejected()
    {
        $asset = new LocalAsset('unknown.bin');
        $this->expectException(\flat\lib\exception\app_error::class); $this->expectExceptionMessage('valid extension or media type');
        ob_start();
        try { $asset->print_contents_style_tag(); } finally { ob_end_clean(); }
    }
    public function testDirectoryFlagsRequireDirectoriesAndAcceptRealDirectories()
    {
        mkdir($this->root.'/dir');
        $this->assertSame($this->root.'/dir',(new LocalAsset('dir'))->get_system(asset::SYSTEM_PATH_DIR));
        $this->expectException(\flat\lib\exception\app_error::class);
        $this->expectExceptionMessage('system path is not a directory');
        (new LocalAsset('app.js'))->get_system(asset::SYSTEM_PATH_DIR);
    }
    /** @dataProvider outputFailures */
    public function testOverriddenSystemPathsAndReadFailuresRemainGuarded($kind,$message)
    {
        $asset = new UncheckedSystemAsset($kind === 'missing' ? 'missing.js' : 'app.js');
        if ($kind === 'permissions') chmod($this->root.'/app.js',0000);
        if ($kind === 'read') \flat\test\Support\AssetIoBoundary::$failedPath = $this->root.'/app.js';
        $this->expectException(\flat\lib\exception\app_error::class); $this->expectExceptionMessage($message);
        ob_start(); try { $asset->print_contents_script_tag(); } finally { ob_end_clean(); }
    }
    public function outputFailures() { return [['missing','file not found'],['permissions','file not readable'],['read','failed to read']]; }
    public function testExplicitCssContentAndExtensionFallbackTags()
    {
        $this->assertStringContainsString('body {color: red;}',$this->output(function () { (new LocalAsset('app.css'))->print_contents(asset::RESOURCE_CSS); }));
        $this->assertStringContainsString('rel="stylesheet"',$this->output(function () { (new UnknownTypeAsset('app.css'))->print_tag(); }));
    }
    public function testHtmlSpecialAcceptsImplicitPositionAndSingleItemsWithoutExecutingCycles()
    {
        $this->assertInstanceOf(asset::class,asset::register_html_special('implicit'));
        $this->assertInstanceOf(asset::class,asset::register_html_special(asset::POSITION_HEAD,'single'));
        asset::register_html('typed',['hash'=>'typed','type'=>asset::RESOURCE_CSS]);
        asset::register_html('invalid position',['hash'=>'invalid','type'=>asset::RESOURCE_JS,'position'=>123]);
        $seen = [];
        // Cyclic special-output entries above are deliberately not iterated.
        $this->assertCount(4,$this->registeredHashes());
    }
    private function registeredHashes()
    {
        $p = new \ReflectionProperty(asset::class,'register_hash'); $p->setAccessible(true); return $p->getValue();
    }
    /** @group known-defect */
    public function testUnimplementedResourceAndTagCasesCurrentlyProduceNoOutput()
    {
        $this->assertSame('',$this->output(function () { (new LocalAsset('unknown.bin'))->print_contents(); }));
        $this->assertSame('',$this->output(function () { (new LocalAsset('app.css'))->print_tag(asset::TAG_STYLE_SOURCE); }));
    }
    public function testExplicitOutputAssetsResourcesAndCallbacksAreRegistered()
    {
        $given = new LocalAsset('given.js');
        asset::register_html('asset output',['asset'=>$given,'hash'=>'asset output']);
        asset::register_html('resource output',['resource'=>'resource.js','hash'=>'resource output']);
        asset::register_html(function () { echo 'callback output'; },['hash'=>'callback']);
        $output=$this->output(function () { asset::print_registered_assets(); });
        $this->assertStringContainsString('asset output',$output); $this->assertStringContainsString('resource output',$output);
        $this->assertStringContainsString('callback output',$output);
    }
    public function testRegistrationsDeduplicateAndRespectResourceAndPositionFilters()
    {
        $js = LocalAsset::register_asset('app.js'); $css = LocalAsset::register_asset('app.css');
        $this->assertSame($js, $js->register());
        $this->assertCount(2, $this->registered());
        $this->assertSame([$css,asset::POSITION_HEAD,null], $this->registered(asset::RESOURCE_CSS)[0]);
        $this->assertSame([$js,asset::POSITION_BODY_AFTER_CONTENT,null], $this->registered(asset::POSITION_BODY_AFTER_CONTENT)[0]);
        $this->assertSame([], $this->registered(asset::POSITION_BODY_BEFORE_CONTENT));
        $this->assertStringContainsString('app.css', $this->output(function () { asset::print_registered_tags(asset::POSITION_HEAD); }));
        $this->assertStringContainsString('app.js', $this->output(function () { asset::print_registered_assets(asset::RESOURCE_JS); }));
        $unknown = LocalAsset::register_asset('other.bin', ['resource'=>'unknown.bin','position'=>asset::POSITION_BODY_BEFORE_CONTENT]);
        $this->assertSame('unknown.bin', $unknown->get_resource_uri());
        $this->assertCount(1, $this->registered(asset::POSITION_BODY_BEFORE_CONTENT));
        LocalAsset::register_asset('fallback.bin'); $this->assertCount(2, $this->registered(asset::POSITION_HEAD));
    }
    /** @dataProvider badPositions */
    public function testInvalidRegistrationPositionFails($position)
    {
        $this->expectException(\flat\lib\exception\bad_param::class);
        LocalAsset::register_asset('app.js', ['position'=>$position]);
    }
    public function badPositions() { return [['bad'], [12345]]; }
    public function testDependenciesRunBeforeParentsAndAreOnlyEmittedOnce()
    {
        $parent = new LocalAsset('parent.js'); $dependency = new LocalAsset('dependency.js');
        $parent->register(['depends'=>[$dependency]]); $dependency->register();
        $seen = $this->registered();
        $this->assertSame($dependency, $seen[0][0]); $this->assertSame($parent, $seen[0][2]);
        $this->assertSame($parent, $seen[1][0]); $this->assertCount(2, $seen);
        $parent->depends(asset::POSITION_HEAD, function ($asset) { echo 'dependency callback:'.$asset->get_resource_uri(); });
        $this->assertStringContainsString('dependency callback:parent.js', $this->output(function () { asset::print_registered_assets(); }));
        $parent->bundle('main')->bundle('main'); $called = false;
        asset::each_bundle_asset('main', function () use (&$called) { $called = true; });
        $this->assertFalse($called); // Current bundle iterator is a declared stub.
    }
    public function testHtmlCallbacksAndArraysAreRegistered()
    {
        asset::register_html('first', ['hash'=>'first']);
        asset::register_html('head', ['position'=>asset::POSITION_HEAD]);
        asset::register_html('special');
        asset::register_html(42);
        $output = $this->output(function () { asset::print_registered_assets(); });
        $this->assertStringContainsString('first', $output); $this->assertStringContainsString('head', $output);
        $this->assertStringContainsString('special', $output);

    }
    /** @group known-defect */
    public function testMultipleHtmlItemsCurrentlyCreateAnAccidentalSelfDependency()
    {
        $asset = asset::register_html_special(asset::POSITION_HEAD, 'first', 'second');
        $p = new \ReflectionProperty(asset::class, 'reigster_depends'); $p->setAccessible(true);
        $dependencies = $p->getValue();
        $this->assertSame($asset, $dependencies[$asset->get_hash()][$asset->get_hash()]);
        // Do not execute this cyclic queue: behavior repair is awaiting owner input.
    }
    /** @group known-defect */
    public function testNewInlineSourceCurrentlyCannotResolveAFilePath()
    {
        $this->expectException(\TypeError::class);
        (new LocalAsset('code', ['type'=>asset::RESOURCE_JS_SOURCE]))->print_contents();
    }
    public function testBuiltinDependenciesAreRegisteredOncePerAssetClass()
    {
        new BuiltinAsset('one.js'); new BuiltinAsset('two.js');
        $this->assertSame(1, BuiltinAsset::$first);
        $this->assertCount(2, $this->registered());
    }
    public function testResolvedHandlerCanRewriteResources()
    {
        asset::set_resolved_handler(function ($asset, $resource) { return 'rewritten/'.$resource; });
        $this->assertSame('rewritten/app.js', (new asset('app.js'))->get_resource_uri());
        asset::set_resolved_handler(function () { return null; });
        $this->assertSame('app.js', (new asset('app.js'))->get_resource_uri());
    }
    public function testStringConversionReportsUrlFailureAsNotice()
    {
        $message = null;
        set_error_handler(function ($level,$text) use (&$message) { $message = $text; return true; });
        try { $this->assertSame('', (string) new BrokenUrlAsset); }
        finally { restore_error_handler(); }
        $this->assertStringContainsString('broken url', $message);
    }
}
