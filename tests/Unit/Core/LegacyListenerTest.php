<?php
namespace flat\test\Unit\Core;
use PHPUnit\Framework\TestCase;
class ConstructorListenerFixture extends \flat\core\event\listener
{
    public $seen;
    protected function callback($data,$event_data) { $this->seen = [$data,$event_data]; }
}
class HookableFixture extends \flat\core\event\hookable
{
    public function resetParameters($name,$hook,$handler,$data) { $this->set_hookable_params($name,$hook,$handler,$data); }
    public function fire() { return $this->call(); }
}
class ListenerFactoryFixture extends \flat\core\event\listener\factory
{
    protected function _add_listeners() {}
    public function suppliedData() { return $this->data; }
}
class LegacyListenerTest extends TestCase
{
    public function testListenerFactoryUsesTheExistingLoaderContractAndSharedFactory()
    {
        $factory = new \flat\core\event\factory; $data = (object)['context'=>'local'];
        $loader = new ListenerFactoryFixture($factory,$data); $seen = [];
        $this->assertInstanceOf(\flat\core\event\factory\loader::class,$loader);
        $this->assertSame($data,$loader->suppliedData());
        $loader->add_listener('factory-event',function ($payload) use (&$seen) { $seen[]=$payload; });
        $factory->trigger_event('factory-event','payload');
        $this->assertSame(['payload'],$seen);
    }
    public function testHookableCurrentlyDropsEventDataAndReportsItsUndefinedLocal()
    {
        $notices = []; $previous = null;
        $previous = set_error_handler(function ($severity,$message,$file,$line) use (&$notices,&$previous) {
            if (strpos($file,'/event/hookable.php') !== false && strpos($message,'event_data') !== false) { $notices[]=$message; return true; }
            return $previous ? $previous($severity,$message,$file,$line) : false;
        });
        $seen = []; $handler = function (...$args) use (&$seen) { $seen[]=$args; };
        try {
            $event = new HookableFixture('original','target',$handler,'ignored data');
            $this->assertSame('original',$event->get_name()); $event->fire();
            $event->resetParameters('updated','target',$handler,'also ignored');
            $this->assertSame('updated',$event->get_name()); $event->fire();
        } finally { restore_error_handler(); }
        $this->assertSame([[],[]],$seen);
        $this->assertCount(2,$notices);
    }
    public function testListenerConstructorImmediatelyInvokesItsCallback()
    {
        $data = new \stdClass;
        $this->assertSame([$data,'event'],(new ConstructorListenerFixture($data,'event'))->seen);
    }
    public function testStubCurrentlyReadsASeparateUndeclaredListenerProperty()
    {
        $stub = new \flat\core\event\stub('local-stub'); $callback = function () {};
        $stub->add_listener($callback);
        $notices = []; $previous = null;
        $previous = set_error_handler(function ($severity,$message,$file,$line) use (&$notices,&$previous) {
            if (strpos($file,'/event/stub.php') !== false && strpos($message,'listeners') !== false) { $notices[]=$message; return true; }
            return $previous ? $previous($severity,$message,$file,$line) : false;
        });
        try { $this->assertSame([],$stub->get_listeners()); }
        finally { restore_error_handler(); }
        $this->assertCount(1,$notices);
        $stub->listeners = [$callback];
        $this->assertSame([$callback],$stub->get_listeners());
    }
}
