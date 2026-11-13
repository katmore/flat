<?php
namespace flat\test\Unit\Api;
use PHPUnit\Framework\TestCase;
use flat\api\status;
class FlexibleStatus extends status
{
    public $code;
    public $message;
    public function __construct($code, $message) { $this->code = $code; $this->message = $message; }
    public function get_code() { return $this->code; }
    public function get_message() { return $this->message; }
}
class RestrictedStatus extends FlexibleStatus { const CODE_CLASSES = []; }
class StatusTest extends TestCase
{
    /** @dataProvider statuses */
    public function testConcreteStatusesProvideExpectedCodeAndMessage($name, $code, $message)
    {
        $class = 'flat\\api\\status\\'.$name; $status = new $class;
        $this->assertSame($code, $status->get_code());
        $this->assertSame($message, $status->get_message());
        $this->assertSame($message, $status->get_reason_phrase());
        $this->assertSame($code.' '.$message, $status->get_str());
        $this->assertSame($status->get_str(), (string) $status);
    }
    public function statuses()
    {
        return [['ok',200,'OK'], ['bad_request',400,'Bad Request'], ['forbidden',403,'Forbidden'],
            ['not_found',404,'Not Found'], ['unauthorized',401,'Unauthorized'], ['method_not_allowed',405,'Method Not Allowed'],
            ['server_error',500,'Internal Server Error'], ['bad_status',500,'Internal Server Error'],
            ['moved_permanently',301,'Moved Permanently'], ['see_other',303,'See Other']];
    }
    public function testCodeClassesAndNumericInputBoundaries()
    {
        foreach ([100=>'1xx',200=>'2xx',300=>'3xx',400=>'4xx',599=>'5xx',99=>'',600=>''] as $code=>$class) {
            $this->assertSame($class, status::status_code2status_code_class($code));
            $this->assertSame($class, (new FlexibleStatus((string) $code, 'message'))->get_code_class());
        }
        $this->assertSame('', RestrictedStatus::status_code2status_code_class(200));
        $this->assertSame(0, (new FlexibleStatus('invalid', []))->get_code_number());
        $this->assertSame('', (new FlexibleStatus('invalid', []))->get_code_class());
        $this->assertSame('', (new FlexibleStatus('invalid', []))->get_reason_phrase());
        $this->assertSame('', (new FlexibleStatus('invalid', []))->format_code('%code%'));
        $this->assertSame('', status::status_code_class_format('invalid', '%class%'));
    }
    public function testFormattingUsesTheCodeClassMetadata()
    {
        $warnings = [];
        set_error_handler(function ($level,$message) use (&$warnings) { $warnings[] = $message; return true; });
        try {
            $this->assertSame('2xx|Successful|The request was successfully received, understood, and accepted',
                status::status_code_class_format('2xx','%class%|%class-type%|%class-description%'));
            $this->assertSame('', status::status_code_class_format('9xx','%class%'));
            $status = new FlexibleStatus(404,'Not Found');
            $this->assertSame('404 Not Found (4xx Client Error)', $status->format_code('%code% %reason-phrase% (%class% %class-type%)'));
            $this->assertSame('Client Error', $status->get_code_class_type());
            $this->assertSame('The request contains bad syntax or cannot be fulfilled', $status->get_code_class_description());
        } finally { restore_error_handler(); }
        $this->assertSame([], $warnings);
    }
}
