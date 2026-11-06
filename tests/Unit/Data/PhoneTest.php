<?php
namespace flat\test\Unit\Data;
use PHPUnit\Framework\TestCase;
use flat\data\phone;
class VisiblePhone extends phone { public $value; }
class PhoneTest extends TestCase
{
    public function testNationalAndLocalNanpFormatting()
    {
        $national = new phone(['original'=>'800—555–1234']);
        $this->assertSame('(800) 555-1234',(string)$national);
        $this->assertSame('8005551234',$national->numeric);
        $this->assertSame('800',$national->area_code); $this->assertSame('555',$national->exchange_code);
        $this->assertSame('1234',$national->subscriber_number);
        $this->assertSame('+18005551234',$national->e164);
        $this->assertSame('NANP-natl',$national->notation);
        $local = new phone(['original'=>'5551234','extension'=>'42']);
        $this->assertSame('555-1234 x42',(string)$local); $this->assertSame('NANP-local',$local->notation);
        $this->assertSame('5551234',$local->e164_ndc);
    }
    public function testEmptyInputAndProtectedValueExtensionPoint()
    {
        $this->assertSame('',(string)new phone);
        $value = new VisiblePhone(['value'=>'5551234']);
        $this->assertSame('5551234',$value->original); $this->assertSame('555-1234',(string)$value);
        $short = new phone(['original'=>'1234','extension'=>'42']);
        $this->assertSame('1234 ext. 42',(string)$short);
        $this->assertSame('',(string)new phone(['original'=>'letters']));
    }
    public function testCurrentCountryOneAndTextExtensionExtractionFailures()
    {
        $country = new phone(['original'=>'+1 800 555 1234']);
        $this->assertSame('8005551234',$country->extension);
        $this->assertSame('(800) 555-1234 x8005551234',(string)$country);
        $ext = new phone(['original'=>'8005551234 ext 42']);
        $this->assertSame('8005551234',$ext->extension);
        $x = new phone(['original'=>'5551234 x89']);
        $this->assertSame('5551234',$x->extension);
    }
    /** @dataProvider internationalPrefixes */
    public function testCurrentInternationalClassificationLeavesComparisonAssignmentsUnset($prefix,$country)
    {
        $number = $prefix.str_repeat('1',12-strlen($prefix));
        $phone = new phone(['original'=>$number]);
        $this->assertSame($number,$phone->numeric);
        $this->assertSame(substr($number,0,1),$phone->zone);
        $this->assertSame($country,$phone->country_code);
        $this->assertSame($number,(string)$phone);
    }
    public function internationalPrefixes()
    {
        $rows=[]; foreach (['20','21','30','35','40','42','51','50','60','67','81','85','90','91','99534','99512','999','93'] as $prefix) $rows[]=[$prefix,null];
        foreach (['7365'=>'7365','7426'=>7426,'76'=>'7611','7840'=>'7840','7869'=>'7869','75'=>7,'77'=>7,'73'=>7] as $prefix=>$country) $rows[]=[(string)$prefix,$country];
        return $rows;
    }
    public function testInternationalDialingPrefixAndExistingFormattedMetadata()
    {
        $phone = new phone(['original'=>'011442012345678','e164_intl'=>'provided','string'=>'provided']);
        $this->assertSame('442012345678',$phone->numeric);
        $this->assertSame('provided',$phone->e164_intl);
        $this->assertSame('442012345678',(string)$phone);
    }
}
