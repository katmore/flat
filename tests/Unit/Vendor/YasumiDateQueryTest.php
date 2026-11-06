<?php
namespace flat\test\Unit\Vendor;
use PHPUnit\Framework\TestCase;
use flat\vendor\azuyalabs\yasumi\dateQuery;

class YasumiDateQueryTest extends TestCase
{
    private $providers;
    private $timezone;
    protected function setUp(): void
    {
        $property=new \ReflectionProperty(dateQuery::class,'_provider_map'); $property->setAccessible(true);
        $this->providers=$property->getValue(); $property->setValue(null,[]);
        $this->timezone=date_default_timezone_get(); date_default_timezone_set('UTC');
    }
    protected function tearDown(): void
    {
        $property=new \ReflectionProperty(dateQuery::class,'_provider_map'); $property->setAccessible(true); $property->setValue(null,$this->providers);
        date_default_timezone_set($this->timezone);
    }
    /** @dataProvider fallbackDates */
    public function testMissingAndUnparseableDatesUseTheCurrentTimestamp($date)
    {
        $before=time(); $query=new dateQuery($date); $after=time();
        $this->assertGreaterThanOrEqual($before,$query->get_date()); $this->assertLessThanOrEqual($after,$query->get_date());
        $this->assertSame(date('Y',$query->get_date()),$query->get_year());
        $this->assertInstanceOf(\Yasumi\Provider\USA::class,$query->getProvider());
    }
    public function fallbackDates() { return [[null],[42],['unparseable date'],['1970-01-01 00:00:00 UTC']]; }
    public function testValidDateAndExplicitYearCurrentlyLeaveTheirBackingFieldsUnset()
    {
        $query=new dateQuery('2020-07-04','USA','2020','ignored locale');
        $this->assertNull($query->get_date()); $this->assertNull($query->get_year());
        $this->assertInstanceOf(\Yasumi\Provider\USA::class,$query->getProvider());
    }
    public function testProviderCacheCurrentlyDoesNotAttachItsHitToTheNextInstance()
    {
        $first=new dateQuery(null); $second=new dateQuery(null);
        $this->assertInstanceOf(\Yasumi\Provider\AbstractProvider::class,$first->getProvider());
        $this->assertNull($second->getProvider());
        $first->set_date('2020-07-04','USA');
        $this->assertSame('2020',$first->get_year());
        // The valid-date branch does not update the prior timestamp.
        $this->assertNotSame(strtotime('2020-07-04'),$first->get_date());
    }
    /** @dataProvider providerMethods */
    public function testRealProviderRejectsTheWrappersRawTimestamp($method)
    {
        $query=new dateQuery(null);
        $this->expectException(\Yasumi\Exception\InvalidDateException::class); $query->$method();
    }
    public function providerMethods() { return [['isWorkingDay'],['isHoliday']]; }
    public function testScheduleComparisonUsesActualNativeDateTimeObjects()
    {
        // Keep the actual current local clock near noon for repeatable native
        // comparisons, including when this suite runs around UTC midnight.
        $offset=(int)gmdate('G')-12;
        date_default_timezone_set('Etc/GMT'.($offset>=0?'+':'').$offset);
        $query=new dateQuery(null); $day=date('D',$query->get_date());
        $this->assertTrue($query->isWithinSchedule([$day=>['09:00 AM'=>'05:00 PM']]));
        $this->assertFalse($query->isWithinSchedule([$day=>['01:00 AM'=>'02:00 AM']]));
        $this->assertFalse($query->isWithinSchedule([$day=>[]]));
    }
}
