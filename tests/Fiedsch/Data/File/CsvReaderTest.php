<?php

declare(strict_types=1);

use Fiedsch\Data\File\Reader;
use Fiedsch\Data\File\CsvReader;
use PHPUnit\Framework\TestCase;


class CsvReaderTest extends TestCase
{
    protected string $filepath = 'assets/data.csv';

    protected string $separator = ';';

    protected CsvReader $reader;

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
     * @noinspection PhpUnusedLocalVariableInspection
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
     * @noinspection PhpUnusedLocalVariableInspection
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
     * @noinspection PhpUnusedLocalVariableInspection
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

    public function testReadAllLines(): void
    {
        $recordsHandled = 0;
        while (($this->reader->getLine(Reader::RETURN_EVERY_LINE)) !== null) {
            ++$recordsHandled;
        }
        $this->assertSame(4, $recordsHandled);
        $this->assertSame(4, $this->reader->getLineNumber());
    }

    public function testGetMappedLine(): void
    {
        $this->assertEquals(
            ['one'=>1, 'two'=>2, 'three'=>3, 'four'=>4, 'five'=>5, 'six'=>6],
            CsvReader::getMappedLine(['one', 'two', 'three', 'four', 'five', 'six'], [1,2,3,4,5,6])
        );

        $this->expectException(\RuntimeException::class);
        $this->assertEquals(
            ['one'=>1, 'two'=>2, 'three'=>3, 'four'=>4, 'five'=>5, 'six'=>6],
            CsvReader::getMappedLine(['one', 'two', 'three', 'four', 'five', 'six'], [1,2,3,4,5/*,6*/])
        );
    }
}
