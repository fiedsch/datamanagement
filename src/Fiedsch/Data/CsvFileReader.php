<?php
/**
 * @package    Datamanagement
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
 * @version    0.0.1
 * @link       https://github.com/fiedsch/datamanagement
 */

namespace Fiedsch\Data;


class CsvFileReader extends FileReader {

    /**
     * @var string the delimiter that separates columns in the file
     */
    protected $delimiter;

    /**
     * @var string the optional characters that enclose column values that might
     * contain the delimiter as part of their value
     */
    protected $enclosure;

    /**
     * @param string $filepath
     * @param string $delimiter the delimiter that separates columns in the file
     * @param string $enclosure (optional, default value is '"') the characters that enclose column values
     */
    public function __construct($filepath, $delimiter, $enclosure = '"')
    {
        parent::__construct($filepath);
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
    }

    /**
     * @return string the delimiter that separates columns in the file
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * @return string the optional characters that enclose column values
     */
    public function getEnclosure()
    {
        return $this->enclosure;
    }
}

// str_getcsv ( string $input [, string $delimiter = "," [, string $enclosure = '"' [, string $escape = "\\" ]]] )