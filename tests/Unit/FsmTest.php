<?php
namespace flat\test\Unit;
use PHPUnit\Framework\TestCase;

/** flat\fsm is an empty alpha definition (version 0.1.0-alpha); this only checks that it loads by its own name. */
class FsmTest extends TestCase
{
    public function testTheFiniteStateMachineDefinitionLoadsByItsPathName()
    {
        $class = new \ReflectionClass(\flat\fsm::class);
        $this->assertSame('flat\fsm', $class->getName());
        $this->assertTrue($class->isAbstract());
        $this->assertSame(realpath(__DIR__.'/../../src/flat/fsm.php'), realpath($class->getFileName()));
        $this->assertTrue($class->implementsInterface(\flat\core\app::class));
        $this->assertTrue($class->implementsInterface(\flat\core\routable::class));
    }
}
