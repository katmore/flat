<?php
namespace flat\test\Unit;
use PHPUnit\Framework\TestCase;
/**
 * The php-7.3 branch is PHP 7.3 and only PHP 7.3 (docker/Dockerfile checks the exact patch).
 * This fails when the suite runs on any other minor version, so a wrong runtime cannot pass.
 */
class PhpVersionTest extends TestCase
{
    public function testSuiteRunsOnPhp73()
    {
        $this->assertSame('7.3', PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION, 'This branch runs on PHP 7.3 only, not ' . PHP_VERSION);
    }
}
