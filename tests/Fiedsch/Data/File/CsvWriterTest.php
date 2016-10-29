<?php

use Fiedsch\Data\File\CsvWriter;
use Fiedsch\Data\File\CsvReader;

class CsvWriterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $filepath = 'tests/assets/tempfile.csv';

    /**
     * @var CsvWriter
     */
    protected $writer;

    /**
     * @var string
     */
    protected $separator = '|';

    /**
     * setup for all tests
     */
    protected function setUp()
    {
        $this->writer = new CsvWriter($this->filepath, $this->separator);
    }

    /**
     * clean up after all tests
     */
    protected function tearDown()
    {
        $this->writer->close();
        unlink($this->writer->getFilePath());
    }

    /**
     * test line numbering
     */
    public function testGetFilePath()
    {
        $this->assertEquals(realpath($this->filepath), $this->writer->getFilePath());
    }

    /**
     *
     */
    public function testDelimiter()
    {
        $this->assertEquals($this->separator, $this->writer->getDelimiter());
    }

    /**
     * in $this->setUp() we did not specify the enclosure character, so
     * we expect it to be the default which is <code>"</code>
     */
    public function testEnclosure()
    {
        $this->assertEquals('"', $this->writer->getEnclosure());
    }

    /**
     * in $this->setUp() we did not specify the escape character, so
     * we expect it to be the default which is <code>\</code>
     */
    public function testEscape()
    {
        $this->assertEquals('\\', $this->writer->getEscape());
    }

    /**
     * Test write to file and the read from that file
     */
    public function testIO()
    {
        $data = [1,2,'a'];
        $this->writer->printLine($data);

        $reader = new CsvReader($this->writer->getFilePath(), $this->separator);
        $line = $reader->getLine();
        $this->assertEquals($data, $line);

        $this->assertNull($reader->getLine());

        $reader->close();
    }

}
