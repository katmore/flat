<?php
namespace flat\test\Unit\Vendor;
use PHPUnit\Framework\TestCase;
use flat\vendor\neitanod\forceutf8\Encoding;
class EncodingTest extends TestCase
{
    /** @dataProvider byteSequences */
    public function testConversionPreservesValidUtf8AndRepairsLegacyBytes($input,$expected)
    {
        $this->assertSame($expected,Encoding::toUTF8($input));
    }
    public function byteSequences()
    {
        return [['ascii','ascii'], ["\xc3\xa9","é"], ["\xe2\x82\xac","€"], ["\xf0\x9f\x98\x80","😀"],
            ["\xc3A","ÃA"], ["\xe9A","éA"], ["\xf0A","ðA"], ["\xf8A","øA"],
            ["\x80","€"], ["\xa0","\xc2\xa0"], [42,42], [null,null], [false,false]];
    }
    public function testNestedArraysAndWindowsEncodingAliases()
    {
        $this->assertSame(['key'=>['café',42]],Encoding::toUTF8(['key'=>["caf\xe9",42]]));
        $this->assertSame(['key'=>["caf\xe9",42]],Encoding::toWin1252(['key'=>['café',42]]));
        $this->assertSame("caf\xe9",Encoding::toISO8859('café'));
        $this->assertSame("caf\xe9",Encoding::toLatin1('café'));
        $this->assertSame(['café',['café']],Encoding::fixUTF8(['cafÃ©',['cafÃ©']]));
        $this->assertSame('€',Encoding::UTF8FixWin1252Chars("\xc2\x80"));
    }
    public function testBomRemovalAndEncodingLabels()
    {
        $this->assertSame('text',Encoding::removeBOM("\xef\xbb\xbftext"));
        $this->assertSame('text',Encoding::removeBOM('text'));
        $this->assertSame('',Encoding::removeBOM());
        foreach (['iso-8859-1','latin1','windows-1252','ISO','win1252'] as $label) {
            $this->assertSame('ISO-8859-1',Encoding::normalizeEncoding($label));
            $this->assertSame("caf\xe9",Encoding::encode($label,'café'));
        }
        $this->assertSame('UTF-8',Encoding::normalizeEncoding('unknown'));
        $this->assertSame('café',Encoding::encode('utf8',"caf\xe9"));
    }
    public function testIconvOptionsUseTheActualExtension()
    {
        foreach ([Encoding::ICONV_TRANSLIT,Encoding::ICONV_IGNORE,''] as $option) {
            $this->assertSame("caf\xe9",Encoding::toWin1252('café',$option));
        }
        $this->assertSame('',Encoding::toWin1252('😀',Encoding::ICONV_IGNORE));
    }
}
