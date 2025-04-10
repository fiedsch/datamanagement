<?php

declare(strict_types=1);

/**
 * @package    Datamanagement
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
 * @link       https://github.com/fiedsch/datamanagement
 */

namespace Fiedsch\Data\File;

use League\Csv\InvalidArgument;
use League\Csv\Reader as LeagueCsvReader;
use League\Csv\Statement;
use Iterator;
use Exception;
use const E_USER_DEPRECATED;

/**
 * Class CsvReader
 * @package Fiedsch\Data
 *
 * Read a CSV file and return an array of data per data line.
 *
 * Files are assumed to be encoded utf-8 with LF line endings.
 * Files are assumed to have a header row as the first data line.
 */

class CsvReader
{
    const RETURN_EVERY_LINE = Reader::RETURN_EVERY_LINE;
    const SKIP_EMPTY_LINES = Reader::SKIP_EMPTY_LINES;

    protected int $lineNumber;

    protected LeagueCsvReader $csvReader;

    protected Iterator $csvRecordsIterator;

    public array $header;

    /**
     * Constructor.
     *
     * @param string $filepath a relative or absolute path to the file.
     *
     * @param string $delimiter the delimiter that separates columns in the file.
     *
     * @param string $enclosure (optional, default value is '"') the character that is used for
     *    enclosing column values.
     *
     * @param string $escape (optional, default value is '\') the character used for escaping.
     *
     * For `$delimiter`, `$enclosure`, and `$escape` see also http://php.net/manual/en/function.str-getcsv.php.
     *
     * @throws InvalidArgument
     * @throws Exception
     */
    public function __construct(string $filepath, string $delimiter, string $enclosure = '"', string $escape = "\\")
    {
        $this->csvReader = LeagueCsvReader::createFromPath($filepath);
        $this->csvReader->setDelimiter($delimiter);
        $this->csvReader->setEnclosure($enclosure);
        $this->csvReader->setEscape($escape);
        $this->csvReader->setHeaderOffset(0);
        $result = (new Statement())->process($this->csvReader);
        $this->csvRecordsIterator = $result->getRecords();
        $this->csvRecordsIterator->rewind(); // see https://github.com/thephpleague/csv/issues/514#issuecomment-1901071961
        $this->header = $this->csvReader->getHeader();
        $this->lineNumber = 0;
    }

    /**
     * Access the delimiter.
     *
     * @return string the delimiter that separates columns in the file.
     */
    public function getDelimiter(): string
    {
        return $this->csvReader->getDelimiter();
    }

    /**
     * Access the enclosure.
     *
     * @return string the character that is used to enclose column values.
     */
    public function getEnclosure(): string
    {
        return $this->csvReader->getEnclosure();
    }

    /**
     * Access the escape.
     *
     * @return string the optional character that is used for escaping.
     */
    public function getEscape(): string
    {
        return $this->csvReader->getEscape();
    }

    public function getReader(): LeagueCsvReader
    {
        return $this->csvReader;
    }

    /**
     * Read the first line of the file and use it as header (column names).
     *
     * @deprecated There is no need to call this method anymore as League\Csv\Reader automatically scans the header
     * @noinspection PhpUnused
     */
    public function readHeader(): void
    {
        @trigger_error('There is no need to call this method anymore as League\Csv\Reader automatically scans the header', E_USER_DEPRECATED);
    }

    /**
     * @return int Zeilennummer, die gerade gelesen wurde
     */
    public function getLineNumber(): int
    {
        return $this->lineNumber;
    }

    /**
     * Read and return the next line from the file.
     *
     * @param int $mode (SKIP_EMPTY_LINES or RETURN_EVERY_LINE which is the default)
     * @return array|null the data from next line of the file or null if there are no more lines.
     */
    public function getLine(int $mode = self::RETURN_EVERY_LINE): ?array
    {
        if (!$this->csvRecordsIterator->valid()) {
            return null;
        }
        $row = $this->csvRecordsIterator->current();
        if (null === $row) {
            return null;
        }
        $this->csvRecordsIterator->next();
        $this->lineNumber++;
        if ($mode === self::SKIP_EMPTY_LINES && self::isEmpty($row)) {
            return $this->getLine($mode);
        }

        return array_values($row);
    }

    /**
     * Access the previously read header.
     *
     * @return array|null the file's header (first row).
     */
    public function getHeader(): ?array
    {
        return $this->header;
    }

    /**
     * Check whether a line is to be considered empty.
     *
     * @param array $line the line to check.
     *
     * @param bool $strict controls how to compare "empty" strings (i.e. is ' ' empty or not).
     *
     * @return bool
     */
    public static function isEmpty(array $line, bool $strict = false): bool
    {
        $test = array_filter($line, function ($element) use ($strict) {
            if (null === $element) {
                $element = '';
            }
            if (!$strict) {
                $element = trim($element);
            }
            return '' !== $element;
        });
        return count($test) === 0;
    }

}
