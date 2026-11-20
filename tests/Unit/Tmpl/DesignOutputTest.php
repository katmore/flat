<?php
namespace flat\test\Unit\Tmpl;
use PHPUnit\Framework\TestCase;
class DesignOutputFixture extends \flat\tmpl\design_output
{
    public $arguments;
    public function output(): void { $this->arguments = func_get_args(); }
}
class DesignOutputTest extends TestCase
{
    public function testResolvableOutputForwardsTheOptionalTemplateData()
    {
        $design = new DesignOutputFixture; $data = new \flat\tmpl\data;
        $design->resolvable_design_output($data); $this->assertSame([$data],$design->arguments);
        $design->resolvable_design_output(); $this->assertSame([null],$design->arguments);
    }
}
