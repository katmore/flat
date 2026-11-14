<?php
namespace flat\test\Unit\Core;

use flat\core\uuid;
use PHPUnit\Framework\TestCase;

class UuidTest extends TestCase
{
    const DNS = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';

    public function testV3MatchesRfc4122Vector()
    {
        // Vector computed independently (Python uuid.uuid3).
        $this->assertSame(
            '5df41881-3aed-3515-88a7-2f4a814cf09e',
            uuid::get_v3(self::DNS, 'www.example.com')
        );
    }

    public function testV5MatchesRfc4122Vector()
    {
        // Vector computed independently (Python uuid.uuid5).
        $this->assertSame(
            '2ed6657d-e927-568b-95e1-2665a8aea6a2',
            uuid::get_v5(self::DNS, 'www.example.com')
        );
    }

    public function testV3AcceptsBracedAndUnhyphenatedNamespace()
    {
        $expected = uuid::get_v3(self::DNS, 'www.example.com');
        $this->assertSame($expected, uuid::get_v3('{6ba7b810-9dad-11d1-80b4-00c04fd430c8}', 'www.example.com'));
        $this->assertSame($expected, uuid::get_v3('6ba7b8109dad11d180b400c04fd430c8', 'www.example.com'));
    }

    public function testV4FormatVersionAndVariant()
    {
        $seen = [];
        for ($i = 0; $i < 100; $i++) {
            $value = uuid::get_v4();
            $this->assertSame(1, preg_match(
                '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
                $value
            ), $value);
            $seen[$value] = true;
        }
        $this->assertCount(100, $seen);
    }

    public function testGetDefaultsToV4()
    {
        $this->assertSame('4', uuid::get()[14]);
        $this->assertSame('4', uuid::get(4)[14]);
    }

    public function testGetArrayFormV3AndV5()
    {
        $this->assertSame(
            '5df41881-3aed-3515-88a7-2f4a814cf09e',
            uuid::get(['version' => 3, 'namespace' => self::DNS, 'name' => 'www.example.com'])
        );
        $this->assertSame(
            '2ed6657d-e927-568b-95e1-2665a8aea6a2',
            uuid::get(['version' => 5, 'namespace' => self::DNS, 'name' => 'www.example.com'])
        );
    }

    public function testGetWithNamespaceAndVersion4Throws()
    {
        $this->expectException(\flat\core\uuid\exception\namespace_unused_version4::class);
        uuid::get(4, self::DNS, 'x');
    }

    public function testGetWithNamespaceAndBadVersionThrows()
    {
        $this->expectException(\flat\core\uuid\exception\invalid_version::class);
        uuid::get(2, self::DNS, 'x');
    }

    public function testGetWithoutNamespaceRejectsVersionsOtherThanThreeFourOrFive()
    {
        foreach ([3,4,5] as $version) $this->assertSame('4', uuid::get($version)[14]);
        $this->expectException(\flat\core\uuid\exception\invalid_version::class);
        uuid::get(2);
    }

    public function testInvalidNamespaceThrows()
    {
        foreach ([[''], ['nope']] as $args) {
            try {
                uuid::get_v3($args[0], 'x');
                $this->fail('expected invalid_namespace for ' . var_export($args[0], true));
            } catch (\flat\core\uuid\exception\invalid_namespace $e) {
                $this->addToAssertionCount(1);
            }
        }
        try {
            uuid::get_v3(123, 'x');
            $this->fail('expected invalid_namespace for non-string namespace');
        } catch (\flat\core\uuid\exception\invalid_namespace $e) {
            $this->addToAssertionCount(1);
        }
    }
    public function testInvokeGeneratesTheRequestedVersion()
    {
        $u = new uuid();
        $this->assertSame('5df41881-3aed-3515-88a7-2f4a814cf09e', $u(3, self::DNS, 'www.example.com'));
        $this->assertSame('2ed6657d-e927-568b-95e1-2665a8aea6a2', $u(5, self::DNS, 'www.example.com'));
        $this->assertSame('4', $u()[14]);
        $this->assertNotSame($u(), $u());
        $this->assertSame('5df41881-3aed-3515-88a7-2f4a814cf09e', $u(['version'=>3,'namespace'=>self::DNS,'name'=>'www.example.com']));
    }

    public function testToStringReturnsUuidForConstructorState()
    {
        $uuid = new uuid(3, self::DNS, 'www.example.com');
        $this->assertSame('5df41881-3aed-3515-88a7-2f4a814cf09e', (string) $uuid);
    }
    public function testConfiguredStateDrivesGenerateAndStringConversion()
    {
        $v3 = new uuid(3, self::DNS, 'www.example.com');
        $this->assertSame('5df41881-3aed-3515-88a7-2f4a814cf09e', $v3->generate());
        $v5 = new uuid(5, self::DNS, 'www.example.com');
        $this->assertSame('2ed6657d-e927-568b-95e1-2665a8aea6a2', $v5->generate());
        $this->assertSame('2ed6657d-e927-568b-95e1-2665a8aea6a2', (string) $v5);
        // Names and versions are part of the state: changing either changes the UUID.
        $this->assertNotSame($v5->generate(), (new uuid(5, self::DNS, 'other.example.com'))->generate());
        $this->assertNotSame($v5->generate(), $v3->generate());
        // A version 4 object has no name to derive from, so each call is new.
        $v4 = new uuid(4);
        $this->assertSame('4', $v4->generate()[14]);
        $this->assertNotSame($v4->generate(), $v4->generate());
    }

    public function testGenerateUsesConstructorState()
    {
        $value = (new uuid())->generate();
        $this->assertSame(1, preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $value
        ), $value);
    }
}
