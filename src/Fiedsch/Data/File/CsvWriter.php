<?php
/**
 * @package    Datamanagement
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
 * @link       https://github.com/fiedsch/datamanagement
 */

namespace Fiedsch\Data\File;

/**
 * Class CsvWriter
 * @package Fiedsch\Data
 *
 * Read a CSV file line by line and return an array of data per line.
 *
 * Files are assumed to be encoded utf-8 with LF line endings.
 * @see FileReader for more information.
 */

class CsvWriter extends Writer
{

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
     * @var array the file might contain a header (column names) in its first row.
     */
    private $header;


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
     * @param string $mode the file mode (see PHPs fopen() $mode parameter;
     *   http://php.net/manual/de/function.fopen.php)
     *
     * For `$delimiter`, `$enclosure`, and `$escape` see also http://php.net/manual/en/function.str-getcsv.php.
     */
    public function __construct($filepath, $delimiter, $enclosure = '"', $escape = "\\", $mode = 'w')
    {
        parent::__construct($filepath, $mode);
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escape = $escape;
        $this->header = null;
    }

    /**
     * Access the delimiter.
     *
     * @return string the delimiter that separates columns in the file.
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * Access the enclosure.
     *
     * @return string the character that is used to enclose column values.
     */
    public function getEnclosure()
    {
        return $this->enclosure;
    }

    /**
     * Access the escpe.
     *
     * @return string the optional character that is used for escapeing.
     */
    public function getEscape()
    {
        return $this->escape;
    }

    /**
     * Read and return the next line from the file.
     *
     * @param array $data
     */
    public function printLine($data)
    {
        if (!is_array($data)) {
            throw new \RuntimeException("can not write CSV data. supplied data is not an array.");
        }
        fputcsv($this->handle, $data, $this->delimiter, $this->enclosure, $this->escape);
    }

}
