<?php
namespace flat\test\Unit\Core\Text;

use flat\core\text\encode;
use PHPUnit\Framework\TestCase;

class EncodeTest extends TestCase
{
    public function testSerializeString()
    {
        $this->assertSame('hello', encode::serialize('hello'));
    }

    public function testSerializeBoolAndNull()
    {
        $this->assertSame('true', encode::serialize(true));
        $this->assertSame('false', encode::serialize(false));
        $this->assertSame('', encode::serialize(null));
        $this->assertSame("''", encode::serialize(''));
    }

    public function testSerializeScalarNumber()
    {
        $this->assertSame('42', encode::serialize(42));
    }

    public function testSerializeNestedArrayIndents()
    {
        $this->assertSame(
            "a: 1\nb:\n   c: 2",
            encode::serialize(['a' => 1, 'b' => ['c' => 2]])
        );
    }

    public function testSerializeHonoursOptions()
    {
        $this->assertSame(
            "b:\n--c: 2--",
            encode::serialize(['b' => ['c' => 2]], ['ident_size' => 2, 'ident_char' => '-'])
        );
        $this->assertSame(
            '|a: 1|b: 2|',
            encode::serialize(['a' => 1, 'b' => 2], ['newline' => '|'])
        );
    }

    public function testSerializeObject()
    {
        $this->assertSame('a: 1', encode::serialize((object) ['a' => 1]));
        $this->assertSame('', encode::serialize(new \stdClass()));
    }

    public function testSerializeNonPrintableString()
    {
        $this->assertSame('\u0001\u0002', encode::serialize("\x01\x02"));
        // Invalid UTF-8: json_encode() fails, leaving only the leading space.
        $this->assertSame(' ', encode::data_to_text("\xB1\x31"));
    }

    public function testUnserializeThrowsFeatureNotReady()
    {
        $this->expectException(\flat\core\status\exception\feature_not_ready::class);
        encode::unserialize('x');
    }
}
