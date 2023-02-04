<?php

declare(strict_types=1);

use Fiedsch\Data\File\Writer;
use Fiedsch\Data\File\Reader;
use PHPUnit\Framework\TestCase;

class WriterTest extends TestCase
{
    protected string $filepath = 'assets/tempfile.txt';
    protected Writer $writer;

    /**
     * setup for all tests
     */
    protected function setUp(): void
    {
        $this->writer = new Writer($this->filepath);
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
     * Test write to file and the read from that file
     */
    public function testIO(): void
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
