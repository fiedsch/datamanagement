<?php

use Fiedsch\Data\File\Writer;
use Fiedsch\Data\File\Reader;
use PHPUnit\Framework\TestCase;

class WriterTest extends TestCase
{
    /**
     * @var string
     */
    protected $filepath = 'tests/assets/tempfile.txt';

    /**
     * @var Writer
     */
    protected $writer;

    /**
     * setup for all tests
     */
    protected function setUp()
    {
        $this->writer = new Writer($this->filepath);
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
     * Test write to file and the read from that file
     */
    public function testIO()
    {
        $text = "a Text";
        $this->writer->printLine($text);

        $reader = new Reader($this->writer->getFilePath());
        $line = $reader->getLine();
        $this->assertEquals($text, $line);

        $this->assertNull($reader->getLine());

        $reader->close();
    }

}
