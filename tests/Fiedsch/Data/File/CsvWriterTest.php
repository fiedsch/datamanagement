<?php

declare(strict_types=1);

use Fiedsch\Data\File\CsvWriter;
use Fiedsch\Data\File\CsvReader;
use PHPUnit\Framework\TestCase;

class CsvWriterTest extends TestCase
{
    /**
     * @var string
     */
    protected string $filepath = 'assets/tempfile.csv';

    /**
     * @var CsvWriter
     */
    protected CsvWriter $writer;

    /**
     * @var string
     */
    protected string $separator = '|';

    /**
     * setup for all tests
     */
    protected function setUp(): void
    {
        $this->writer = new CsvWriter($this->filepath, $this->separator);
    }

    /**
     * clean up after all tests
     */
    protected function tearDown(): void
    {
        $this->writer->close();
        unlink($this->writer->getFilePath());
    }

    /**
     * test line numbering
     */
    public function testGetFilePath(): void
    {
        $this->assertEquals(realpath($this->filepath), $this->writer->getFilePath());
    }

    /**
     *
     */
    public function testDelimiter(): void
    {
        $this->assertEquals($this->separator, $this->writer->getDelimiter());
    }

    /**
     * in $this->setUp() we did not specify the enclosure character, so
     * we expect it to be the default which is <code>"</code>
     */
    public function testEnclosure(): void
    {
        $this->assertEquals('"', $this->writer->getEnclosure());
    }

    /**
     * in $this->setUp() we did not specify the escape character, so
     * we expect it to be the default which is <code>\</code>
     */
    public function testEscape(): void
    {
        $this->assertEquals('\\', $this->writer->getEscape());
    }

    /**
     * Test write to file and the read from that file
     */
    public function testIO(): void
    {
        $header = ['A','B','C'];
        $this->writer->printLine($header);
        $data = [1,2,'a'];
        $this->writer->printLine($data);

        $reader = new CsvReader($this->writer->getFilePath(), $this->separator);
        $line = $reader->getLine();
        $this->assertEquals($data, $line);

        $this->assertNull($reader->getLine());
    }

}
