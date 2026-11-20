<?php
namespace flat\test\Unit\Core\Util;

use flat\core\util\deepcopy;
use flat\core\util\reserved;
use flat\core\util\time;
use flat\core\util\time_ago;
use flat\core\util\validate\assoc;
use PHPUnit\Framework\TestCase;

class CopyFixture { public $value; public function __construct($value) { $this->value = $value; } }

class CustomTimeUnits extends time_ago
{
    public static function withoutMatchingUnit()
    {
        $original = self::$unit_sec_floor;
        try {
            self::$unit_sec_floor = [60 => 'I'];
            return self::str(100, 101);
        } finally { self::$unit_sec_floor = $original; }
    }
}
class UtilityTest extends TestCase
{
    /** @dataProvider booleanOptions */
    public function testBooleanOptionsAreStrict($value, $truth, $falsehood)
    {
        $this->assertSame($truth, assoc::only_bool_true(['key' => $value], 'key', 'invalid'));
        $this->assertSame($falsehood, assoc::only_bool_false(['key' => $value], 'key', 'invalid'));
    }
    public function booleanOptions()
    {
        return [[true, true, 'invalid'], [false, 'invalid', false], [1, 'invalid', 'invalid'], [0, 'invalid', 'invalid'], ['true', 'invalid', 'invalid'], [null, 'invalid', 'invalid']];
    }
    public function testMissingOptionsAndScalarModes()
    {
        $this->assertNull(assoc::only_bool_true());
        $this->assertSame('fallback', assoc::only_bool_false([], 'absent', 'fallback'));
        $this->assertNull(assoc::only_bool_true(['x' => true], 'absent'));
        $this->assertSame(42, assoc::non_empty_scalar(['x' => 42], 'x'));
        $this->assertTrue(assoc::non_empty_scalar(['x' => 'value'], 'x', false));
        $this->assertNull(assoc::non_empty_scalar(['x' => []], 'x'));
        $this->assertFalse(assoc::non_empty_scalar(['x' => []], 'x', false));
        $this->assertSame('fallback', assoc::non_empty_scalar(['x' => 0], 'x', true, 'fallback'));
        $this->assertNull(assoc::non_empty_scalar());
        $this->assertNull(assoc::non_empty_scalar(['x' => new \stdClass], 'x'));
    }
    public function testCopyPreservesTruthyScalarsAndRecursesArrays()
    {
        $this->assertSame('text', deepcopy::data('text'));
        $this->assertSame(42, deepcopy::data(42));
        $this->assertSame(['a' => [1, 2]], deepcopy::arr(['a' => [1, 2]]));
        $this->assertSame(['a' => 1], deepcopy::data(['a' => 1]));
        foreach ([null, 0, false, '', '0', []] as $value) $this->assertSame($value, deepcopy::data($value));
        $this->assertNull(deepcopy::arr());
        $this->assertSame([], deepcopy::arr([]));
        $this->assertSame([0, false, []], deepcopy::arr([0, false, []]));
    }
    public function testDefaultObjectCopyIsShallowAndIndependent()
    {
        $nested = new CopyFixture('original');
        $source = new CopyFixture($nested);
        $copy = deepcopy::data($source);
        $this->assertInstanceOf(CopyFixture::class, $copy);
        $this->assertNotSame($source, $copy);
        $this->assertSame($nested, $copy->value);
        $copy->value = 'changed';
        $this->assertSame($nested, $source->value);
    }
    public function testObjectConversionsRecurseAndStdClassTakesPrecedence()
    {
        $source = new CopyFixture(new CopyFixture(7));
        $this->assertSame(['value' => ['value' => 7]], deepcopy::data($source, ['objects_to_assoc' => true]));
        foreach (['objects_to_stdClass', 'objects_to_object'] as $flag) {
            $copy = deepcopy::data($source, [$flag => true, 'objects_to_assoc' => true]);
            $this->assertEquals((object) ['value' => (object) ['value' => 7]], $copy);
            $this->assertNotSame($source->value, $copy->value);
        }
    }
    /** @dataProvider timecodes */
    public function testTimecodes($seconds, $text)
    {
        $this->assertSame($text, time::seconds_to_timecode($seconds));
        $this->assertEquals($seconds, time::timecode_to_seconds($text));
    }
    public function timecodes() { return [[0, '00:00:00'], [59, '00:00:59'], [60, '00:01:00'], [3661, '01:01:01'], [360001, '100:00:01']]; }
    public function testShortTimecodeAndNumericCoercion()
    {
        $this->assertSame(125, time::timecode_to_seconds('02:05'));
        $this->assertSame('00:01:00', time::seconds_to_timecode('60.9'));
    }
    /** @dataProvider relativeTimes */
    public function testRelativeTimeUnits($delta, $phrase)
    {
        $this->assertSame($phrase, time_ago::str(1000000000 - $delta, 1000000000));
    }
    public function relativeTimes()
    {
        return [[0, '0 seconds ago'], [1, '1 second ago'], [2, '2 seconds ago'], [60, '1 minute ago'], [120, '2 minutes ago'], [3600, '1 hour ago'], [7200, '2 hours ago'], [86400, '1 day ago'], [172800, '2 days ago'], [2592000, '1 month ago'], [5184000, '2 months ago'], [31104000, '1 year ago'], [62208000, '2 years ago'], [-120, '2 minutes from now']];
    }
    public function testRelativeTimeParsingDefaultsAndInvalidValues()
    {
        $this->assertSame('1 hour ago', time_ago::str('2001-09-09T01:46:40+00:00', '2001-09-09T02:46:40+00:00'));
        $this->assertNull(time_ago::str(null));
        $this->assertNull(time_ago::str('not a timestamp'));
        $this->assertRegExp('/^[0-9]+ years ago$/', time_ago::str(0));
        $this->assertRegExp('/^[0-9]+ years ago$/', time_ago::str(0, 'invalid date'));
        $object = new time_ago('invalid date', 123);
        $this->assertNull($object->time_ago());
        $this->assertSame('', (string) $object);
    }
    public function testReservedSegmentsAndCustomDelimiter()
    {
        $this->assertSame('App\\class_\\IF_\\User', reserved::make_safe_classname('App\\class\\IF\\User'));
        $this->assertSame('class_/new_/user', reserved::make_safe_classname('class/new/user', '/'));
        $this->assertSame('', reserved::make_safe_classname(''));
    }
    public function testCustomRelativeTimeTableMayHaveNoMatchingUnit()
    {
        $this->assertNull(CustomTimeUnits::withoutMatchingUnit());
        $this->assertSame('1 second ago', time_ago::str(100, 101));
    }
}
