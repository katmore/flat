<?php
namespace flat\test\Unit;
use PHPUnit\Framework\TestCase;
/**
 * The php-8.0 branch is PHP 8.0 and only PHP 8.0 (docker/Dockerfile checks the exact patch).
 * This fails when the suite runs on any other minor version, so a wrong runtime cannot pass.
 */
class PhpVersionTest extends TestCase
{
    public function testSuiteRunsOnPhp80()
    {
        $this->assertSame('8.0', PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION, 'This branch runs on PHP 8.0 only, not ' . PHP_VERSION);
    }
}
