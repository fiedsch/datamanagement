<?php

declare(strict_types=1);

use Fiedsch\Data\File\Reader;
use Fiedsch\Data\File\CsvReader;
use PHPUnit\Framework\TestCase;


class CsvReaderTest extends TestCase
{
    /**
     * @var string
     */
    protected $filepath = 'tests/assets/data.csv';

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
    protected function setUp(): void
    {
        $this->reader = new CsvReader($this->filepath, $this->separator);
    }

    /**
     * clean up after all tests
     */
    protected function tearDown(): void
    {
        // $this->reader->close();
    }

    /**
     * test line numbering
     */
    public function testLinenumbering(): void
    {
        $i = 0;
        $this->assertEquals($i, $this->reader->getLineNumber());
        while (($line = $this->reader->getLine(Reader::RETURN_EVERY_LINE)) !== null) {
            $this->assertEquals(++$i, $this->reader->getLineNumber());
        }
    }

    /**
     * Test skip empty lines
     */
    public function testSkipEmptyLines(): void
    {
        $i = 0;
        while (($line = $this->reader->getLine(CsvReader::SKIP_EMPTY_LINES)) !== null) {
            ++$i;
        }
        $this->assertEquals(3, $i); // drei nichtleere Zeilen gelesen
        $this->assertEquals(4, $this->reader->getLineNumber()); // inkl. Leerzeile haben wir 4 Zeilen verarbeitet
    }

    /**
     * Test do not skip empty lines (default behaviour)
     */
    public function testDoNotSkipEmptyLines(): void
    {
        $i = 0;
        while (($line = $this->reader->getLine()) !== null) {
            ++$i;
        }
        $this->assertEquals(4, $i);
        $this->assertEquals(4, $this->reader->getLineNumber());
    }

    /**
     *
     */
    public function testDelimiter(): void
    {
        $this->assertEquals($this->separator, $this->reader->getDelimiter());
    }

    /**
     * in $this->setUp() we did not specify the enclosure character, so
     * we expect it to be the default which is <code>"</code>
     */
    public function testEnclosure(): void
    {
        $this->assertEquals('"', $this->reader->getEnclosure());
    }

    /**
     * in $this->setUp() we did not specify the escape character, so
     * we expect it to be the default which is <code>\</code>
     */
    public function testEscape(): void
    {
        $this->assertEquals('\\', $this->reader->getEscape());
    }

    public function testGetHeader(): void
    {
        $this->assertSame(['id', 'name', 'age'], $this->reader->getHeader());
    }

    public function testDataWithLinebreaks(): void
    {
        $this->filepath = str_replace('.csv', '_with_linebreaks.csv', $this->filepath);
        $this->reader = new CsvReader($this->filepath, $this->separator);

        $expectedNamesInLines = [
                ['line' => 1, 'name' => "Andreas\nFieger\n"],
                ['line' => 2, 'name' => "Fiedsch"],
                ['line' => 4, 'name' => "John\nDoe"],
        ];

        $i = 0;
        while (($line = $this->reader->getLine(CsvReader::SKIP_EMPTY_LINES)) !== null) {
            $this->assertEquals($expectedNamesInLines[$i]['line'], $this->reader->getLineNumber());
            $this->assertEquals($expectedNamesInLines[$i]['line'], $line[0]);
            $this->assertEquals($expectedNamesInLines[$i]['name'], $line[1]);
            ++$i;
        }
        $this->assertEquals(3, $i); // drei nichtleere Zeilen gelesen
        $this->assertEquals(4, $this->reader->getLineNumber()); // inkl. Leerzeile haben wir 4 Zeilen verarbeitet
    }

    public function testGetReader(): void
    {
        $this->assertInstanceOf(\League\Csv\Reader::class, $this->reader->getReader());
    }

    // public function testReadHeaderIsDeprecated(): void
    // {
    //     $this->reader->readHeader();
    // }
}
