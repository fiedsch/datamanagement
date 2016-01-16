<?php
/**
 * @package    Datamanagement
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
 * @version    0.1.1
 * @link       https://github.com/fiedsch/datamanagement
 */

namespace Fiedsch\Data;

/**
 * Class CsvFileReader
 * @package Fiedsch\Data
 *
 * Read a CSV file line by line and return an array of data per line.
 *
 * Files are assumed to be encoded utf-8 with LF line endings.
 * @see FileReader for more information.
 */

class CsvFileReader extends FileReader {

    /**
     * @var string the delimiter that separates columns in the file.
     */
    protected $delimiter;

    /**
     * @var string the character that is used to enclose column values
     * that might contain the delimiter as part of their value.
     */
    protected $enclosure;

    /**
     * @var string the character used for escaping.
     */
    protected $escape;

    /**
     * @var array
     */
    private $header;


    /**
     * @param string $filepath
     * @param string $delimiter the delimiter that separates columns in the file.
     * @param string $enclosure (optional, default value is '"') the character that is used for
     *    enclosing column values.
     * @param string $escape (optional, default value is '\') the character used for escaping.
     *
     * For `$delimiter`, `$enclosure`, and `$escape` see also http://php.net/manual/en/function.str-getcsv.php
     *
     */
    public function __construct($filepath, $delimiter, $enclosure = '"', $escape = "\\") {
        parent::__construct($filepath);
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escape = $escape;
        $this->header = null;
    }

    /**
     * @return string the delimiter that separates columns in the file
     */
    public function getDelimiter() {
        return $this->delimiter;
    }

    /**
     * @return string the character that is used to enclose column values
     */
    public function getEnclosure() {
        return $this->enclosure;
    }

    /**
     * @return string the optional character that is used for escapeing
     */
    public function getEscape() {
        return $this->escape;
    }

    /**
     * @return array|null the data from next line of the file or null if there are no more lines
     */
    public function getLine() {
        $line = parent::getLine();
        if ($line !== null) {
            return str_getcsv($line, $this->delimiter, $this->enclosure, $this->escape);
        }
        return null;
    }

    /**
     * Read the first line of the file and use it as header (column names).
     * @throws Exception if the current line is > 0, i.e. data was already read.
     */
    public function readHeader() {
        if ($this->lineNumber > 0) {
            throw new \RuntimeException("can not read header when data was already read.");
        }

        $this->header = $this->getLine();
    }

    /**
     * @return array|null the
     */
    public function getHeader() {
        return $this->header;
    }
}
