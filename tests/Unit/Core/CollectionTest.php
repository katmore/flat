<?php
namespace flat\test\Unit\Core;
use flat\core\collection;
use flat\core\collection\member;
use flat\core\collection\member\ignore;
use PHPUnit\Framework\TestCase;
class AlternateCopyCollection extends collection {}
abstract class AbstractCopyCollection extends collection {}
class CollectionTest extends TestCase
{
    public function testEmptyAndPopulatedIteration()
    {
        $col = new collection;
        $this->assertSame(0, $col->count()); $this->assertFalse($col->valid());
        $this->assertNull($col->current()); $this->assertNull($col->key());
        $this->assertNull($col->next()); $col->rewind();
        $this->assertTrue($col->add('first', 'a')); $this->assertTrue($col->add('second', 'b'));
        $this->assertSame(2, $col->count());
        $col->rewind(); $this->assertSame('first', $col->current());
        $col->next(); $this->assertSame('second', $col->current());
        $col->next(); $this->assertFalse($col->valid());
        $col->rewind(); $this->assertTrue($col->valid());
        $this->assertSame('first', $col->get('a')->get_data());
        $this->assertTrue($col->is_exist('b')); $this->assertFalse($col->is_exist('missing'));
        $this->assertNull($col->get('missing'));
    }
    public function testUnindexedMembersAndIgnoredMembers()
    {
        $col = new collection; $col->add(42);
        $this->assertSame(0, $col->key());
        $this->assertNull($col->add(new ignore));
        $this->assertSame(1, $col->count());
    }
    public function testReplacingMembersCopiesWrappersAndHonorsMatchingIndexLength()
    {
        $col = new collection; $col->add('old');
        $original = new member('new');
        $col->set_member([$original], ['newKey' => 0]);
        $this->assertSame(1, $col->count());
        $this->assertSame('new', $col->get('newKey')->get_data());
        $this->assertNotSame($original, $col->get('newKey'));
        $col->set_member([new member('ignored')], ['a' => 0, 'b' => 1]);
        $this->assertSame('new', $col->get('newKey')->get_data());
        $col->set_member([new member('unindexed')]);
        $this->assertSame(0, $col->key()); $this->assertSame('unindexed', $col->current());
    }
    public function testRangeLimitNormalizationAndCallbacks()
    {
        $col = new collection;
        foreach (['a', 'b', 'c'] as $key) $col->add($key, $key);
        $this->assertCount(3, $col->range());
        $this->assertCount(3, $col->range(100, -1));
        $this->assertCount(2, $col->range('2'));
        $calls = [];
        $this->assertNull($col->range(2, 0, function ($member, $position, $key) use (&$calls) {
            $calls[] = [$member->get_data(), $position, $key];
        }));
        $this->assertSame([['a', 0, 'a'], ['b', 1, 'b']], $calls);
    }
    public function testEmptyRangeRaisesOutOfRange()
    {
        $this->expectException(\flat\core\collection\exception\out_of_range::class);
        $this->expectExceptionMessage('position:0 is out of range');
        (new collection)->range();
    }
    public function testCallbacksRunOncePerAddedMember()
    {
        $col = new collection; $validated = [];
        $col->set_data_validator(function ($data) use (&$validated) { $validated[] = $data; });
        $col->set_data_filter(function ($data) { return $data + 1; });
        $col->add(1, 'key');
        $this->assertSame([1], $validated);
        $this->assertSame(2, $col->get('key')->get_data());
    }
    public function testFilterCanIgnoreAMemberAndRunsOnce()
    {
        $col = new collection; $calls = 0;
        $col->set_data_filter(function () use (&$calls) { $calls++; return new ignore; });
        $this->assertNull($col->add('ignored')); $this->assertSame(0, $col->count());
        $this->assertSame(1, $calls);
    }
    public function testValidatorExceptionsPropagate()
    {
        $col = new collection;
        $col->set_data_validator(function () { throw new \DomainException('invalid'); });
        $this->expectException(\DomainException::class);
        $col->add('data');
    }
    public function testCopiesAreIndependentAndPreserveIndexZero()
    {
        $original = new collection; $original->add(false, 'first'); $original->add(0, 'second');
        $copy = $original->get_copy();
        $this->assertNotSame($original, $copy);
        $this->assertSame(2, $copy->count());
        $this->assertFalse($copy->get('first')->get_data());
        $this->assertSame(0, $copy->get('second')->get_data());
        $this->assertNotSame($original->get('first'), $copy->get('first'));
        $copy->add('third'); $this->assertSame(2, $original->count());
        $this->assertSame(0, (new collection)->get_copy()->count());
    }
    public function testCopyDestinationFormsAndFallbacks()
    {
        $source = new AlternateCopyCollection; $source->add('value', 'key');
        $this->assertInstanceOf(AlternateCopyCollection::class, $source->get_copy());
        foreach ([collection::class, new collection] as $destination) {
            $copy = collection::load_copy($source, $destination);
            $this->assertSame(collection::class, get_class($copy));
            $this->assertSame('value', $copy->get('key')->get_data());
            $this->assertNotSame($destination, $copy);
        }
        foreach (['MissingCollectionClass', \stdClass::class, AbstractCopyCollection::class] as $destination) {
            $this->assertInstanceOf(AlternateCopyCollection::class, collection::load_copy($source, $destination));
        }
    }
    public function testFreshCollectionAcceptsMemberReplacement()
    {
        $collection = new collection;
        $collection->set_member([new member('zero')], ['first' => 0]);
        $this->assertSame('zero', $collection->get('first')->get_data());
        $collection->set_member([]); $this->assertSame(0, $collection->count());
        $this->assertFalse($collection->valid());
    }

    public function testIterationKeysMixRegisteredNamesAndNumericPositions()
    {
        $col = new collection;
        $col->add('first', 'named'); $col->add('second'); $col->add('third', 'last');
        $this->assertSame(['named' => 'first', 1 => 'second', 'last' => 'third'], iterator_to_array($col));
        $this->assertNull($col->key());
        $col->rewind(); $this->assertSame('named', $col->key());
        $col->next(); $this->assertSame(1, $col->key());
    }
    public function testRegisteredNumericZeroDoesNotBecomeTheCurrentPosition()
    {
        $col = new collection;
        $col->set_member([new member('first'), new member('second')], ['named' => 0, 0 => 1]);
        $this->assertSame('named', $col->key()); $col->next();
        $this->assertSame(0, $col->key());
    }
    public function testRangeLimitIsACountAndClipsAtTheEnd()
    {
        $col = new collection;
        foreach (['a', 'b', 'c', 'd'] as $value) $col->add($value);
        $values = function (array $members) { return array_map(function ($m) { return $m->get_data(); }, $members); };
        $this->assertSame(['b', 'c'], $values($col->range(2, 1)));
        $this->assertSame(['d'], $values($col->range(2, 3)));
        $this->assertSame(['c', 'd'], $values($col->range(0, 2)));
        $this->assertSame(['a', 'b'], $values($col->range(2, -1)));
        $this->assertSame(['b', 'c', 'd'], $values($col->range(PHP_INT_MAX, 1)));
        $calls = [];
        $col->range(2, 1, function ($member, $position, $key) use (&$calls) { $calls[] = [$member->get_data(), $position, $key]; });
        $this->assertSame([['b', 1, 1], ['c', 2, 2]], $calls);
        $this->assertSame(0, $col->key()); // A range does not advance the iterator.
    }
    public function testRangeRejectsStartAtTheMemberCount()
    {
        $col = new collection; $col->add('value');
        $this->expectException(\flat\core\collection\exception\out_of_range::class);
        $this->expectExceptionMessage('position:1 is out of range');
        $col->range(1, 1);
    }

}
