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
    public function testHookablePassesItsEventDataToTheHandler()
    {
        $seen = []; $handler = function (...$args) use (&$seen) { $seen[]=$args; };
        $event = new HookableFixture('original','target',$handler,'first data');
        $this->assertSame('original',$event->get_name()); $event->fire();
        $event->resetParameters('updated','target',$handler,'second data');
        $this->assertSame('updated',$event->get_name()); $event->fire();
        $this->assertSame([['first data'],['second data']],$seen);
    }
    public function testListenerConstructorImmediatelyInvokesItsCallback()
    {
        $data = new \stdClass;
        $this->assertSame([$data,'event'],(new ConstructorListenerFixture($data,'event'))->seen);
    }
    public function testStubReturnsTheListenersItWasGiven()
    {
        $stub = new \flat\core\event\stub('local-stub');
        $this->assertSame([],$stub->get_listeners());
        $first = function () {}; $second = function () {};
        $stub->add_listener($first); $stub->add_listener($second);
        $this->assertSame([$first,$second],$stub->get_listeners());
    }
}
