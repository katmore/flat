<?php
namespace flat\test\Unit\Core\Template;
use PHPUnit\Framework\TestCase;
class ValidateControllerTest extends TestCase
{
    public function testRetiredControllerFailsClearlyOnceItsArgumentsAreValid()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('validate is not implemented');
        new \flat\core\controller\validate(new \flat\input\map(['present'=>'value']), new \flat\core\collection);
    }
    public function testRetiredInputMethodFailsClearly()
    {
        $controller = (new \ReflectionClass(\flat\core\controller\validate::class))->newInstanceWithoutConstructor();
        $this->expectException(\BadMethodCallException::class);
        $controller->set_input(new \flat\input\map);
    }
    /** @dataProvider invalidArguments */
    public function testConstructorRejectsInvalidInputAndCollection($input,$collection)
    {
        $this->expectException(\flat\core\controller\validate\exception\bad_param::class);
        new \flat\core\controller\validate($input,$collection);
    }
    public function invalidArguments()
    {
        return [[new \flat\input\map, new \stdClass], [new \stdClass, new \flat\core\collection]];
    }
}
