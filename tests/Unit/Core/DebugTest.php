<?php
namespace flat\test\Unit\Core;
use flat\core\debug;
use flat\core\config;
use PHPUnit\Framework\TestCase;
require_once __DIR__.'/../../Support/PhpSubprocess.php';
class DebugCollectionProbe extends \flat\core\debug\display_data\collection
{
    public function validateValue($data) { return $this->_validate($data); }
    public function filterValue($data) { return $this->_filter($data); }
    public function successValue($data) { return $this->_success($data); }
}

class DebugTest extends TestCase
{
    use \flat\test\Support\PhpSubprocess;
    public function testDisplayCollectionHelperAcceptsOnlyDisplayData()
    {
        $collection = new DebugCollectionProbe; $data = new \flat\core\debug\display_data('text',[]);
        $this->assertTrue($collection->validateValue($data));
        $this->assertSame('data must be \\flat\\core\\debug\\display_data',$collection->validateValue(new \stdClass));
        $this->assertNull($collection->filterValue($data));
        $this->assertNull($collection->successValue($data));
    }
    private $saved = [];
    protected function setUp(): void
    {
        foreach ([debug::class, config::class] as $class) {
            foreach ((new \ReflectionClass($class))->getProperties(\ReflectionProperty::IS_STATIC) as $p) {
                $p->setAccessible(true);
                $this->saved[$class][$p->getName()] = $p->getValue();
            }
        }
        debug::set_suppress_off();
        $this->active(true);
        $this->set(debug::class, 'event_factory', null);
    }
    private function set($class, $name, $value)
    {
        $p = new \ReflectionProperty($class, $name); $p->setAccessible(true); $p->setValue(null, $value);
    }
    private function active($active)
    {
        // Simulate an already loaded startup configuration without changing its lifecycle.
        $this->set(config::class, 'base_dir', sys_get_temp_dir());
        $this->set(config::class, 'value', ['debug/active' => $active]);
        $this->set(config::class, 'searched', ['debug/active' => true]);
        $this->set(config::class, 'transformed_value', []);
    }
    protected function tearDown(): void
    {
        foreach ($this->saved as $class => $values) foreach ($values as $name => $value) $this->set($class, $name, $value);
    }
    public function testDefaultKillHandlerExitsWithConfiguredStatus()
    {
        list($status,$out,$err) = $this->subprocess('\\flat\\core\\debug::kill("message", ["exit_status"=>["exit_status"=>7]]);');
        $this->assertSame(7,$status);
        $this->assertStringContainsString('message:',$out); $this->assertStringContainsString('message',$out);
        $this->assertStringContainsString('exit status:</strong> 7',$out);
        $this->assertSame('',$err);
    }
    public function testKillHonorsAPlainExitStatus()
    {
        list($status,$out,$err) = $this->subprocess('\\flat\\core\\debug::kill("plain", ["exit_status"=>9]);');
        $this->assertSame(9,$status);
        $this->assertStringContainsString('exit status:</strong> 9',$out);
        $this->assertSame('',$err);
    }

    public function testKillSanitizesInvalidNestedExitStatusAndEmptyMessage()
    {
        list($status,$out,$err) = $this->subprocess('\\flat\\core\\debug::kill(null, ["exit_status"=>["exit_status"=>true]]);');
        $this->assertSame(0,$status); $this->assertStringNotContainsString('message:</strong>',$out);
        $this->assertStringContainsString('{none}',$out); $this->assertSame('',$err);
    }
    public function testReturningCustomKillHandlerReachesTheFinalExit()
    {
        list($status,$out,$err) = $this->subprocess('\\flat\\core\\debug::set_kill_handler(function () { echo "custom handler"; }); \\flat\\core\\debug::kill("message");');
        $this->assertSame(0,$status);
        $this->assertStringContainsString('custom handler',$out);
        $this->assertStringContainsString('end script: /flat/core/debug::kill()',$out);
    }
    public function testWebDisplayFormattingInAnIsolatedPlatformBranch()
    {
        $body = <<<'CHILD'
define('flat\core\PHP_SAPI','fpm-fcgi');
$property = new \ReflectionProperty(\flat\core\config::class,'base_dir'); $property->setAccessible(true); $property->setValue(null,__DIR__);
foreach (['searched'=>['debug/active'=>true], 'value'=>['debug/active'=>true]] as $name=>$value) { $property = new \ReflectionProperty(\flat\core\config::class,$name); $property->setAccessible(true); $property->setValue(null,$value); }
\flat\core\debug::display_handler(new \flat\core\debug\display_data('web message',null));
CHILD;
        list($status,$out,$err) = $this->subprocess($body);
        $this->assertSame(0,$status); $this->assertStringContainsString('<textarea>',$out);
        $this->assertStringContainsString('web message',$out); $this->assertSame('',$err);
    }
    public function testSuppressionAndInactiveConfigurationPreventDisplay()
    {
        $seen = [];
        debug::set_display_handler(function ($data) use (&$seen) { $seen[] = $data; });
        $this->assertFalse(debug::is_suppress_active());
        debug::set_suppress_on(); $this->assertTrue(debug::is_suppress_active());
        debug::display('hidden'); debug::dump('hidden');
        debug::set_suppress_off(); $this->active(false);
        debug::display('hidden'); debug::dump('hidden');
        debug::set_display_handler(function () { throw new \LogicException('inactive handler'); });
        ob_start(); debug::display_handler(new \flat\core\debug\display_data('hidden', null));
        $this->assertSame('', ob_get_clean());
        $this->assertSame([], $seen);
    }
    public function testDisplayDumpAndMessageEventsCarryCallerAndCreationTime()
    {
        $seen = [];
        debug::set_display_handler(function ($data) use (&$seen) { $seen[] = $data; });
        $start = microtime(true);
        debug::display('hello'); debug::dump(['key' => 'value'], 'payload', 1);
        debug::display(['nested' => 42]); debug::msg('message');
        $this->assertCount(4, $seen);
        $this->assertSame('hello', $seen[0]->str);
        $this->assertSame(__FILE__, $seen[0]->caller->file);
        $this->assertStringContainsString(self::class, $seen[0]->caller->object);
        $this->assertGreaterThanOrEqual($start, $seen[0]->get_created());
        $this->assertSame('payload', $seen[1]->str);
        $this->assertStringContainsString('value', $seen[1]->dump);
        $this->assertStringContainsString('nested', $seen[2]->dump);
        $this->assertSame('message', $seen[3]->str);
        $this->assertStringContainsString('hello', (string) $seen[0]);
    }
    public function testDisplayAndDumpTriggerOnlySupportedUserErrors()
    {
        debug::set_display_handler(function () {});
        $errors = [];
        set_error_handler(function ($severity, $message) use (&$errors) { $errors[] = [$severity, $message]; return true; });
        try {
            debug::display('notice', ['trigger_error' => E_USER_NOTICE, 'callers_offset' => 1]);
            debug::dump(42, 'warning', ['trigger_error' => E_USER_WARNING]);
            debug::display('', ['trigger_error' => E_USER_NOTICE]);
            debug::display('ignored', ['trigger_error' => E_WARNING]);
        } finally { restore_error_handler(); }
        $this->assertCount(3, $errors);
        $this->assertSame([E_USER_NOTICE, 'notice'], $errors[0]);
        $this->assertSame([E_USER_WARNING, 'warning'], $errors[1]);
        $this->assertStringContainsString('unlabled error triggered in', $errors[2][1]);
    }
    public function testDefaultCliHandlerDisplaysStructuredData()
    {
        ob_start();
        debug::display('default output');
        $output = ob_get_clean();
        $this->assertStringContainsString('default output', $output);
        $this->assertStringContainsString('caller', $output);
    }
    public function testDataDescriptionsUseTheMatchingFormat()
    {
        $this->assertSame('{empty string}', debug::data2str(''));
        $this->assertSame('0', debug::data2str('0'));
        $this->assertSame('{null}', debug::data2str(null));
        $this->assertSame('text', debug::data2str('text'));
        $this->assertSame('{object class: stdClass}', debug::data2str(new \stdClass));
        $this->assertSame('{array count: 2 elements}', debug::data2str([1, 2]));
        $this->assertSame('{integer} 42', debug::data2str(42));
        $this->assertSame('{boolean} false', debug::data2str(false));
        $this->assertSame('{orig length: 41 truncated string} '.str_repeat('x', 40), debug::data2str(str_repeat('x', 41)));
        $handle = fopen('php://memory', 'r+');
        try { $this->assertSame('{non-scalar resource}', debug::data2str($handle)); }
        finally { fclose($handle); }
    }
    public function testCustomKillHandlerReceivesCallerAndConvertedMessage()
    {
        $seen = null;
        debug::set_kill_handler(function ($caller, $message, $options) use (&$seen) {
            $seen = [$caller, $message, $options];
            // Stop before the process-terminating tail while preserving real handler execution.
            throw new \RuntimeException('test kill boundary');
        });
        try { debug::kill(['reason' => 'failure']); $this->fail('kill handler must run'); }
        catch (\RuntimeException $e) { $this->assertSame('test kill boundary', $e->getMessage()); }
        $this->assertSame(__FILE__, $seen[0]['file']);
        $this->assertStringContainsString('failure', $seen[1]);
        $this->assertSame([], $seen[2]);
    }
}
