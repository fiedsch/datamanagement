<?php

use Fiedsch\Data\File\Helper;

class HelperTest extends PHPUnit_Framework_TestCase
{

    public function testSC()
    {

    }
    public function testGetBySC()
    {
        $data = str_split("abcdefghijklmnopqrstuvwxyz");
        foreach($data as $letter) {
            $data[] = "a$letter";
        }

        foreach($data as $letter) {
            $this->assertEquals($letter, Helper::getBySC($data, $letter));
        }

        // accessing data at non existing index: return null
        $this->assertEquals(null, Helper::getBySC($data, 'xy'));
    }

}