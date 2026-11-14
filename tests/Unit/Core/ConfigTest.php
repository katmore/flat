<?php
namespace flat\test\Unit\Core;

use flat\core\config;
use PHPUnit\Framework\TestCase;

/** Filesystem fixtures exercise the public API without external services. */
class ConfigTest extends TestCase
{
    private $directory;
    private $saved = [];

    protected function setUp(): void
    {
        $class = new \ReflectionClass(config::class);
        foreach ($class->getProperties(\ReflectionProperty::IS_STATIC) as $property) {
            $property->setAccessible(true);
            $this->saved[$property->getName()] = $property->getValue();
            $property->setValue(null, $property->getName() === 'base_dir' ? null : []);
        }
        $this->directory = sys_get_temp_dir() . '/flat-config-' . bin2hex(random_bytes(8));
        mkdir($this->directory);
    }

    protected function tearDown(): void
    {
        foreach ($this->saved as $name => $value) {
            $property = new \ReflectionProperty(config::class, $name);
            $property->setAccessible(true);
            $property->setValue(null, $value);
        }
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            chmod($file->getPathname(), 0700);
            if ($file->isDir()) rmdir($file->getPathname());
            else unlink($file->getPathname());
        }
        rmdir($this->directory);
    }

    private function file($name, $contents)
    {
        $path = $this->directory . '/' . $name;
        if (!is_dir(dirname($path))) mkdir(dirname($path), 0700, true);
        file_put_contents($path, $contents);
        return $path;
    }

    private function values($name, array $values)
    {
        return $this->file($name . '.php', '<?php return ' . var_export($values, true) . ';');
    }

    private function ready()
    {
        config::set_base_dir($this->directory);
    }

    public function testDefaultsBeforeInitialization()
    {
        $this->assertSame('fallback', config::get('missing', ['default' => 'fallback']));
        $this->assertSame(false, config::get_ref_value('missing', 'a', ['default' => false]));
    }

    /** @dataProvider uninitializedCalls */
    public function testUninitializedAccessThrows($method, array $args)
    {
        $this->expectException(\flat\core\config\exception\not_ready::class);
        call_user_func_array([config::class, $method], $args);
    }

    public function uninitializedCalls()
    {
        return [['get', ['missing']], ['get_ref_value', ['missing', 'a']], ['enum_values', ['missing']]];
    }

    /** @dataProvider invalidBaseDirectories */
    public function testInvalidBaseDirectory($kind, $exception)
    {
        $path = $kind === 'empty' ? '' : $this->directory . '/missing';
        if ($kind === 'file') $path = $this->file('file.txt', 'contents');
        $this->expectException($exception);
        config::set_base_dir($path);
    }

    public function invalidBaseDirectories()
    {
        return [
            ['empty', \flat\core\config\exception\bad_base_dir\is_empty::class],
            ['missing', \flat\core\config\exception\bad_base_dir\not_readable::class],
            ['file', \flat\core\config\exception\bad_base_dir\not_dir::class],
        ];
    }

    public function testLoadsValuesAndCachesThem()
    {
        $this->values('settings', ['a' => 'first', 'b' => false, 'zero' => 0]);
        $this->ready();
        $this->assertSame('first', config::get('settings/a'));
        $this->assertFalse(config::get('settings/b'));
        $this->assertSame(0, config::get('settings/zero'));
        $this->values('settings', ['a' => 'changed']);
        $this->assertSame('first', config::get('settings/a'));
        $this->assertNull(config::get('settings/missing', ['not_found_exception' => false]));
    }

    public function testPathsAreNormalizedAndBaseNamesAreRemoved()
    {
        $this->values('settings', ['a' => 42]);
        $this->ready();
        $this->assertSame(42, config::get(' /settings\\a/ '));
        $this->assertSame(42, config::get('Prefix\\settings\\a', ['key_name_base' => 'Prefix\\']));
        $this->assertSame(42, config::get('Prefix\\settings\\a', ['key_name_base' => 'Prefix/']));
        $this->assertSame(42, config::get('settings/a', ['key_name_base' => 'Unrelated']));
        // The top-level "flat/" namespace is removed from the front of a key.
        $this->assertSame(42, config::get('flat/settings/a'));
        $this->assertSame(42, config::get(' \\flat\\settings\\a '));
        // Only the exact prefix counts.
        $this->assertSame('fallback', config::get('flatten/settings/a', ['default' => 'fallback']));
    }
    public function testEachConfigFileIsIncludedOnlyOnce()
    {
        $GLOBALS['flat_config_includes'] = 0;
        $this->file('counted.php', '<?php $GLOBALS["flat_config_includes"]++; return ["a"=>1,"b"=>2];');
        $this->ready();
        $this->assertSame(1, config::get('counted/a'));
        $this->assertSame(2, config::get('counted/b'));
        $this->assertSame(1, $GLOBALS['flat_config_includes']);
        unset($GLOBALS['flat_config_includes']);
    }

    public function testNonStringKeyThrows()
    {
        $this->ready();
        $this->expectException(\flat\core\config\exception\bad_key::class);
        config::get(123);
    }

    public function testMissingKeysAreCachedAndDefaultsPreserveTypes()
    {
        $this->ready();
        $this->assertNull(config::get('missing/a', ['not_found_exception' => false]));
        $this->assertSame(false, config::get('missing/a', ['default' => false]));
        $this->assertNull(config::get('missing/a', ['default' => null]));
        $this->assertSame(['a'], config::get('other/a', ['default' => ['a']]));
        $this->expectException(\flat\core\config\exception\key_not_found::class);
        config::get('missing/a');
    }

    public function testInvalidPhpConfigIsRejectedAndCanUseDefault()
    {
        $this->file('bad.php', '<?php return "wrong";');
        $this->ready();
        $this->assertSame('fallback', config::get('bad/a', ['default' => 'fallback']));
        $this->expectException(\flat\core\config\exception\bad_config_file::class);
        config::get('bad/a');
    }

    public function testDirectoriesAndUnreadableFilesAreNotLoadedAsConfig()
    {
        if (function_exists('posix_geteuid') && posix_geteuid() === 0) $this->markTestSkipped('root can read every file');
        mkdir($this->directory . '/folder.php');
        $locked = $this->values('locked', ['key' => 'secret']);
        chmod($locked, 0000);
        $this->ready();
        $this->assertSame('fallback', config::get('folder/key', ['default' => 'fallback']));
        $this->assertSame('fallback', config::get('locked/key', ['default' => 'fallback']));
    }

    public function testReferenceFilesAreReadOnlyOncePerNamespace()
    {
        $path = $this->file('refs.json', '{"host":"first"}');
        $this->ready();
        $this->assertSame('first', config::get_ref_value('refs', 'host'));
        file_put_contents($path, '{"host":"second"}');
        $this->assertSame('first', config::get_ref_value('refs', 'host'));
    }

    public function testEnumeratesValuesWithReferences()
    {
        $this->file('refs.json', '{"host":"localhost"}');
        $this->values('settings', ['a' => 1, 'host' => 'CONFIG-REF::refs.host']);
        $this->ready();
        $this->assertSame(['a' => 1, 'host' => 'localhost'], config::enum_values('settings'));
        $this->assertSame(['a' => 1, 'host' => 'localhost'], config::enum_values('settings'));
        $this->assertNull(config::enum_values('absent'));
        $this->assertNull(config::enum_values('absent'));
        $this->assertSame(['default'], config::enum_values('another', ['default' => ['default']]));
        $this->assertNull(config::enum_values('scalarDefault', ['default' => 123]));
        $this->assertNull(config::enum_ref_values('nested\\refs'));
    }

    public function testReferenceValuesAndNamespaceCaching()
    {
        $this->file('nested/refs.json', '{"host":"localhost","port":123,"nothing":null,"values":[1,2]}');
        $this->ready();
        $this->assertSame('localhost', config::get_ref_value('nested\\refs', 'host'));
        $this->assertSame('localhost', config::get_ref_value('nested\\refs', 'host'));
        $this->assertSame(123, config::get_ref_value('nested\\refs', 'port'));
        $this->assertNull(config::get_ref_value('nested\\refs', 'nothing'));
        $this->assertSame([1, 2], config::get_ref_value('nested\\refs', 'values'));
    }

    /** @dataProvider badReferenceDocuments */
    public function testRejectsInvalidReferenceDocuments($document, $exception)
    {
        $this->file('bad.json', $document);
        $this->ready();
        $this->expectException($exception);
        config::get_ref_value('bad', 'a');
    }

    public function badReferenceDocuments()
    {
        return [
            ['', \flat\core\config\exception\bad_config_file::class],
            ['{', \flat\core\config\exception\bad_config_file::class],
            ['[]', \flat\core\config\exception\bad_config_file::class],
            ['{"0":"numeric key"}', \flat\core\config\exception\bad_key::class],
        ];
    }

    public function testMissingReferenceDocumentAndCachedMisses()
    {
        $this->ready();
        foreach (['a', 'b', 'b'] as $key) {
            try {
                config::get_ref_value('missing', $key);
                $this->fail('Expected missing reference');
            } catch (\flat\core\config\exception\key_not_found $e) {
                $this->assertStringContainsString('missing.' . $key, $e->getMessage());
            }
        }
    }

    public function testWholeReferencesAndNestedValues()
    {
        $this->file('refs.json', '{"host":"localhost","port":123,"flag":true,"nothing":null}');
        $this->values('settings', [
            'nested' => ['plain' => 12, 'ref' => 'CONFIG-REF::refs.port'],
            'flag' => 'CONFIG-REF::refs.flag', 'nothing' => 'CONFIG-REF::refs.nothing',
            'noDot' => 'CONFIG-REF::invalid', 'emptyBase' => 'CONFIG-REF::.host',
            'emptyName' => 'CONFIG-REF::refs.',
        ]);
        $this->ready();
        $this->assertSame(['plain' => 12, 'ref' => 123], config::get('settings/nested'));
        $this->assertTrue(config::get('settings/flag'));
        $this->assertNull(config::get('settings/nothing'));
        $this->assertSame('CONFIG-REF::invalid', config::get('settings/noDot'));
        $this->assertSame('CONFIG-REF::.host', config::get('settings/emptyBase'));
        $this->assertSame('CONFIG-REF::refs.', config::get('settings/emptyName'));
    }

    public function testMissingWholeReferenceReportsTheOriginalKey()
    {
        $this->values('settings', ['a' => 'CONFIG-REF::missing.a']);
        $this->ready();
        $this->expectException(\flat\core\config\exception\key_not_found::class);
        $this->expectExceptionMessage('settings/a=CONFIG-REF::missing.a');
        config::get('settings/a');
    }

    public function testInlineReferencesIncludingDuplicatesBooleansAndMalformedTokens()
    {
        $this->file('refs.json', '{"host":"localhost","port":123,"yes":true,"no":false}');
        $this->values('settings', [
            'url' => '<%CONFIG-REF-STRING::refs.host%>:<%CONFIG-REF-STRING::refs.port%>/<%CONFIG-REF-STRING::refs.host%>',
            'bools' => '<%CONFIG-REF-STRING::refs.yes%>/<%CONFIG-REF-STRING::refs.no%>',
            'noDot' => '<%CONFIG-REF-STRING::bad%>',
            'unclosed' => '<%CONFIG-REF-STRING::refs.host',
        ]);
        $this->ready();
        $this->assertSame('localhost:123/localhost', config::get('settings/url'));
        $this->assertSame('true/false', config::get('settings/bools'));
        $this->assertSame('<%CONFIG-REF-STRING::bad%>', config::get('settings/noDot'));
        $this->assertSame('<%CONFIG-REF-STRING::refs.host', config::get('settings/unclosed'));
    }

    public function testNonScalarInlineReferenceThrows()
    {
        $this->file('refs.json', '{"list":[1,2]}');
        $this->values('settings', ['a' => '<%CONFIG-REF-STRING::refs.list%>']);
        $this->ready();
        $this->expectException(\flat\core\config\exception\non_scalar_inline_ref_value::class);
        config::get('settings/a');
    }
}
