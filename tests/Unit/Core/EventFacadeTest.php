<?php
namespace flat\test\Unit\Core;
use PHPUnit\Framework\TestCase;
class FacadeEventFixture extends \flat\event {}
class EventFacadeTest extends TestCase
{
    public function testFacadeSharesFactoryAndDeliversHandlerResults()
    {
        $property = new \ReflectionProperty(\flat\event::class,'_event'); $property->setAccessible(true);
        $saved = $property->getValue(); $property->setValue(null,null);
        $resolverProperty = new \ReflectionProperty(\flat\event\resolver::class,'_event'); $resolverProperty->setAccessible(true);
        $savedResolver = $resolverProperty->getValue(); $resolverProperty->setValue(null,null);
        try {
            $seen = []; FacadeEventFixture::set_data('stored','value');
            FacadeEventFixture::set_handler(function ($payload) { return $payload['call'].'-'.$payload['data']->stored; });
            FacadeEventFixture::add_listener(function ($data) use (&$seen) { $seen[]=$data; });
            FacadeEventFixture::trigger('input');
            $this->assertSame(['input-value'],$seen);
            $resolver = FacadeEventFixture::load('named-facade'); $resolved = [];
            $resolver->add_listener(function ($data) use (&$resolved) { $resolved[]=$data; });
            $resolver->trigger('local'); $this->assertSame(['local'],$resolved);
            $property->setValue(null,null);
            FacadeEventFixture::trigger(function () use (&$seen) { $seen[]='callback shortcut'; });
            $this->assertSame(['input-value','callback shortcut'],$seen);
        } finally { $property->setValue(null,$saved); $resolverProperty->setValue(null,$savedResolver); }
    }
}
