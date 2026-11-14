<?php
namespace flat\test\Unit\Core\Util;
require_once __DIR__.'/../../../Support/PhpSubprocess.php';
use PHPUnit\Framework\TestCase;
use flat\core\util\validate;
use flat\core\util\validate\match\regexp;
class AlwaysPass extends validate { protected static function _validate($value,array $param=null) { return true; } }
class EchoValidation extends validate { protected static function _validate($value,array $param=null) { return $value; } }
class DefaultValidation extends validate {}
class OwnExceptionValidation extends validate implements \flat\core\util\validate\exceptionable
{
    protected static function _validate($value,array $param=null) { return false; }
    public static function throw_exception(array $param=null) { throw new \DomainException('own: '.$param['value']); }
}
class DefinedRegex extends regexp implements regexp\definer { public function get_regexp() { return '/^a$/'; } }
class RegexProbe extends regexp { public static function check($pattern) { return self::_check_regexp($pattern); } }
class RegexConstructorProbe extends regexp
{
    protected static function _validate($value,array $param=null) { return true; }
}
class ValidationTest extends TestCase
{
    use \flat\test\Support\PhpSubprocess;
    private $suppressed;
    protected function setUp(): void { $this->suppressed=\flat\core\debug::is_suppress_active(); \flat\core\debug::set_suppress_on(); }
    protected function tearDown(): void { if ($this->suppressed) \flat\core\debug::set_suppress_on(); else \flat\core\debug::set_suppress_off(); }
    public function testOnlyStrictTruePassesAndStoredStringsAreAvailable()
    {
        $this->assertTrue(EchoValidation::is_valid(true));
        $this->assertFalse(EchoValidation::is_valid(1)); $this->assertFalse(EchoValidation::is_valid('yes'));
        $this->assertFalse(DefaultValidation::is_valid('value'));
        $this->assertSame('value',AlwaysPass::validate('value',[]));
        $stored = new AlwaysPass(['value'=>'stored','validate'=>[]]);
        $this->assertSame('stored',$stored->get_value()); $this->assertSame('stored',(string)$stored);
        $this->assertSame('',(string)new AlwaysPass);
        $this->assertSame('',(string)new AlwaysPass(['value'=>['array'],'validate'=>'ignored']));
        $this->assertNull(EchoValidation::validate(false,[]));
    }
    public function testValidationOptions()
    {
        $this->assertSame('replacement',AlwaysPass::validate('original',['value_on_pass'=>'replacement']));
        $this->assertSame('original',AlwaysPass::validate('original'));
        $this->assertSame('fallback',EchoValidation::validate(false,['value_on_fail'=>'fallback']));
        $this->assertNull(EchoValidation::validate(false));
        // fail_handler wins over the other failure options.
        $this->assertSame('handled: 0',EchoValidation::validate(0,['fail_handler'=>function ($v) { return 'handled: '.$v; },'exception_on_fail'=>true,'value_on_fail'=>'fallback']));
        try { EchoValidation::validate('bad',['exception_on_fail'=>true,'value_on_fail'=>'fallback']); $this->fail('expected a failure'); }
        catch (\flat\core\util\validate\exception\fail $e) { $this->assertStringContainsString("value 'bad' did not pass validation rule ".EchoValidation::class,$e->getMessage()); }
        $this->expectException(\DomainException::class); $this->expectExceptionMessage('own: x');
        OwnExceptionValidation::validate('x',['exception_on_fail'=>true]);
    }
    public function testExplicitRegexConstructorKeepsItsExistingArrayPayload()
    {
        $rule = new RegexConstructorProbe(['regexp'=>'/a/']);
        $this->assertSame(['regexp'=>'/a/'],$rule->get_value());
    }
    public function testFactoryInterfaceFailureLoadsThroughBothNames()
    {
        $error = new validate\factory\exception\not_match_interface(new \stdClass);
        $this->assertSame("match rule 'stdClass' in factory is not a match_interface",$error->getMessage());
        $this->assertInstanceOf(\flat\core\util\factory\exception\not_match_interface::class,$error);
        $this->assertInstanceOf(validate\factory\exception::class,$error);
    }
    public function testHistoricalFactoryExceptionAutoloadsInAFreshProcess()
    {
        list($status,$out,$err)=$this->subprocess('echo (new \flat\core\util\factory\exception\not_match_interface(new \stdClass))->getMessage();');
        $this->assertSame(0,$status,$err); $this->assertSame('',$err);
        $this->assertSame("match rule 'stdClass' in factory is not a match_interface",$out);
    }
    public function testRegexMatchingAndMultiplePatterns()
    {
        $this->assertTrue(regexp::is_valid('alpha',['regexp'=>'/^a/']));
        $this->assertTrue(regexp::is_valid('alpha',['regexp'=>['/^a/','/a$/']]));
        $this->assertFalse(regexp::is_valid('beta',['regexp'=>'/^a/']));
        $this->assertNull((new regexp)->get_value()); $this->assertNull((new DefinedRegex)->get_value());
        $this->assertNull(RegexProbe::check('/a/'));
    }
    /** @dataProvider invalidPatternParameters */
    public function testRegexConfigurationFailures($options,$exception)
    {
        $this->expectException($exception); regexp::is_valid('value',$options);
    }
    public function invalidPatternParameters()
    {
        return [[[],regexp\exception\bad_param::class], [['regexp'=>new \stdClass],regexp\exception\bad_param::class],
            [['regexp'=>['']],regexp\exception\invalid_regex\cannot_be_empty::class],
            [['regexp'=>[42]],\ArgumentCountError::class]];
    }
    public function testInvalidRegexSyntaxProducesItsDescriptiveException()
    {
        $warnings = [];
        set_error_handler(function ($level,$message) use (&$warnings) { if (strpos($message,'preg_match(): Compilation failed:') === 0) { $warnings[]=$message; return true; } return false; });
        try {
            try { regexp::is_valid('value',['regexp'=>'/[invalid/']); }
            catch (regexp\exception\invalid_regex\bad_pattern $e) {
                $this->assertStringContainsString('/[invalid/',$e->getMessage());
                $this->assertStringContainsString('PREG_INTERNAL_ERROR',$e->getMessage());
                $this->assertCount(1,$warnings); return;
            }
            $this->fail('Invalid regex syntax must fail');
        } finally { restore_error_handler(); }
    }
    public function testPcreExecutionErrorRaisesPregError()
    {
        try { regexp::is_valid("\xB1",['regexp'=>'/./u']); $this->fail('expected preg_error'); }
        catch (regexp\exception\preg_error $e) {
            $this->assertSame(PREG_BAD_UTF8_ERROR,preg_last_error());
            $this->assertStringContainsString('PREG_BAD_UTF8_ERROR',$e->getMessage());
        }
        $this->assertFalse(regexp::is_valid('b',['regexp'=>'/^a$/']));
    }
    /** @dataProvider nativePcreFailures */
    public function testPcreExceptionExplainsNativeEngineErrors($kind,$expected,$fragment)
    {
        $jit = ini_get('pcre.jit'); $backtrack = ini_get('pcre.backtrack_limit'); $recursion = ini_get('pcre.recursion_limit');
        try {
            ini_set('pcre.jit','0');
            if ($kind === 'compile') { @preg_match('/[/', ''); }
            elseif ($kind === 'backtrack') { ini_set('pcre.backtrack_limit','1'); preg_match('/^(a+)+$/',str_repeat('a',32).'b'); }
            elseif ($kind === 'recursion') { ini_set('pcre.recursion_limit','1'); preg_match('/(a|b)+/','ab'); }
            elseif ($kind === 'offset') { preg_match('/./u','é',$matches,0,1); }
            else { preg_match('/valid/','valid'); }
            $this->assertSame($expected,preg_last_error());
            $error = new regexp\exception\preg_error;
            $this->assertStringContainsString($fragment,$error->getMessage());
        } finally {
            ini_set('pcre.jit',$jit); ini_set('pcre.backtrack_limit',$backtrack); ini_set('pcre.recursion_limit',$recursion);
        }
    }
    public function nativePcreFailures()
    {
        return [['compile',PREG_INTERNAL_ERROR,'internal PCRE error'], ['backtrack',PREG_BACKTRACK_LIMIT_ERROR,'backtract limit exhausted'],
            ['recursion',PREG_RECURSION_LIMIT_ERROR,'recursion limit exhausted'], ['offset',PREG_BAD_UTF8_OFFSET_ERROR,'offset did not correspond'],
            ['none',PREG_NO_ERROR,'unknown error']];
    }
    public function testInvalidPatternExceptionIncludesPatternAndNativeCodeName()
    {
        $error = new regexp\exception\invalid_regex\preg_error(PREG_BAD_UTF8_ERROR,'/./u');
        $this->assertStringContainsString('PREG_BAD_UTF8_ERROR',$error->getMessage());
        $this->assertStringContainsString('/./u',$error->getMessage());
        $this->assertNull(regexp\exception\invalid_regex\preg_error::preg_errtxt(-1));
    }
    /** @dataProvider hostnames */
    public function testHostnameDefinerSuppliesAValidPatternWithoutChangingItsRawConstant($hostname,$expected)
    {
        $rule = new validate\hostname;
        $this->assertSame('/'.validate\hostname::hostname_regex.'/',$rule->get_regexp());
        $this->assertSame($expected,regexp::is_valid($hostname,['regexp'=>$rule->get_regexp()]));
    }
    public function hostnames()
    {
        return [['example.com',true],['sub-1.example',true],['localhost',true],['192.0.2.1',true],
            ['-bad.example',false],['a_b.example',false],['example.com:80',false],['',false]];
    }
    public function testSupportedFilterIdsConstructSuccessfully()
    {
        foreach ([FILTER_VALIDATE_EMAIL,FILTER_VALIDATE_IP,FILTER_VALIDATE_REGEXP,FILTER_VALIDATE_URL] as $filter) {
            $this->assertInstanceOf(validate\match\filtervar::class,new validate\match\filtervar($filter));
        }
    }
    public function testInvalidFilterUsesItsConcreteException()
    {
        $this->expectException(validate\match\filtervar\exception\invalid_filter::class);
        new validate\match\filtervar(-1);
    }
}
