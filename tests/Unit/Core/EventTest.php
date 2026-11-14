<?php
namespace flat\test\Unit\Core;
use PHPUnit\Framework\TestCase;
class EventFixture extends \flat\core\event
{
    public function __construct($name = 'example', callable $handler = null, $data = null) { $this->set_params($name, $handler, $data); }
    public function fire($data = null) { return $this->call($data); }
}
class EventTest extends TestCase
{
    public function testNamedEmptyEventAndMultipleListeners()
    {
        $event = new EventFixture;
        $this->assertSame('example', $event->get_name());
        $this->assertNull($event->fire());
        $calls = [];
        $event->add_listener(function (...$args) use (&$calls) { $calls[] = ['first', $args]; });
        $event->add_listener(function (...$args) use (&$calls) { $calls[] = ['second', $args]; });
        $event->fire();
        $this->assertSame([['first', []], ['second', []]], $calls);
    }
    public function testHandlerReplacementRunsBeforeListeners()
    {
        $calls = [];
        $event = new EventFixture('name', function () { throw new \LogicException('replaced'); });
        $event->set_handler(function ($data) use (&$calls) { $calls[] = ['handler', $data]; return 'result'; });
        $event->add_listener(function ($data) use (&$calls) { $calls[] = ['listener', $data]; });
        $event->fire('input');
        $this->assertSame([['handler', 'input'], ['listener', 'result']], $calls);
    }
    public function testFailureAbortsListeners()
    {
        $event = new EventFixture('failure', function () { return false; });
        $event->add_listener(function () { $this->fail('Listener must not run after handler failure'); });
        $this->expectException(\flat\core\event\exception\failure::class);
        $this->expectExceptionMessage('event handler for failure indicated failure');
        $event->fire();
    }
    public function testCallAndEventAndStoredDataAreAggregatedForHandler()
    {
        $actual = null;
        $event = new EventFixture('event', function ($data) use (&$actual) { $actual = $data; }, 'event data');
        $event->set_data('key', 'first');
        $event->set_data('key', 'ignored', true);
        $event->set_data('other', 2);
        $event->fire('call data');
        $this->assertSame('call data', $actual['call']);
        $this->assertSame('event data', $actual['event']);
        $this->assertEquals((object) ['key' => 'first', 'other' => 2], $actual['data']);
        $event->set_data('key', 'replacement');
        $event->fire();
        $this->assertSame('replacement', $actual['data']->key);
    }
    /** @dataProvider listenerInputs */
    public function testListenerInputPriority($call, $data, $stored, $expected)
    {
        $received = [];
        $event = new EventFixture('priority', null, $data);
        if ($stored) $event->set_data('key', 'stored');
        $event->add_listener(function (...$args) use (&$received) { $received = $args; });
        $event->fire($call);
        $this->assertEquals($expected, $received);
    }
    public function listenerInputs()
    {
        return [
            ['call', null, false, ['call']],
            ['call', 'event', false, ['event']],
            ['call', 'event', true, [(object) ['key' => 'stored']]],
            [0, false, false, []],
        ];
    }
    public function testStoredNullCanBeReplacedWithNoOverwriteFlag()
    {
        $event = new EventFixture;
        $event->set_data('key', null);
        $event->set_data('key', 'later', true);
        $seen = null;
        $event->add_listener(function ($data) use (&$seen) { $seen = $data; });
        $event->fire();
        $this->assertSame('later', $seen->key);
    }
    /** @dataProvider invalidNames */
    public function testInvalidName($name, $exception)
    {
        $this->expectException($exception);
        new EventFixture($name);
    }
    public function invalidNames()
    {
        return [['', \flat\core\event\exception\invalid_name\cannot_be_empty::class], [123, \flat\core\event\exception\invalid_name\is_not_string::class]];
    }
    /** @dataProvider invalidKeys */
    public function testInvalidDataKey($key, $exception)
    {
        $this->expectException($exception);
        (new EventFixture)->set_data($key, 1);
    }
    public function invalidKeys()
    {
        return [['', \flat\core\event\exception\invalid_key\cannot_be_empty::class], [123, \flat\core\event\exception\invalid_key\is_not_string::class]];
    }
}
