<?php
namespace flat\test\Unit\Core;

use flat\core\mappable\exception\cannot_get_property;
use flat\core\mappable\exception\cannot_set_property;
use PHPUnit\Framework\TestCase;

class PublicOnlyFixture { public $visible = 'value'; }
class MapperFixture
{
    use \flat\core\mapper;
    public $first = 'one';
    public $second = 'two';
    public $nil;
    protected $guarded = 'protected';
    private $secret = 'private';
    public function read($key, array $options = null) { return $this->get_property_value($key, $options); }
    public function write($key, $value, array $options = null) { return $this->set_property_value($key, $value, $options); }
    public function visit(callable $callback, array $list = null, array $options = null) { return $this->each_property($callback, $list, $options); }
}
class IgnoreReadMapper extends MapperFixture implements \flat\core\mappable\property\ignore_cannot_get {}
class IgnoreWriteMapper extends MapperFixture implements \flat\core\mappable\property\ignore_cannot_set {}
class IgnoreMissingMapper extends MapperFixture implements \flat\core\mappable\property\ignore_non_existing_set {}
class AddMapper extends MapperFixture implements \flat\core\mappable\property\add_if_not_exists {}

class MapperTest extends TestCase
{
    public function testReadsPropertiesByNameAndReflection()
    {
        $m = new MapperFixture;
        $this->assertSame('one', $m->read('first'));
        $this->assertNull($m->read('nil'));
        $this->assertSame('two', $m->read(new \ReflectionProperty($m, 'second')));
    }

    /** @dataProvider unreadableProperties */
    public function testReadErrors($key, $exception)
    {
        $this->expectException($exception);
        (new MapperFixture)->read($key);
    }

    public function unreadableProperties()
    {
        return [
            ['missing', cannot_get_property::class],
            ['secret', cannot_get_property::class],
            [12, \flat\core\mappable\exception\bad_property_name::class],
        ];
    }

    public function testIgnoredReadsReturnConfiguredSentinel()
    {
        $m = new MapperFixture;
        $this->assertInstanceOf(\flat\core\mappable\ignored::class, $m->read('missing', ['ignore_cannot_get' => true]));
        $marker = new \stdClass;
        $this->assertSame($marker, $m->read('secret', ['ignore_cannot_get' => true, 'value_if_ignored' => $marker]));
        $this->assertSame('fallback', $m->read('missing', ['ignore_cannot_get' => true, 'value_if_ignored' => 'fallback']));
        $this->assertInstanceOf(\flat\core\mappable\ignored::class, (new IgnoreReadMapper)->read('missing'));
    }

    public function testWritesDeclaredProtectedAndReflectionProperties()
    {
        $m = new MapperFixture;
        $this->assertTrue($m->write('first', 'changed'));
        $this->assertSame('changed', $m->first);
        $this->assertTrue($m->write('guarded', 'updated'));
        $this->assertTrue($m->write(new \ReflectionProperty($m, 'second'), 42));
        $this->assertSame(42, $m->second);
        $this->assertTrue($m->write('secret', 'updated', ['set_private_property' => true]));
        $p = new \ReflectionProperty($m, 'secret');
        $p->setAccessible(true);
        $this->assertSame('updated', $p->getValue($m));
    }

    /** @dataProvider unwritableProperties */
    public function testWriteErrors($key)
    {
        $this->expectException(cannot_set_property::class);
        (new MapperFixture)->write($key, 'value');
    }

    public function unwritableProperties() { return [[''], ['missing'], ['secret'], ['read']]; }

    public function testIgnoredWritesAndDynamicProperties()
    {
        $m = new MapperFixture;
        foreach (['missing', 'secret', 'read'] as $key) {
            $this->assertFalse($m->write($key, 1, ['ignore_cannot_set' => true]));
        }
        $this->assertFalse($m->write('missing', 1, ['ignore_non_existing_set' => true]));
        $this->assertFalse((new IgnoreWriteMapper)->write('missing', 1));
        $this->assertFalse((new IgnoreMissingMapper)->write('missing', 1));
        $this->assertTrue($m->write('added', false, ['add_if_not_exists' => true]));
        $this->assertFalse($m->added);
        $dynamic = new AddMapper;
        $this->assertTrue($dynamic->write('newProperty', 42));
        $this->assertSame(42, $dynamic->newProperty);
    }

    public function testExportsPublicPropertiesAndOmitsNullFromStdClass()
    {
        $m = new MapperFixture;
        $this->assertSame(['first' => 'one', 'second' => 'two', 'nil' => null], $m->get_as_assoc());
        $this->assertEquals((object) ['first' => 'one', 'second' => 'two'], $m->get_as_stdClass());
        $this->assertSame(['first' => 'one', 'second' => 'two', 'nil' => null], $m->get_as_assoc(new MapperFixture));
        // PHP 7.2 ReflectionClass does not enumerate these dynamic properties.
        $this->assertSame([], $m->get_as_assoc((object) ['x' => 1]));
        $this->assertEquals(new \stdClass, $m->get_as_stdClass(['x' => 1]));
        $this->assertSame([], $m->get_as_assoc(5));
    }

    public function testListsAndObjectsPopulateProperties()
    {
        $m = new MapperFixture;
        $m->list_to_properties(['first' => 0, 'second' => false]);
        $this->assertSame(0, $m->first);
        $this->assertFalse($m->second);
        $m->object_to_properties((object) ['first' => 'object', 'added' => 5], ['add_if_not_exists' => true]);
        $this->assertSame('object', $m->first);
        $this->assertSame(5, $m->added);
    }

    /** @dataProvider argumentForms */
    public function testArgumentMapping(array $args, array $names, array $expected)
    {
        $m = new MapperFixture;
        $m->args_to_properties($args, $names);
        foreach ($expected as $key => $value) $this->assertSame($value, $m->$key);
    }

    public function argumentForms()
    {
        return [
            [['a', 'b'], ['first', 'second'], ['first' => 'a', 'second' => 'b']],
            [[['first' => 'named']], ['first', 'second'], ['first' => 'named', 'second' => '']],
            [['first' => 'named'], ['first'], ['first' => 'named']],
            [[], ['first' => 'default', 'second' => 'fallback'], ['first' => 'default', 'second' => 'fallback']],
            [['x'], ['first' => 'default', 'second' => 'fallback'], ['first' => 'x', 'second' => 'fallback']],
            [['first' => 'named'], ['first' => 'default'], ['first' => 'named']],
        ];
    }

    public function testParamsSupportNamedPositionalAndEmptyLists()
    {
        $m = new MapperFixture;
        $m->params_to_properties(['second' => 2]);
        $this->assertSame(2, $m->second);
        $m->params_to_properties([10, 20]);
        $this->assertSame(10, $m->first);
        $this->assertSame(20, $m->second);
        $m->params_to_properties([]);
        $this->assertSame(10, $m->first);
    }

    public function testVisitReadsAndUpdatesSelectedPublicProperties()
    {
        $m = new MapperFixture;
        $seen = [];
        $m->visit(function ($value, $name) use (&$seen) { $seen[$name] = $value; });
        $this->assertSame(['first' => 'one', 'second' => 'two', 'nil' => null], $seen);
        $m->visit(function ($value) { return strtoupper($value); }, ['first']);
        $this->assertSame('ONE', $m->first);
        $this->assertSame('two', $m->second);
        $m->visit(function () { return new \flat\core\mappable\property\nulled; }, ['first']);
        $this->assertNull($m->first);
    }

    /**
     * Reading non-public properties was never supported (it crashed on a
     * misspelled reflection call); the retired options now say so clearly.
     * @dataProvider privateAccessFailures
     */
    public function testRetiredPrivateReadOptionsRaiseAClearMappingError($operation, $property)
    {
        $m = new MapperFixture;
        $this->expectException(\flat\core\mappable\exception\cannot_get_property::class);
        $this->expectExceptionMessage("mapper cannot get property: $property reason:");
        if ($operation === 'read') $m->read('secret', ['get_private_property' => true]);
        elseif ($operation === 'assoc') $m->get_as_assoc(null, true);
        else $m->get_as_stdClass(null, true);
    }

    public function testPrivateReadFlagIsHarmlessForPublicOnlyObjects()
    {
        $source = new PublicOnlyFixture;
        $m = new MapperFixture;
        $this->assertSame(['visible' => 'value'], $m->get_as_assoc($source, true));
        $this->assertEquals((object) ['visible' => 'value'], $m->get_as_stdClass($source, true));
    }

    public function privateAccessFailures() { return [['read', 'secret'], ['assoc', 'guarded'], ['stdClass', 'guarded']]; }

    public function testProtectedVisitPreservesReflectionFailure()
    {
        $this->expectException(\ReflectionException::class);
        (new MapperFixture)->visit(function () { return 'changed'; }, ['guarded']);
    }

    public function testPrivateVisitIsIgnoredEvenWithObsoleteFlags()
    {
        $calls = 0;
        (new MapperFixture)->visit(function () use (&$calls) { ++$calls; }, ['secret'], ['get_private_property' => true]);
        $this->assertSame(0, $calls);
    }

    public function testMissingVisitThrowsCannotGetProperty()
    {
        // The legacy exception omits the requested name; preserve that message.
        $this->expectException(cannot_get_property::class);
        $this->expectExceptionMessage('mapper cannot get property:  reason: does not exist');
        (new MapperFixture)->visit(function () {}, ['missing']);
    }

}
