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
    public function fallbackDates() { return [[null],[42],['unparseable date']]; }
    public function testValidDateYearAndLocaleAreKept()
    {
        $query=new dateQuery('2020-07-04','USA','2021','en_US');
        $this->assertSame(strtotime('2020-07-04'),$query->get_date()); $this->assertSame('2021',$query->get_year());
        $this->assertInstanceOf(\Yasumi\Provider\USA::class,$query->getProvider());
        $this->assertSame(2021,$query->getProvider()->getYear());
        $epoch=new dateQuery('1970-01-01 00:00:00 UTC');
        $this->assertSame(0,$epoch->get_date()); $this->assertSame('1970',$epoch->get_year());
    }
    public function testCachedProvidersAreSharedAndDatesUpdate()
    {
        $first=new dateQuery('2020-01-15'); $second=new dateQuery('2020-03-01');
        $this->assertInstanceOf(\Yasumi\Provider\AbstractProvider::class,$first->getProvider());
        $this->assertSame($first->getProvider(),$second->getProvider());
        $first->set_date('2019-07-04','USA');
        $this->assertSame('2019',$first->get_year());
        $this->assertSame(strtotime('2019-07-04'),$first->get_date());
    }
    public function testHolidayAndWorkingDayQueriesUseTheRealProvider()
    {
        $holiday=new dateQuery('2020-07-04','USA');
        $this->assertTrue($holiday->isHoliday()); $this->assertFalse($holiday->isWorkingDay());
        $workday=new dateQuery('2020-07-08','USA');
        $this->assertFalse($workday->isHoliday()); $this->assertTrue($workday->isWorkingDay());
    }
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
