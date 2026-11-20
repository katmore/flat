<?php
namespace flat\test\Unit\Core\Util;
use flat\core\util\listcheck\factory;
use PHPUnit\Framework\TestCase;
class ListFixture extends factory
{
    public function configure(array $values) { $this->set_list($values); }
    public function register(callable $handler) { $this->set_fail_handler($handler); }
}
class ListcheckTest extends TestCase
{
    public function testLastSuppliedValueDefinesTheList()
    {
        $m = new ListFixture;
        $m->configure([['ignored'], ['red', 'blue']]);
        $this->assertSame(['red', 'blue'], $m->get_list());
        $this->assertTrue($m->is_in_list('red'));
        $this->assertFalse($m->is_in_list('ignored'));
        $m->configure([]);
        $this->assertSame(['red', 'blue'], $m->get_list());
        $m->configure([['green']]);
        $this->assertSame(['green'], $m->get_list());
    }
    public function testMembershipUsesLooseComparison()
    {
        $m = new ListFixture;
        $m->configure([[1, 'a']]);
        $this->assertTrue($m->is_in_list('1'));
        $this->assertFalse($m->is_in_list('b'));
    }
    public function testFailureInvokesRegisteredCallbackWithFactoryAndValue()
    {
        $m = new ListFixture; $m->configure([['allowed']]);
        $calls = [];
        $m->register(function ($factory, $value) use (&$calls) { $calls[] = [$factory, $value]; });
        $this->assertTrue($m->is_in_list('allowed'));
        $this->assertSame([], $calls);
        $this->assertFalse($m->is_in_list('rejected'));
        $this->assertSame([[$m, 'rejected']], $calls);
    }
    public function testFailureCallbackExceptionPropagates()
    {
        $m = new ListFixture; $m->configure([['allowed']]);
        $m->register(function () { throw new \DomainException('rejected'); });
        $this->expectException(\DomainException::class);
        $m->is_in_list('bad');
    }
}
