<?php
// use Fiedsch\Data\File\Reader;
use Fiedsch\Data\File\FixedWidthReader;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Assert;

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
    protected function setUp()
    {
        $this->reader = new FixedWidthReader($this->filepath, $this->fields);
    }

    /**
     * clean up after all tests
     */
    protected function tearDown()
    {
        $this->reader->close();
    }

    public function testGetLines()
    {
        $i = 0;
        while (($data = $this->reader->getLine(FixedWidthReader::SKIP_EMPTY_LINES)) !== null) {
            if ($i++ == 0) {
                Assert::assertEquals('01234',    $data[0]);
                Assert::assertEquals('56789012', $data[1]);
                Assert::assertEquals('345',       $data[2]);
            }
        }
        Assert::assertEquals(5, $i);
        Assert::assertEquals(6, $this->reader->getLineNumber());
    }

    public function testShortLinesLines()
    {
        $lastLine = null;
        while (($data = $this->reader->getLine(FixedWidthReader::SKIP_EMPTY_LINES)) !== null) {
            $lastLine = $data;
        }
        Assert::assertEquals(['last','',''], $lastLine);
    }

    public function testInvalidConfigNull()
    {
        $this->expectException(\TypeError::class);
        $this->reader = new FixedWidthReader($this->filepath, null);
    }

    public function testInvalidConfigEmpty()
    {
        $this->expectException(\RuntimeException::class);
        $this->reader = new FixedWidthReader($this->filepath, []);
    }

    public function testInvalidConfigMissingTo()
    {
        $this->expectException(\RuntimeException::class);
        $this->reader = new FixedWidthReader($this->filepath, ['from'=>1]);
    }

    public function testInvalidConfigNegativeFrom()
    {
        $this->expectException(\RuntimeException::class);
        $this->reader = new FixedWidthReader($this->filepath, ['from'=>-1]);
    }

    public function testInvalidConfigNegativeTo()
    {
        $this->expectException(\RuntimeException::class);
        $this->reader = new FixedWidthReader($this->filepath, ['from'=>1, 'to'=>-1]);
    }

    public function testInvalidConfigToEqFrom()
    {
        $this->expectException(\RuntimeException::class);
        $this->reader = new FixedWidthReader($this->filepath, ['from'=>3, 'to'=>3]);
    }

    public function testInvalidConfigToNotGtFrom()
    {
        $this->expectException(\RuntimeException::class);
        $this->reader = new FixedWidthReader($this->filepath, ['from'=>3, 'to'=>-2]);
    }

}

