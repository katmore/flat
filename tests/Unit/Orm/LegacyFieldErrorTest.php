<?php
namespace flat\test\Unit\Orm;
require_once __DIR__.'/../../Support/PhpSubprocess.php';
use PHPUnit\Framework\TestCase;
class LegacyFieldErrorTest extends TestCase
{
    use \flat\test\Support\PhpSubprocess;
    public function testLegacyFieldFileCanBeIncludedUnderItsActualDeclaredName()
    {
        $file = dirname(__DIR__,3).'/src/flat/orm/invalid_field.php';
        $body = 'require '.var_export($file,true).'; $e=new \\flat\\orm\\invalid_param("name","unknown field"); echo json_encode([$e->get_field_key(),$e->get_reason(),$e->getMessage(),(new ReflectionClass($e))->getFileName()]);';
        list($status,$out,$err) = $this->subprocess($body);
        $this->assertSame(0,$status); $this->assertSame('',$err);
        $this->assertSame(['name','unknown field','invalid field: name, reason: unknown field',$file],json_decode($out,true));
    }
}
