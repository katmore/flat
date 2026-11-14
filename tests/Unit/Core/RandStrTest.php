<?php
namespace flat\test\Unit\Core;

use flat\core\rand\str;
use PHPUnit\Framework\TestCase;

class RandStrTest extends TestCase
{
    public function testStrDefaultLengthAndCharset()
    {
        $value = str::get();
        $this->assertSame(20, strlen($value));
        $this->assertSame(1, preg_match('/^[A-Za-z0-9]+$/', $value), $value);
    }
    public function testDefaultPoolDrawsFromDigitsAndBothCases()
    {
        $sample = str::get(600);
        $this->assertSame(600,strlen($sample));
        foreach (['/[0-9]/','/[a-z]/','/[A-Z]/'] as $class) $this->assertSame(1,preg_match($class,$sample),$class);
    }

    public function testStrCustomCharpoolAndLength()
    {
        $value = str::get(5, ['charpool' => 'ab']);
        $this->assertSame(1, preg_match('/^[ab]{5}$/', $value), $value);

        $object = str::load(5, ['load_object' => true]);
        $this->assertInstanceOf(str::class, $object);
        $this->assertIsString(str::load(5));
        $this->assertSame(5, strlen((string) $object));

        $lowerOnly = str::get(10, ['use_upperalpha' => false, 'use_digits' => false]);
        $this->assertSame(1, preg_match('/^[a-z]{10}$/', $lowerOnly), $lowerOnly);
    }

    public function testStrGenerateAndInvoke()
    {
        $object = str::load(8, ['load_object' => true]);
        $generated = $object->generate();
        $this->assertSame(8, strlen($generated));
        $this->assertSame(1, preg_match('/^[A-Za-z0-9]+$/', $generated), $generated);
        $invoked = $object(5);
        $this->assertIsString($invoked);
        $this->assertSame(5, strlen($invoked));
    }

    public function testInvokingAnInstanceReusesItsPoolAndLength()
    {
        $object = str::load(12, ['charpool' => 'xy', 'load_object' => true]);
        $this->assertSame(1, preg_match('/^[xy]{12}$/', $object()));
        $this->assertSame(1, preg_match('/^[xy]{5}$/', $object(5)));
        // Explicit options build a new pool, as str::load() does.
        $this->assertSame(1, preg_match('/^[01]{6}$/', $object(6, ['charpool' => '01'])));
    }

    public function testStrGetStringWithExplicitLength()
    {
        $object = str::load(8, ['load_object' => true]);
        $this->assertSame(5, strlen($object->get_string(5)));
        // Length below min_len is ignored; configured length is kept.
        $this->assertSame(8, strlen($object->get_string(0)));
    }

    public function testStrOptionsAsList()
    {
        $value = str::get(10, ['use_digits']);
        $this->assertSame(10, strlen($value));
        $this->assertSame(1, preg_match('/^[A-Za-z0-9]+$/', $value), $value);
    }

    public function testStrWithoutLowerAlpha()
    {
        $value = str::get(10, ['use_loweralpha' => false]);
        $this->assertSame(1, preg_match('/^[A-Z0-9]{10}$/', $value), $value);
    }

    public function testStrNonStringCharpoolThrowsBadConfig()
    {
        $this->expectException(\flat\core\rand\exception\bad_config::class);
        str::get(5, ['charpool' => 123]);
    }

    public function testStrTooSmallCharpoolThrows()
    {
        $this->expectException(\flat\core\rand\exception\not_enough_valid_chars::class);
        str::get(5, ['charpool' => 'a']);
    }
}
