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
    public function testCountryOneAndTextExtensions()
    {
        $country = new phone(['original'=>'+1 800 555 1234']);
        $this->assertNull($country->extension);
        $this->assertSame('(800) 555-1234',(string)$country);
        $long = new phone(['original'=>'1800555123499']);
        $this->assertSame('99',$long->extension);
        $this->assertSame('(800) 555-1234 x99',(string)$long);
        $ext = new phone(['original'=>'8005551234 ext 42']);
        $this->assertSame('42',$ext->extension);
        $this->assertSame('(800) 555-1234 x42',(string)$ext);
        $x = new phone(['original'=>'5551234 x89']);
        $this->assertSame('89',$x->extension);
        $this->assertSame('555-1234 x89',(string)$x);
        $this->assertNull((new phone(['original'=>'5551234 ext']))->extension);
    }
    /** @dataProvider internationalPrefixes */
    public function testInternationalNumbersGetTheirCountryCode($prefix,$country)
    {
        $number = $prefix.str_repeat('1',12-strlen($prefix));
        $phone = new phone(['original'=>$number]);
        $this->assertSame($number,$phone->numeric);
        $this->assertSame(substr($number,0,1),$phone->zone);
        $this->assertSame($country,$phone->country_code);
        $this->assertSame('+'.$number,$phone->e164);
        $this->assertSame($number,(string)$phone);
    }
    public function internationalPrefixes()
    {
        // Expected codes follow the country-code table written in data/phone.php.
        $rows=[]; foreach (['20'=>'20','21'=>'211','30'=>'30','35'=>'351','40'=>'40','42'=>'421','45'=>'45','51'=>'51',
            '50'=>'501','60'=>'60','67'=>'671','81'=>'81','85'=>'851','90'=>'90','91'=>'91','93'=>'93','96'=>'961',
            '99534'=>'99534','99512'=>'995','999'=>'999',
            '31'=>'31','32'=>'32','33'=>'33','34'=>'34','36'=>'36','39'=>'39','37'=>'371',
            '61'=>'61','62'=>'62','63'=>'63','64'=>'64','65'=>'65','66'=>'66'] as $prefix=>$country) $rows[]=[(string)$prefix,$country];
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
