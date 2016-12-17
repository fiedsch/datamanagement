<?php

use Fiedsch\Data\Utility\VariablenameMapper;

class VariablenameMapperTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     */
    public function testMappingFound()
    {
        $names = ['a', 'b', 'c', 'd'];
        $mapper = new VariablenameMapper($names);
        foreach ($names as $i => $name) {
            $this->assertEquals($i, $mapper->getColumnNumber($name));
        }
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testMappingNotFound()
    {
        $names = ['a', 'b', 'c', 'd'];
        $mapper = new VariablenameMapper($names);
        $this->assertEquals(-1, $mapper->getColumnNumber('not_in_array'));
        // has to throw a \RuntimeException:
        $mapper = new VariablenameMapper($names, true);
        $this->assertEquals(-1, $mapper->getColumnNumber('not_in_array'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testBadConstructorInput()
    {
        // has to throw a \RuntimeException:
        new VariablenameMapper(['a', 'b', 'a']);
    }

}
