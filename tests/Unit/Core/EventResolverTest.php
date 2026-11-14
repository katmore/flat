<?php
namespace flat\test\Unit\Core;
use PHPUnit\Framework\TestCase;
use flat\event\resolver;
class EventResolverTest extends TestCase
{
    public function testSharedNamespaceDeliversHandlerResultsToListeners()
    {
        $property = new \ReflectionProperty(resolver::class, '_event'); $property->setAccessible(true);
        $saved = $property->getValue(); $property->setValue(null, null);
        try {
            $a = new resolver('resolver-unit'); $b = new resolver('resolver-unit'); $seen = [];
            $a->set_handler(function ($data) { return strtoupper($data); });
            $b->add_listener(function ($data) use (&$seen) { $seen[] = $data; });
            $a->trigger('first');
            $b->trigger('second', ['handler'=>function ($data) { return $data.'!'; }]);
            $this->assertSame(['FIRST', 'second!'], $seen);
            $this->assertNull($a->set_data('key', 'currently ignored'));
        } finally { $property->setValue(null, $saved); }
    }
}
