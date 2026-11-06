<?php
namespace flat\test\Unit\Orm;
use PHPUnit\Framework\TestCase;
class ReportFixture extends \flat\report { public function metadata() { return $this->_get_meta(); } }
class MetadataReportFixture extends ReportFixture implements \flat\report\meta
{
    public static $provided;
    public function get_meta() { return self::$provided; }
}
class ReportTest extends TestCase
{
    public function testMetadataProviderSuppliesTheSourceAndIsRetained()
    {
        MetadataReportFixture::$provided = new \flat\report\meta\data(['source'=>\flat\db\driver\pdo::class]);
        try {
            $report = new MetadataReportFixture(\stdClass::class);
            $this->assertSame(MetadataReportFixture::$provided,$report->metadata());
        } finally { MetadataReportFixture::$provided = null; }
    }
    public function testReportAcceptsDatabaseClassNamesWithoutInstantiatingThem()
    {
        $report = new ReportFixture(\flat\db\driver\pdo::class);
        $this->assertNull($report->metadata());
    }
    public function testReportRejectsNonDatabaseSources()
    {
        $this->expectException(\flat\report\exception\invalid_source::class);
        $this->expectExceptionMessage('resolved source must be');
        new ReportFixture(\stdClass::class);
    }
}
