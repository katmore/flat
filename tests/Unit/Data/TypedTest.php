<?php
namespace flat\test\Unit\Data\TypedBase {
    class item extends \flat\data { public $value; }
}
namespace flat\test\Unit\Data {
    use PHPUnit\Framework\TestCase;
    class TypedBase extends \flat\data\typed\loader {}
    class TypedTest extends TestCase
    {
        public function testRelativeAndAbsoluteTypesAndMissingNames()
        {
            $class = '\\'.TypedBase\item::class;
            $this->assertSame($class,TypedBase::typed_class('item'));
            $this->assertSame($class,TypedBase::typed_class($class));
            $this->assertNull(TypedBase::typed_class('missing'));
            $this->assertNull(TypedBase::typed_class(''));
            $this->assertSame('item',TypedBase::typed_name(['type'=>'item']));
            $this->assertSame('item',TypedBase::typed_name('item'));
            $this->assertNull(TypedBase::typed_name('missing'));
        }
        public function testTypedLoadingUsesTheDiscriminatorOrExplicitOverride()
        {
            $data = TypedBase::typed_load(['type'=>'item','value'=>42]);
            $this->assertInstanceOf(TypedBase\item::class,$data); $this->assertSame(42,$data->value);
            $this->assertFalse(property_exists($data,'type'));
            $this->assertSame('value',TypedBase::typed_load(['value'=>'value'],'item')->value);
            $this->assertNull(TypedBase::typed_load(['type'=>'missing']));
        }
    /** @dataProvider unusableExplicitTypes */
    public function testUnusableExplicitTypesFallBackToTheDiscriminator($explicit)
    {
        $data = TypedBase::typed_load(['type'=>'item','value'=>7],$explicit);
        $this->assertInstanceOf(TypedBase\item::class,$data);
        $this->assertSame(7,$data->value);
    }
    public function unusableExplicitTypes() { return [[''],[null],[5],[['item']]]; }
    public function testAnExplicitTypeWinsOverTheDiscriminator()
    {
        $data = TypedBase::typed_load(['type'=>'missing','value'=>'kept'],'item');
        $this->assertInstanceOf(TypedBase\item::class,$data);
        $this->assertSame('kept',$data->value);
    }
        public function testMediaDefaultsAndExplicitMetadata()
        {
            $media = new \flat\data\datestamped\media;
            $this->assertInstanceOf(\stdClass::class,$media->meta);
            $this->assertInstanceOf(\stdClass::class,$media->url); $this->assertSame([],$media->history);
            $explicit = new \flat\data\datestamped\media(['meta'=>['key'=>'value'],'url'=>'url','history'=>['entry']]);
            $this->assertSame(['key'=>'value'],$explicit->meta); $this->assertSame('url',$explicit->url);
            $this->assertSame(['entry'],$explicit->history);
        }
        public function testHistoryPreservesProvidedTimestampsAndGeneratesMissingOnes()
        {
            $before = time(); $history = new \flat\data\history('entry',['url']);
            $this->assertSame('entry',$history->str); $this->assertSame(['url'],$history->assoc);
            $this->assertGreaterThanOrEqual($before,strtotime($history->date));
            $this->assertLessThanOrEqual(time(),strtotime($history->date));
            $this->assertSame('2000-01-01T00:00:00+00:00',(new \flat\data\history('entry',null,'2000-01-01T00:00:00+00:00'))->date);
        }
    }
}
