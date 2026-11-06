<?php
namespace flat\test\Unit\Core\Collection;
use flat\core\collection;
use PHPUnit\Framework\TestCase;
interface MemberTag {}
class BaseMember {}
class AllowedMember extends BaseMember implements MemberTag, \Countable { public function count() { return 1; } }
class ClassCollection extends collection implements \flat\core\collection\member\constrainer\class_constraint
{
    private $constraint;
    public function __construct($constraint = BaseMember::class) { $this->constraint = $constraint; }
    public function get_member_class_constraint() { return $this->constraint; }
}
class InterfaceCollection extends collection implements \flat\core\collection\member\constrainer\interface_constraint
{
    private $constraint;
    public function __construct($constraint = MemberTag::class) { $this->constraint = $constraint; }
    public function get_member_interface_constraint() { return $this->constraint; }
}
class ValidatedCollection extends collection implements \flat\core\collection\member\validator
{
    public $result = true; public $filtered; public $success = [];
    public function member_validate($data) { return $this->result; }
    public function member_filter($data) { return $this->filtered; }
    public function member_success($data) { $this->success[] = $data; }
}
class ConstraintTest extends TestCase
{
    public function testClassAndInterfaceConstraintsAcceptMatchingMembers()
    {
        foreach ([new ClassCollection, new ClassCollection([BaseMember::class, AllowedMember::class]), new InterfaceCollection,
            new InterfaceCollection([MemberTag::class, \Countable::class])] as $collection) {
            $value = new AllowedMember;
            $this->assertTrue($collection->add($value, 'allowed'));
            $this->assertSame($value, $collection->get('allowed')->get_data());
        }
    }
    /** @dataProvider rejectingCollections */
    public function testConstraintsRejectWrongMember($collection)
    {
        $this->expectException(\flat\core\collection\member\constrainer\exception\failed::class);
        $collection->add(new \stdClass);
    }
    public function rejectingCollections() { return [[new ClassCollection], [new InterfaceCollection]]; }
    /** @dataProvider badConstraintDefinitions */
    public function testMalformedConstraintsAreRejected($kind, $constraint)
    {
        $collection = $kind === 'class' ? new ClassCollection($constraint) : new InterfaceCollection($constraint);
        $this->expectException(\flat\core\collection\member\constrainer\exception\bad_constrainer::class);
        $collection->add(new AllowedMember);
    }
    public function badConstraintDefinitions()
    {
        return [['class', 'MissingClassForFlatTests'], ['class', 12], ['class', [[BaseMember::class, AllowedMember::class]]],
            ['interface', 'MissingInterfaceForFlatTests'], ['interface', 12], ['interface', [[MemberTag::class, \Countable::class]]]];
    }
    public function testInactiveControllersHaveNoChecks()
    {
        $collection = new collection;
        $constraint = new \flat\core\collection\member\constrainer\controller($collection);
        $this->assertFalse($constraint->has_active_checks());
        $value = new \stdClass; $this->assertSame($value, $constraint->check_member($value));
        $validator = new \flat\core\collection\member\validator\controller($collection);
        $this->assertFalse($validator->has_active_checks()); $this->assertNull($validator->check_member($value));
    }
    /** @dataProvider acceptedValidationResults */
    public function testValidationPassesAndSuccessReceivesFilteredValue($result)
    {
        $collection = new ValidatedCollection; $collection->result = $result;
        $collection->filtered = 'filtered';
        $this->assertTrue($collection->add('input', 'key'));
        $this->assertSame('filtered', $collection->get('key')->get_data());
        $this->assertSame(['filtered'], $collection->success);
    }
    public function acceptedValidationResults() { return [[true], [null], [0], ['']]; }
    public function testNullFilterLeavesValueUnchanged()
    {
        $collection = new ValidatedCollection;
        $this->assertTrue($collection->add('input', 'key'));
        $this->assertSame('input', $collection->get('key')->get_data());
    }
    /** @dataProvider rejectedValidationResults */
    public function testValidationRejectsFailureResults($result)
    {
        $collection = new ValidatedCollection; $collection->result = $result;
        $this->expectException(\flat\core\collection\member\validator\exception\failed::class);
        $collection->add('input');
    }
    public function rejectedValidationResults() { return [[false], ['reason'], [12]]; }
    public function testValidationExceptionIsPropagatedUnchanged()
    {
        $collection = new ValidatedCollection; $error = new \DomainException('invalid'); $collection->result = $error;
        try { $collection->add('input'); $this->fail('Expected validation exception'); }
        catch (\DomainException $actual) { $this->assertSame($error, $actual); }
        $this->assertSame([], $collection->success);
    }
    public function testValidationAndFilterMayIgnoreMembers()
    {
        $collection = new ValidatedCollection; $collection->result = new \flat\core\collection\member\ignore;
        $this->assertNull($collection->add('ignored')); $this->assertSame(0, $collection->count());
        $collection->result = true; $collection->filtered = new \flat\core\collection\member\ignore;
        $this->assertNull($collection->add('ignored')); $this->assertSame([], $collection->success);
    }
}
