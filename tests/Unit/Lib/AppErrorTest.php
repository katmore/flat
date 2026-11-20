<?php
namespace flat\test\Unit\Lib;
use PHPUnit\Framework\TestCase;
use flat\lib\exception\app_error;
class StringKey { public function __toString() { return 'string-key'; } }
class AppErrorTest extends TestCase
{
    /** @dataProvider payloads */
    public function testSummariesPreserveDataAndExposeCurrentFormatting($value,$summary)
    {
        $error = new app_error('details',$value,true);
        $this->assertSame($value,$error->get_data());
        $this->assertSame('details',$error->get_details());
        $this->assertSame($summary,$error->get_data_summary());
        $this->assertStringContainsString((string)$summary,$error->getMessage());
    }
    public function payloads()
    {
        return [[null,'data: (none)'], [true,'data: (bool) true'], [false,'data: (bool) false'],
            [42,42], ['value','value'], [1.5,'data: (double) 1.5'],
            [['key'=>'value'],"data: {\n    \"key\": \"value\"\n}"],
            [(object) ['key'=>'value'],'data: (stdClass) {"key":"value"}'],
            [["invalid\xB1"],'data: (array)'], [(object) ['invalid'=>"\xB1"],'data: (stdClass)']];
    }
    public function testLargePayloadSummariesAreTruncatedAndResourcesAreDescribed()
    {
        $this->assertStringContainsString('(truncated)...}',(new app_error('',[str_repeat('x',600)]))->get_data_summary());
        $handle = fopen('php://memory','r+');
        try { $this->assertSame('data: (resource)',(new app_error('',$handle))->get_data_summary()); }
        finally { fclose($handle); }
        $this->assertSame('application_error',(new app_error)->getMessage());
    }
    /** @dataProvider requiredKeys */
    public function testMissingParameterNamesAcceptStringsListsAndStringableMembers($input,$expected)
    {
        $error = new \flat\lib\exception\missing_required_param($input);
        $this->assertSame($expected,$error->get_required_keys());
        $this->assertStringContainsString(count((array)$expected)>1 ? 'one or more' : 'missing',$error->getMessage());
    }
    public function requiredKeys()
    {
        return [['first,second',['first','second']], [42,['42']], [['named'=>null],['named']],
            [[new StringKey],['string-key']], [[new \stdClass,[]],[]], [null,null], [[],[]]];
    }
}
