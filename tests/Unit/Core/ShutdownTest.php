<?php
namespace flat\test\Unit\Core;
require_once __DIR__.'/../../Support/PhpSubprocess.php';
use PHPUnit\Framework\TestCase;
use flat\core\shutdown;
class ShutdownTest extends TestCase
{
    use \flat\test\Support\PhpSubprocess;
    private $saved = [];
    protected function setUp(): void
    {
        foreach ((new \ReflectionClass(shutdown::class))->getProperties(\ReflectionProperty::IS_STATIC) as $property) {
            $property->setAccessible(true); $this->saved[$property->getName()] = $property->getValue();
            $property->setValue(null, $property->getName() === '_init' ? false : ($property->getName() === '_weight_max' ? 0 : null));
        }
    }
    protected function tearDown(): void
    {
        foreach ($this->saved as $name=>$value) {
            $property = new \ReflectionProperty(shutdown::class, $name); $property->setAccessible(true); $property->setValue(null, $value);
        }
    }
    public function testNamedPrioritiesRunInDocumentedOrderAndHandlesAreUnique()
    {
        $seen = []; $handles = [];
        foreach ([['normal',null], ['last','last'], ['first','first'], ['first2','first'], ['normal2','default'], ['fallback','unknown']] as $entry) {
            list($name,$priority) = $entry;
            $handles[] = shutdown::register(function () use (&$seen,$name) { $seen[] = $name; }, $priority, 'local-context');
        }
        shutdown::invoke_shutdown_callbacks();
        $this->assertSame(['first','first2','normal','normal2','fallback','last'], $seen);
        $this->assertCount(6, array_unique($handles));
    }
    public function testZeroAndOutOfRangeWeightsCurrentlyJoinTheDefaultQueue()
    {
        $seen = [];
        foreach ([0,51,-51,false] as $weight) shutdown::register(function () use (&$seen,$weight) { $seen[] = $weight; },$weight);
        shutdown::invoke_shutdown_callbacks();
        $this->assertSame([0,51,-51,false], $seen);
    }
    public function testDistinctWeightsRunBeforeDefaultAndAfterFirst()
    {
        $seen = [];
        foreach ([['default',null], ['high',50], ['low',-50], ['first','first'], ['last','last']] as $entry) {
            list($name,$weight) = $entry; shutdown::register(function () use (&$seen,$name) { $seen[] = $name; }, $weight);
        }
        shutdown::invoke_shutdown_callbacks();
        $this->assertSame(['first','low','high','default','last'], $seen);
    }
    public function testRepeatedInvocationCurrentlyRepeatsCallbacks()
    {
        $calls = 0; shutdown::register(function () use (&$calls) { $calls++; });
        shutdown::invoke_shutdown_callbacks(); shutdown::invoke_shutdown_callbacks();
        $this->assertSame(2, $calls);
    }
    public function testEqualWeightsRunNewestFirstWithoutLosingCallbacks()
    {
        $seen = [];
        foreach (['a','b','c','d','e'] as $name) shutdown::register(function () use (&$seen,$name) { $seen[] = $name; },1);
        shutdown::invoke_shutdown_callbacks();
        $this->assertSame(['e','d','c','b','a'], $seen);
    }
    public function testRegisteredCallbackRunsAtActualPhpShutdown()
    {
        list($status,$out,$err) = $this->subprocess('\flat\core\shutdown::register(function () { echo "actual shutdown"; });');
        $this->assertSame(0,$status,$err); $this->assertSame('',$err); $this->assertSame('actual shutdown',$out);
    }
    public function testWeightCeilingStopsUnboundedInsertion()
    {
        $this->expectException(shutdown\exception\weight_ceiling::class);
        for ($i=0; $i<shutdown::weight_ceiling; $i++) shutdown::register(function () {},1);
    }
}
