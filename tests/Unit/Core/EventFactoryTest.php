<?php
namespace flat\test\Unit\Core;
use flat\core\event\factory;
use PHPUnit\Framework\TestCase;
class NonTriggerableEvent extends \flat\core\event
{
    public function __construct($name, callable $handler = null, $data = null) { $this->set_params($name, $handler, $data); }
}
class EventFactoryTest extends TestCase
{
    public function testCreationHandlersListenersAndStoredData()
    {
        $factory = new factory; $calls = [];
        $this->assertTrue($factory->add_event('example', ['data' => 'event data']));
        $factory->set_handler('example', function ($data) use (&$calls) { $calls[] = ['handler', $data]; return 'result'; });
        $factory->add_listener('example', function ($value) use (&$calls) { $calls[] = ['listener', $value]; });
        $factory->trigger_event('example', 'call data');
        $this->assertSame(['call' => 'call data', 'event' => 'event data'], $calls[0][1]);
        $this->assertSame(['listener', 'result'], $calls[1]);
        $factory->set_data('stored', 'key', 'value');
        $factory->set_data('stored', 'key', 'ignored', true);
        $stored = null;
        $factory->add_listener('stored', function ($data) use (&$stored) { $stored = $data; });
        $factory->trigger_event('stored');
        $this->assertSame('value', $stored->key);
    }
    public function testOnDemandCreationAndPerTriggerCallbacks()
    {
        $factory = new factory; $seen = [];
        $factory->trigger_event('missing');
        $factory->trigger_event('created', 'input', [
            'handler' => function ($data) { return strtoupper($data); },
            'listener' => function ($data) use (&$seen) { $seen[] = $data; },
        ]);
        $this->assertSame(['INPUT'], $seen);
        $factory->trigger_event('created', 'again', ['handler' => 'not_callable', 'listener' => 'not_callable']);
        $this->assertSame(['INPUT', 'AGAIN'], $seen);
    }
    public function testDuplicateCanBeIgnored()
    {
        $factory = new factory;
        $this->assertTrue($factory->add_event('event'));
        $this->assertFalse($factory->add_event('event', ['ignore_if_exists' => true]));
    }
    public function testDuplicateRaisesSpecificException()
    {
        $factory = new factory; $factory->add_event('event');
        $this->expectException(\flat\core\event\factory\exception\already_exists::class);
        $factory->add_event('event');
    }
    public function testUnknownEventTypeRaisesSpecificException()
    {
        $this->expectException(\flat\core\event\factory\exception\type_not_found::class);
        (new factory)->add_event('event', ['type' => 'NoSuchFlatEventClass']);
    }
    public function testNonTriggerableTypeDoesNotInvokeHandler()
    {
        $factory = new factory;
        $this->assertTrue($factory->add_event('plain', ['type' => NonTriggerableEvent::class, 'handler' => function () { $this->fail('Non-triggerable event must not run'); }]));
        $this->assertNull($factory->trigger_event('plain'));
    }
}
