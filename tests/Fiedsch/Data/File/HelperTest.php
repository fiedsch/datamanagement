<?php

declare(strict_types=1);

use Fiedsch\Data\File\Helper;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{

    public function testSC(): void
    {
        $data = str_split("abcdefghijklmnopqrstuvwxyz");
        foreach ($data as $i => $letter) {
            $this->assertEquals($i, Helper::SC($letter));
        }
        $this->assertEquals(Helper::SC('aa'), Helper::SC('z')+1);
        $this->assertEquals(Helper::SC('az'), Helper::SC('z')+26);
        $this->assertEquals(Helper::SC('ba'), Helper::SC('az')+1);
    }

    public function testGetBySC(): void
    {
        $data = str_split("abcdefghijklmnopqrstuvwxyz");
        foreach ($data as $letter) {
            $data[] = "a$letter";
        }

        foreach ($data as $letter) {
            $this->assertEquals($letter, Helper::getBySC($data, $letter));
        }

        // accessing data at non-existing index: return null
        $this->assertEquals(null, Helper::getBySC($data, 'xy'));
    }

    /**
     * @expectedDeprecation Deprecated. Use toNamedArray() instead.
     * @noinspection PhpDeprecationInspection
     */
    public function testSetArrayKeys(): void
    {
        $data = [1,2,3];
        $names = ['one', 'two', 'three'];
        $expected = ['one'=>1, 'two'=>2, 'three'=>3];
        $this->assertEquals($expected, Helper::setArrayKeys($data, $names));

        $this->expectException(RuntimeException::class);
        $this->assertEquals($expected, Helper::setArrayKeys($data, ['one'=>1, 'two'=>2]));
    }

    public function testToNamedArray(): void
    {
        $data = [1,2,3];
        $names = ['one', 'two', 'three'];
        $expected = ['one'=>1, 'two'=>2, 'three'=>3];
        $this->assertEquals($expected, Helper::toNamedArray($data, $names));

        $this->expectException(RuntimeException::class);
        $this->assertEquals($expected, Helper::toNamedArray($data, ['one'=>1, 'two'=>2]));
    }

}
