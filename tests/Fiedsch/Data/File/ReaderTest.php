<?php

use Fiedsch\Data\File\Reader;


class ReaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $filepath = 'tests/assets/data.txt';

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * setup for all tests
     */
    protected function setUp()
    {
        $this->reader = new Reader($this->filepath);
    }

    /**
     * clean up after all tests
     */
    protected function tearDown()
    {
        $this->reader->close();
    }

    /**
     * test line numbering
     */
    public function testLinenumbering()
    {
        $i = 0;
        $this->assertEquals($i, $this->reader->getLineNumber());
        while (($line = $this->reader->getLine()) !== null) {
                $this->assertEquals(++$i, $this->reader->getLineNumber());
        }
    }

    /**
     * Test skip empty lines
     */
    public function testSkipEmptyLines()
    {
        $i = 0;
        while (($line = $this->reader->getLine(Reader::SKIP_EMPTY_LINES)) !== null) {
            ++$i;
        }
        $this->assertEquals(4, $i);
        $this->assertEquals(5, $this->reader->getLineNumber());
    }

    /**
     * Test do not skip empty lines (default behaviour)
     */
    public function testDoNotSkipEmptyLines()
    {
        $i = 0;
        while (($line = $this->reader->getLine()) !== null) {
            ++$i;
        }
        $this->assertEquals(5, $i);
        $this->assertEquals(5, $this->reader->getLineNumber());
    }

}