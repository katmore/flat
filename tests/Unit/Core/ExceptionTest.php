<?php
namespace flat\test\Unit\Core;
use PHPUnit\Framework\TestCase;
class ExceptionFixture extends \flat\core\exception
{
    public function metadata(array $data) { $this->_set_exception_data($data); }
}
class LegacyExceptionFixture extends \flat\exception {}
function feature_not_ready_fixture() { return new \flat\core\status\exception\feature_not_ready; }
class ExceptionTest extends TestCase
{
    public function testLegacyExceptionBaseKeepsExplicitCodesAndDerivesMissingCodes()
    {
        $this->assertSame(47,(new LegacyExceptionFixture('explicit',47))->getCode());
        foreach ([0,null,'47'] as $code) {
            $exception = new LegacyExceptionFixture('derived',$code);
            $this->assertSame('derived',$exception->getMessage());
            $this->assertIsInt($exception->getCode());
            $this->assertGreaterThan(0,$exception->getCode());
        }
    }
    public function testExplicitAndDerivedCodesAndMetadata()
    {
        $explicit = new ExceptionFixture('message',42);
        $this->assertSame(42,$explicit->getCode()); $this->assertSame('message',$explicit->getMessage());
        $this->assertIsInt((new ExceptionFixture('derived','invalid'))->getCode());
        $explicit->metadata(['key'=>['nested'=>false]]);
        $this->assertEquals((object) ['key'=>['nested'=>false]],$explicit->get_exception_data());
        $explicit->metadata([1,2]);
        $this->assertEquals((object) ['data'=>[1,2]],$explicit->get_exception_data());
    }
    /** @dataProvider unavailableFeatures */
    public function testUnavailableFeaturesExplainTheirContext($param,$message)
    {
        $e = new \flat\core\status\exception\feature_not_ready($param);
        $this->assertStringContainsString($message,$e->getMessage());
    }
    public function unavailableFeatures()
    {
        return [[['msg'=>'explicit message'],'explicit message'], [['object'=>new \stdClass],'stdClass not ready'],
            [['object'=>\stdClass::class],'class stdClass not ready'], [['method'=>'example'],'method \'example\' not ready'],
            [['function'=>'example'],'function \'example\' not ready'], [[],self::class.'::testUnavailableFeaturesExplainTheirContext() not ready']];
    }
    public function testUnavailableFunctionContext()
    {
        $this->assertStringContainsString('feature_not_ready_fixture()',feature_not_ready_fixture()->getMessage());
    }
}
