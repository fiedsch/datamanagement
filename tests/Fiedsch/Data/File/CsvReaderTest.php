<?php

use Fiedsch\Data\File\CsvReader;


class CsvReaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $filepath = 'tests/assets/data.txt';

    /**
     * @var string
     */
    protected $separator = ';';


    /**
     * @var CsvReader
     */
    protected $reader;

    /**
     * setup for all tests
     */
    protected function setUp()
    {
        $this->reader = new CsvReader($this->filepath, $this->separator);
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
        while (($line = $this->reader->getLine(CsvReader::SKIP_EMPTY_LINES)) !== null) {
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

    /**
     *
     */
    public function testDelimiter()
    {
        $this->assertEquals($this->separator, $this->reader->getDelimiter());
    }

    /**
     * in $this->setUp() we did not specify the enclosure character, so
     * we expect it to be the default which is <code>"</code>
     */
    public function testEnclosure()
    {
        $this->assertEquals('"', $this->reader->getEnclosure());
    }

    /**
     * in $this->setUp() we did not specify the escape character, so
     * we expect it to be the default which is <code>\</code>
     */
    public function testEscape()
    {
        $this->assertEquals('\\', $this->reader->getEscape());
    }

}
