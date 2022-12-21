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

use League\Csv\Reader as LeagueCsvReader;
use League\Csv\Statement;

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

    /**
     * @var int
     */
    protected $lineNumber;

    /**
     * @var LeagueCsvReader
     */
    protected $csvReader;

    /**
     * @var \Iterator
     */
    protected $csvRecordsIterator;

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
     */
    public function __construct(string $filepath, string $delimiter, string $enclosure = '"', string $escape = "\\")
    {
        $this->csvReader = LeagueCsvReader::createFromPath($filepath, 'r');
        $this->csvReader->setDelimiter($delimiter);
        $this->csvReader->setEnclosure($enclosure);
        $this->csvReader->setEscape($escape);
        $this->csvReader->setHeaderOffset(0);
        $result = Statement::create()->process($this->csvReader);
        $this->csvRecordsIterator = $result->getRecords();
        $this->header = $this->csvReader->getHeader();
        $this->lineNumber = 0;
    }

    /**
     * Access the delimiter.
     *
     * @return string the delimiter that separates columns in the file.
     */
    public function getDelimiter()
    {
        return $this->csvReader->getDelimiter();
    }

    /**
     * Access the enclosure.
     *
     * @return string the character that is used to enclose column values.
     */
    public function getEnclosure()
    {
        return $this->csvReader->getEnclosure();
    }

    /**
     * Access the escpe.
     *
     * @return string the optional character that is used for escapeing.
     */
    public function getEscape()
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
     */
    public function readHeader()
    {
        @trigger_error('There is no need to call this method anymore as League\Csv\Reader automatically scans the header', \E_USER_DEPRECATED);
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
    public function getLine($mode = self::RETURN_EVERY_LINE)
    {
        $row = $this->csvRecordsIterator->current();
        if (null === $row) {
            return $row;
        }
        $this->csvRecordsIterator->next();
        $this->lineNumber++;
        if ($mode === self::SKIP_EMPTY_LINES && self::isEmpty($row, false)) {
            return $this->getLine($mode);
        }

        return array_values($row);
    }

    /**
     * Access the previously read header.
     *
     * @return array|null the file's header (first row).
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Check whether a line is to be considered empty.
     *
     * @param array $line the line to check.
     *
     * @param boolean $strict controls how to compare "empty" strings (i.e. is ' ' empty or not).
     *
     * @return boolean
     */
    public static function isEmpty(array $line, $strict = false)
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
