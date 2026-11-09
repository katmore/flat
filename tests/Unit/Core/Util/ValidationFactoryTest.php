<?php
namespace flat\test\Unit\Core\Util;
use PHPUnit\Framework\TestCase;
class FactoryValidationRule extends \flat\core\util\validate {}
class ValidationFactoryFixture extends \flat\core\util\validate\factory
{
    public function push($rule,$data = null) { return $this->add_validate($rule,$data); }
}
class PairConstraintFactory extends ValidationFactoryFixture
{
    public function get_member_class_constraint() { return \flat\core\util\validate\factory\data::class; }
}
class ValidationFactoryTest extends TestCase
{
    /** @group known-defect */
    public function testDefaultConstraintCurrentlyRejectsThePairBuiltByItsHelper()
    {
        $factory = new ValidationFactoryFixture;
        $this->assertSame('\\flat\\core\\factory',$factory->get_member_class_constraint());
        $this->expectException(\flat\core\collection\member\constrainer\exception\failed::class);
        $factory->push(new FactoryValidationRule);
    }
    public function testSubclassConstraintAllowsPairsAndPreservesTheirInputs()
    {
        $factory = new PairConstraintFactory; $rule = new FactoryValidationRule;
        $this->assertNull($factory->push($rule,['context'=>'value']));
        $this->assertSame(1,$factory->count());
        $pair = $factory->range()[0]->get_data();
        $this->assertInstanceOf(\flat\core\util\validate\factory\data::class,$pair);
        $this->assertSame($rule,$pair->validate);
        $this->assertSame(['context'=>'value'],$pair->data);
    }
}
