<?php
namespace flat\test\Unit\Core\Util\Image;
use PHPUnit\Framework\TestCase;
use flat\core\util\image;
class NativeDim extends image\dim { public $imagick; }
class ImageTest extends TestCase
{
    private function picture($width=20,$height=10)
    {
        $image = new \Imagick; $image->newImage($width,$height,new \ImagickPixel('red')); $image->setImageFormat('ppm'); return $image;
    }
    public function testBlobBase64SaveAndLoadUseRealImages()
    {
        $native = $this->picture(); $image = new image($native);
        $this->assertSame($native,$image->get_imagick());
        $this->assertSame($native->getImageBlob(),$image->get_blob());
        $this->assertSame(base64_encode($image->get_blob()),$image->get_base64());
        $directory = sys_get_temp_dir().'/flat-image-'.bin2hex(random_bytes(8)); mkdir($directory); $path = $directory.'/picture.ppm';
        try {
            $image->save($path); $loaded = new image($path);
            $this->assertSame(['width'=>20,'height'=>10],$loaded->get_imagick()->getImageGeometry());
            $this->assertSame(['width'=>0,'height'=>0,'x'=>0,'y'=>0],$loaded->get_imagick()->getImagePage());
        } finally { if (is_file($path)) unlink($path); rmdir($directory); $native->clear(); }
    }
    public function testDimensionExtractionClonesTheImageWithoutChangingItsPage()
    {
        $native = $this->picture(); $native->setImagePage(100,100,3,4);
        $dimensions = new NativeDim(['imagick'=>$native]);
        $this->assertSame('20x10',(string) $dimensions);
        $this->assertSame(['width'=>100,'height'=>100,'x'=>3,'y'=>4],$native->getImagePage());
        $native->clear();
    }
    /** @dataProvider resizeCases */
    public function testSingleAxisResizePreservesTheOriginalRatio($method,$target,$expected)
    {
        $native = $this->picture();
        try { image::$method($native,$target); $this->assertSame($expected,$native->getImageGeometry()); }
        finally { $native->clear(); }
    }
    public function resizeCases()
    {
        return [['resize_width_proportional',40,['width'=>40,'height'=>20]],
            ['resize_width_proportional',10,['width'=>10,'height'=>5]],
            ['resize_width_proportional',20,['width'=>20,'height'=>10]],
            ['resize_height_proportional',20,['width'=>40,'height'=>20]],
            ['resize_height_proportional',5,['width'=>10,'height'=>5]],
            ['resize_height_proportional',10,['width'=>20,'height'=>10]]];
    }
    public function testMinimumBoundsAndEqualHeightDoNotIgnoreRequiredWidth()
    {
        $native = $this->picture();
        try {
            image::resize_min_proportional($native,20,10);
            $this->assertSame(['width'=>20,'height'=>10],$native->getImageGeometry());
            image::resize_min_proportional($native,40,10);
            $this->assertSame(['width'=>40,'height'=>20],$native->getImageGeometry());
            image::resize_min_proportional($native,40,40);
            $this->assertSame(['width'=>80,'height'=>40],$native->getImageGeometry());
        } finally { $native->clear(); }
    }
    public function testSingleAxisConstructorsDelegateToProportionalResizing()
    {
        foreach ([[40,0],[0,20]] as $bounds) {
            $native = $this->picture();
            try {
                $image = new image($native,$bounds[0],$bounds[1]);
                $this->assertSame(['width'=>40,'height'=>20],$image->get_imagick()->getImageGeometry());
            } finally { $native->clear(); }
        }
    }
    /** @group known-defect */
    public function testBothConstructorBoundsCurrentlyLeaveTheImageUnchanged()
    {
        $native = $this->picture();
        try {
            $image = new image($native,40,40);
            $this->assertSame(['width'=>20,'height'=>10],$image->get_imagick()->getImageGeometry());
        } finally { $native->clear(); }
    }
}
