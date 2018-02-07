<?php

use Fiedsch\Data\File\Helper;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{

    public function testSC()
    {
        $data = str_split("abcdefghijklmnopqrstuvwxyz");
        foreach ($data as $i => $letter) {
            $this->assertEquals($i, Helper::SC($letter));
        }
        $this->assertEquals(Helper::SC('aa'), Helper::SC('z')+1);
        $this->assertEquals(Helper::SC('az'), Helper::SC('z')+26);
        $this->assertEquals(Helper::SC('ba'), Helper::SC('az')+1);
    }

    public function testGetBySC()
    {
        $data = str_split("abcdefghijklmnopqrstuvwxyz");
        foreach ($data as $letter) {
            $data[] = "a$letter";
        }

        foreach ($data as $letter) {
            $this->assertEquals($letter, Helper::getBySC($data, $letter));
        }

        // accessing data at non existing index: return null
        $this->assertEquals(null, Helper::getBySC($data, 'xy'));
    }

    public function testSetArrayKeys() {
        $data = [1,2,3];
        $names = ['one', 'two', 'three'];
        $expected = ['one'=>1, 'two'=>2, 'three'=>3];
        $this->assertEquals($expected, Helper::setArrayKeys($data, $names));

        $this->expectException(\RuntimeException::class);
        $this->assertEquals($expected, Helper::setArrayKeys($data, ['one'=>1, 'two'=>2]));
    }

}
