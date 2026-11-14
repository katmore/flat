<?php
namespace flat\test\Unit\Core\Html;
use PHPUnit\Framework\TestCase;
use flat\core\html\encode\form;
class FormTest extends TestCase
{
    public function testTextAndTextareaInputsEscapeNamesAndValues()
    {
        $html = form::keyval_to_input('field"name','<value>&');
        $this->assertStringContainsString('name="field&quot;name"',$html);
        $this->assertStringContainsString('value="&lt;value&gt;&amp;"',$html);
        $this->assertStringContainsString('type="text"',$html);
        $area = form::keyval_to_input('notes','<value>','textarea');
        $this->assertStringContainsString('<textarea name="notes">&lt;value&gt;</textarea>',$area);
        $this->assertStringContainsString('type="text"',form::keyval_to_input('field','value',42));
        $this->assertNull(form::keyval_to_input('field','value','unsupported'));
    }
    public function testNestedFormsApplyNestedTypeMaps()
    {
        $html = form::data_to_form(['top'=>'value', 'nested'=>(object) ['notes'=>'body'], 'more'=>['unmapped'=>'value']], ['nested'=>['notes'=>'textarea'],'top'=>'text']);
        $this->assertStringContainsString('name="top"',$html);
        $this->assertStringContainsString('<textarea name="notes">body</textarea>',$html);
        $this->assertStringContainsString('name="unmapped"',$html);
        $this->assertSame(3,substr_count($html,'<!--START flat/core/html/encode/form::data_to_form-->'));
        $this->assertStringNotContainsString('<input',form::data_to_form(42,[]));
    }
    public function testSerializeReturnsTheConstructedForm()
    {
        $form = form::serialize(['value'=>'text'], ['type_map'=>['value'=>'textarea'],'ignored'=>'option']);
        $this->assertSame(form::data_to_form(['value'=>'text'], ['value'=>'textarea']), $form);
        $this->assertStringContainsString('<textarea name="value">text</textarea>', $form);
    }
    public function testDecodeExplicitlyReportsFeatureNotReady()
    {
        $this->expectException(\flat\core\status\exception\feature_not_ready::class);
        form::unserialize('<input>');
    }
}
