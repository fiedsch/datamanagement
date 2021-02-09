<?php

declare(strict_types=1);

use Fiedsch\Data\File\FixedWidthReader;
use PHPUnit\Framework\TestCase;

class FixedWidthReaderTest extends TestCase
{
    /**
     * @var string
     */
    protected $filepath = 'tests/assets/data.fixed';

    /**
     * @var array
     */
    protected $fields = [
        ['from'=>0,'to'=>5],
        ['from'=>5,'to'=>13],
        ['from'=>13,'to'=>100],
    ];


    /**
     * @var FixedWidthReader
     */
    protected $reader;

    /**
     * setup for all tests
     */
    protected function setUp(): void
    {
        $this->reader = new FixedWidthReader($this->filepath, $this->fields);
    }

    /**
     * clean up after all tests
     */
    protected function tearDown(): void
    {
        $this->reader->close();
    }

    public function testGetLines(): void
    {
        $i = 0;
        while (($data = $this->reader->getLine(FixedWidthReader::SKIP_EMPTY_LINES)) !== null) {
            if ($i++ == 0) {
                $this->assertEquals('01234',    $data[0]);
                $this->assertEquals('56789012', $data[1]);
                $this->assertEquals('345',       $data[2]);
            }
        }
        $this->assertEquals(5, $i);
        $this->assertEquals(6, $this->reader->getLineNumber());
    }

    public function testShortLinesLines(): void
    {
        $lastLine = null;
        while (($data = $this->reader->getLine(FixedWidthReader::SKIP_EMPTY_LINES)) !== null) {
            $lastLine = $data;
        }
        $this->assertEquals(['last','',''], $lastLine);
    }

    public function testInvalidConfigNull(): void
    {
        $this->expectException(\TypeError::class);
        $this->reader = new FixedWidthReader($this->filepath, null);
    }

    public function testInvalidConfigEmpty(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->reader = new FixedWidthReader($this->filepath, []);
    }

    public function testInvalidConfigMissingTo(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->reader = new FixedWidthReader($this->filepath, ['from'=>1]);
    }

    public function testInvalidConfigNegativeFrom(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->reader = new FixedWidthReader($this->filepath, ['from'=>-1]);
    }

    public function testInvalidConfigNegativeTo(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->reader = new FixedWidthReader($this->filepath, ['from'=>1, 'to'=>-1]);
    }

    public function testInvalidConfigToEqFrom(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->reader = new FixedWidthReader($this->filepath, ['from'=>3, 'to'=>3]);
    }

    public function testInvalidConfigToNotGtFrom(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->reader = new FixedWidthReader($this->filepath, ['from'=>3, 'to'=>-2]);
    }

}

