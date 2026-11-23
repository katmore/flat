<?php
namespace flat\test\Unit\Cloud;
use PHPUnit\Framework\TestCase;
class AuthorizeDeprecationTest extends TestCase
{
    public function testEveryAuthorizeNetWrapperDeclarationIsMarkedDeprecated()
    {
        $root = dirname(__DIR__, 3).'/src/flat/cloud/authorize';
        $files = new \RegexIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root)), '/\.php$/');
        $declaring = 0;
        foreach ($files as $file) {
            $code = file_get_contents($file->getPathname());
            if (!preg_match('/^(abstract |final )?(class|interface|trait) /m', $code)) continue; // a shim that only requires another file
            $declaring++;
            $this->assertStringContainsString('@deprecated since v1.73.1', $code, $file->getPathname().' must be marked deprecated');
        }
        $this->assertSame(47, $declaring);
    }
}
