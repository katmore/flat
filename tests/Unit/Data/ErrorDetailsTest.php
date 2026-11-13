<?php
namespace flat\test\Unit\Data;
use PHPUnit\Framework\TestCase;
class ErrorDetailsTest extends TestCase
{
    public function testValidationFailureCollectsStringFieldsAndKeepsItsMessage()
    {
        foreach ([null, '', 42] as $field) {
            $failure = new \flat\data\validate_failure($field, 'local message');
            $this->assertSame([], $failure->get_field());
            $this->assertSame('local message', $failure->get_message());
            $this->assertSame('local message', $failure->getMessage());
        }
        $this->assertNull((new \flat\data\validate_failure('email'))->get_message());
        $failure = new \flat\data\validate_failure('email');
        $failure->add_field('name');
        $this->assertSame(['email','name'], $failure->get_field());
        $failure = new \flat\data\validate_failure(['email',42,false,'name']);
        $this->assertSame(['email','name'], $failure->get_field());
    }
    public function testDataNotFoundRetainsLookupContext()
    {
        $error = new \flat\data\exception\not_found('customer', ['id'=>42]);
        $this->assertSame('customer', $error->get_details());
        $this->assertSame(['id'=>42], $error->get_data());
        $this->assertSame('data not found: customer', $error->getMessage());
        $this->assertSame('data not found', (new \flat\data\exception\not_found)->getMessage());
    }
    public function testRandomFailurePreservesRequestedLength()
    {
        foreach ([null,0,'12',12] as $length) {
            $error = new \flat\core\rand\exception\failed($length);
            $this->assertSame($length, $error->get_len());
            $this->assertSame('failed to create random value'.($length === 12 ? ' of 12 length' : ''), $error->getMessage());
        }
    }
    public function testCliErrorExposesStatusUsageFlagAndData()
    {
        $error = new \flat\cli\error('bad input', 7, true, ['field'=>'id']);
        $this->assertSame('bad input', $error->get_details());
        $this->assertSame(7, $error->get_status());
        $this->assertTrue($error->is_display_usage_on());
        $this->assertSame(['field'=>'id'], $error->get_data());
        $error = new \flat\cli\error('bad input', 1, false);
        $this->assertFalse($error->is_display_usage_on());
        $this->assertSame([], $error->get_data());
    }
    public function testCloudErrorsExposeParameterAndChildClass()
    {
        $error = new \flat\cloud\ipinfodb\bad_param('ip', 'invalid address');
        $this->assertSame('ip', $error->get_param());
        $this->assertSame('invalid address', $error->get_details());
        foreach ([\flat\cloud\authorize\sandbox_mismatch::class, \flat\cloud\authorize\log_file_mismatch::class] as $class) {
            $error = new $class('LocalPayment');
            $this->assertSame('LocalPayment', $error->get_child_class_name());
            $this->assertStringContainsString('LocalPayment::', $error->getMessage());
        }
    }
}
