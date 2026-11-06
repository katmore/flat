<?php
namespace flat\test\Unit\Lib;
use PHPUnit\Framework\TestCase;
use flat\lib;
class LibTest extends TestCase
{
    public function testDependencyMessagesIncludeOptionalMissingClassDetails()
    {
        foreach (['MissingOne', ['MissingOne','MissingTwo']] as $missing) {
            $error = new \flat\lib\exception\missing_dependency('optional library',$missing);
            $expected = is_array($missing) ? 'MissingOne, MissingTwo' : 'MissingOne';
            $this->assertStringContainsString("missing class '$expected'",$error->getMessage());
            $this->assertSame($missing,$error->get_missing_class());
        }
        $this->assertSame("missing dependency: 'optional library'",(new \flat\lib\exception\missing_dependency('optional library'))->getMessage());
    }
    public function testRequiredValuesAndLists()
    {
        $this->assertNull(lib::require_scalar_param(['a'=>'value','b'=>true], ['a','b']));
        $this->assertNull(lib::require_nonempty_param(['a'=>['value'],'b'=>'value'], ['a','b']));
        $this->assertNull(lib::require_array_param(['a'=>[]], 'a'));
        $this->assertNull(lib::require_nonempty_param(['a'=>'value'], 'a'));
    }
    /** @dataProvider invalidValues */
    public function testInvalidRequiredValues($method,$parameters,$key,$exception)
    {
        $this->expectException($exception); lib::$method($parameters,$key);
    }
    public function invalidValues()
    {
        $bad = \flat\lib\exception\bad_param::class; $missing = \flat\lib\exception\missing_required_param::class;
        return [['require_scalar_param',null,'a',$bad], ['require_scalar_param',['a'=>0],'a',$missing],
            ['require_scalar_param',['a'=>[]],'a',$missing], ['require_scalar_param',['a'=>new \stdClass],'a',$bad],
            ['require_nonempty_param',['a'=>false],'a',$missing], ['require_array_param',['b'=>1],'a',$missing],
            ['require_array_param',['a'=>'text'],'a',$bad]];
    }
    public function testPresentClassDependenciesPass()
    {
        $this->assertNull(lib::enforce_class_dependency(\stdClass::class,'native class'));
        $this->assertNull(lib::enforce_class_dependency([\stdClass::class, self::class],'classes'));
    }
    /** @dataProvider missingClasses */
    public function testMissingClassDependenciesRetainTheNormalizedClassSet($classes,$expected)
    {
        try { lib::enforce_class_dependency($classes,'dependency'); }
        catch (\flat\lib\exception\missing_dependency $e) {
            $this->assertSame('dependency',$e->get_dependency());
            $this->assertSame(['flat_missing_dependency'],$e->get_missing_class());
            $this->assertSame($expected,$e->get_class_set()); return;
        }
        $this->fail('Missing dependency must fail');
    }
    public function missingClasses() { return [['flat_missing_dependency',['flat_missing_dependency']], [[\stdClass::class,'flat_missing_dependency'],[\stdClass::class,'flat_missing_dependency']]]; }
    public function testBadParameterMetadata()
    {
        $exception = new \flat\lib\exception\bad_param('name','reason');
        $this->assertSame('name',$exception->get_param_key());
        $this->assertSame('reason',$exception->get_reason());
        $this->assertSame(sprintf('%u',crc32('reason')),$exception->get_reason_code());
    }
}
