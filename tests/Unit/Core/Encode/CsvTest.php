<?php
namespace flat\test\Unit\Core\Encode;

use flat\core\encode\csv;
use PHPUnit\Framework\TestCase;

class CsvTest extends TestCase
{
    public function testFields2csvJoinsScalars()
    {
        $this->assertSame("a,b,c\n", csv::fields2csv(['a', 'b', 'c']));
    }

    public function testFields2csvKeepsNumbers()
    {
        $this->assertSame("a,1,2.5\n", csv::fields2csv(['a', 1, 2.5]));
        // Booleans are scalars: true becomes 1, false becomes empty.
        $this->assertSame("t,1,\n", csv::fields2csv(['t', true, false]));
    }

    public function testFields2csvBlanksNonScalars()
    {
        $this->assertSame("a,,,\n", csv::fields2csv(['a', [1], null, new \stdClass()]));
    }

    public function testFields2csvQuotesCommaAndQuote()
    {
        $this->assertSame("\"x,y\",\"q\"\"z\"\n", csv::fields2csv(['x,y', 'q"z']));
    }

    /** Known quirk: serialize()/unserialize() are unimplemented and return null. */
    public function testSerializeAndUnserializeAreNoOps()
    {
        $this->assertNull(csv::serialize(['a']));
        $this->assertNull(csv::unserialize('a'));
    }
}
