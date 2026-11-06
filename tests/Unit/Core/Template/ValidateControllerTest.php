<?php
namespace flat\test\Unit\Core\Template;
use PHPUnit\Framework\TestCase;
class ValidateControllerTest extends TestCase
{
    public function testControllerUnwrapsMembersBeforeInspectingInputFields()
    {
        $collection = new \flat\core\collection;
        $collection->add((object)['data'=>'present']); $collection->add((object)['data'=>'missing']);
        $input = new \flat\input\map(['present'=>'value']);
        $controller = new \flat\core\controller\validate($input,$collection);
        $this->assertNull($controller->set_input($input));
        $this->assertSame('value',$input->present);
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
