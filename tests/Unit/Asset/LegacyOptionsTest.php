<?php
namespace flat\test\Unit\Asset;
use PHPUnit\Framework\TestCase;
class LegacyOptionsFixture
{
    use \flat\core\controller\asset\deprecated_methods_trait;
    public static function flag($flag, $params = null) { return self::_asset_param_to_flag($flag, $params); }
    public static function option($key, $params, $default = null) { return self::_asset_param_to_option($key, $params, $default); }
}
class LegacyOptionsTest extends TestCase
{
    public function testFlagsSupportListAndAssociativeForms()
    {
        $this->assertNull(LegacyOptionsFixture::flag([]));
        $this->assertFalse(LegacyOptionsFixture::flag('minify'));
        $this->assertFalse(LegacyOptionsFixture::flag('minify',['flag'=>['other']]));
        $this->assertTrue(LegacyOptionsFixture::flag('minify',['flag'=>['minify']]));
        $this->assertTrue(LegacyOptionsFixture::flag('minify',['flag'=>['minify'=>false]]));
        $this->assertNull(LegacyOptionsFixture::data_to_refs_on());
        $this->assertNull(LegacyOptionsFixture::data_to_refs_off());
    }
    public function testFlagsAreOnlyReadFromAnArrayOfFlags()
    {
        $this->assertFalse(LegacyOptionsFixture::flag('a',['flag'=>'a']));
        $this->assertFalse(LegacyOptionsFixture::flag('a',['flag'=>[]]));
        $this->assertFalse(LegacyOptionsFixture::flag('a',['other'=>['a']]));
        $this->assertFalse(LegacyOptionsFixture::flag('a','flag'));
        $this->assertFalse(LegacyOptionsFixture::flag('a',null));
    }
    public function testOptionsReturnPresentValuesOrFallback()
    {
        $this->assertSame('fallback',LegacyOptionsFixture::option([],[], 'fallback'));
        $this->assertSame('fallback',LegacyOptionsFixture::option('key',null,'fallback'));
        $this->assertSame('fallback',LegacyOptionsFixture::option('key',['option'=>['other'=>42]],'fallback'));
        $this->assertFalse(LegacyOptionsFixture::option('key',['option'=>['key'=>false]],'fallback'));
        $this->assertSame('fallback',LegacyOptionsFixture::option('key',['option'=>['key'=>null]],'fallback'));
    }
}
