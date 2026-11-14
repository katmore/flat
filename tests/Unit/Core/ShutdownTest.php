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
    public function testZeroIsAWeightAndEmptyValuesJoinTheDefaultQueue()
    {
        $seen = [];
        foreach ([['default',null],['false',false],['zero',0],['minus',-1],['plus',1],['first','first'],['last','last']] as $entry) {
            list($name,$priority) = $entry;
            shutdown::register(function () use (&$seen,$name) { $seen[] = $name; },$priority);
        }
        shutdown::invoke_shutdown_callbacks();
        $this->assertSame(['first','minus','zero','plus','default','false','last'], $seen);
    }
    /** @dataProvider outOfRangeWeights */
    public function testOutOfRangeWeightsAreRejected($weight)
    {
        $this->expectException(shutdown\exception\bad_priority::class);
        $this->expectExceptionMessage('(int) -50 to 50');
        shutdown::register(function () {},$weight);
    }
    public function outOfRangeWeights() { return [[51],[-51],[1000]]; }
    public function testDistinctWeightsRunBeforeDefaultAndAfterFirst()
    {
        $seen = [];
        foreach ([['default',null], ['high',50], ['low',-50], ['first','first'], ['last','last']] as $entry) {
            list($name,$weight) = $entry; shutdown::register(function () use (&$seen,$name) { $seen[] = $name; }, $weight);
        }
        shutdown::invoke_shutdown_callbacks();
        $this->assertSame(['first','low','high','default','last'], $seen);
    }
    public function testEachCallbackRunsOnlyOnceAcrossRepeatedInvocation()
    {
        $seen = [];
        foreach ([['first','first'], ['weighted',5], ['default',null], ['last','last']] as $entry) {
            list($name,$priority) = $entry;
            shutdown::register(function () use (&$seen,$name) { $seen[] = $name; }, $priority);
        }
        shutdown::invoke_shutdown_callbacks();
        shutdown::invoke_shutdown_callbacks();
        $this->assertSame(['first','weighted','default','last'], $seen);
        shutdown::register(function () use (&$seen) { $seen[] = 'later'; });
        shutdown::invoke_shutdown_callbacks();
        $this->assertSame(['first','weighted','default','last','later'], $seen);
    }
    public function testExplicitInvocationIsNotRepeatedAtPhpShutdown()
    {
        list($status,$out,$err) = $this->subprocess(
            '\flat\core\shutdown::register(function () { echo "once;"; });'
            .' \flat\core\shutdown::invoke_shutdown_callbacks(); echo "end;";'
        );
        $this->assertSame(0,$status,$err); $this->assertSame('',$err);
        $this->assertSame('once;end;',$out);
    }
    public function testCallbackRegisteredDuringInvocationRunsAtPhpShutdown()
    {
        list($status,$out,$err) = $this->subprocess(
            '\flat\core\shutdown::register(function () { echo "explicit;";'
            .' \flat\core\shutdown::register(function () { echo "deferred;"; }); });'
            .' \flat\core\shutdown::invoke_shutdown_callbacks(); echo "end;";'
        );
        $this->assertSame(0,$status,$err); $this->assertSame('',$err);
        $this->assertSame('explicit;end;deferred;',$out);
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
    public function testWeightCeilingAllowsExactlyTheRemainingSlots()
    {
        $registered = 0;
        try { for ($i=0; $i<2*shutdown::weight_ceiling; $i++) { shutdown::register(function () {},1); $registered++; } }
        catch (shutdown\exception\weight_ceiling $e) {}
        // Weight 1 starts at slot 51 (offset 50); slots run up to the ceiling, inclusive.
        $this->assertSame(shutdown::weight_ceiling - 51 + 1, $registered);
    }
}
