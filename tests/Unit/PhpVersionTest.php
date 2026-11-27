<?php
namespace flat\test\Unit;
use PHPUnit\Framework\TestCase;
/**
 * The php-7.4 branch is PHP 7.4 and only PHP 7.4 (docker/Dockerfile checks the exact patch).
 * This fails when the suite runs on any other minor version, so a wrong runtime cannot pass.
 */
class PhpVersionTest extends TestCase
{
    public function testSuiteRunsOnPhp74()
    {
        $this->assertSame('7.4', PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION, 'This branch runs on PHP 7.4 only, not ' . PHP_VERSION);
    }
}
