<?php
namespace flat\test\Unit\Orm;
require_once __DIR__.'/../../Support/PhpSubprocess.php';
use PHPUnit\Framework\TestCase;
class LegacyFieldErrorTest extends TestCase
{
    use \flat\test\Support\PhpSubprocess;
    public function testFieldErrorLoadsByItsOwnName()
    {
        $file = dirname(__DIR__,3).'/src/flat/orm/invalid_field.php';
        // A fresh process, so only the autoloader can find the class.
        $body = '$e=new \\flat\\orm\\invalid_field("name","unknown field"); echo json_encode([$e->get_field_key(),$e->get_reason(),$e->getMessage(),(new ReflectionClass($e))->getFileName()]);';
        list($status,$out,$err) = $this->subprocess($body);
        $this->assertSame(0,$status); $this->assertSame('',$err);
        $this->assertSame(['name','unknown field','invalid field: name, reason: unknown field',$file],json_decode($out,true));
    }
}
