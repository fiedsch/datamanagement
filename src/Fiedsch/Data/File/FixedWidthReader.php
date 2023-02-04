<?php
/**
 * @package    Datamanagement
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
 * @link       https://github.com/fiedsch/datamanagement
 */

namespace Fiedsch\Data\File;

use RuntimeException;

/**
 * Class FixedWidthReader
 * @package Fiedsch\Data
 *
 * Read a file line by line and return an array of data per line
 * that has been created by splitting the line into fixed width fields.
 *
 * Files are assumed to be encoded utf-8 with LF line endings.
 * @see FileReader for more information.
 */

class FixedWidthReader extends Reader
{

    /**
     * @var array the positions (start and end) of the fields.
     */
    protected array $fields;

    /**
     * Constructor.
     *
     * @param string $filepath a relative or absolute path to the file.
     *
     * @param array $fields the positions (start and end) of the fields to be read from each line of the file.
     */
    public function __construct(string $filepath, array $fields)
    {
        parent::__construct($filepath);
        $this->fields = $fields;
        $this->checkFieldsDefinition();
    }

    /**
     * Check the fields definition array and throw an exception if it does not make sense.
     * e.g. if we encounter negative values for 'from' or 'to' or if they are missing.
     *
     * @throws RuntimeException
     */
    protected function checkFieldsDefinition(): void
    {
        if (count($this->fields) === 0) {
            throw new RuntimeException("fields definition is empty");
        }
        foreach ($this->fields as $i => $field) {
            if (!isset($field['from'])) {
                throw new RuntimeException("'from' is missing for field '$i'");
            }
            if (!isset($field['to'])) {
                throw new RuntimeException("'to' is missing for field '$i'");
            }
            if (!is_integer($field['from'])) {
                throw new RuntimeException("'from' is not an integer (field '$i')");
            }
            if (!is_integer($field['to'])) {
                throw new RuntimeException("'to' is not an integer (field '$i')");
            }
            if ($field['from']<0) {
                throw new RuntimeException("'from' is negative (field '$i')");
            }
            if ($field['to']<0) {
                throw new RuntimeException("'to' is negative (field '$i')");
            }
            if ($field['from']>$field['to']) {
                throw new RuntimeException("'from' not less than to (field '$i')");
            }
        }
    }

    /**
     * Access the field's definition.
     *
     * @return array the definition of the fields (start and end) to be read.
     */
    public function getFields(): array
    {
        return $this->fields;
    }


    /**
     * Read and return the next line from the file.
     *
     * @param int $mode (SKIP_EMPTY_LINES or RETURN_EVERY_LINE which is the default)
     * @return array|null the data from next line of the file or null if there are no more lines.
     */
    public function getLine(int $mode = self::RETURN_EVERY_LINE): ?array
    {
        $line = parent::getLine($mode);
        if ($line === null) { return null; }
        return $this->splitLine($line);
    }

    /**
     * Split a line of data (a string) into an array based on the $fields configuration array.
     *
     * @param string $line the line of "fixed width" data
     * @return array
     */
    protected function splitLine(string $line): array
    {
        $result = [];
        foreach($this->fields as $field) {
            $result[] = mb_substr($line, $field['from'], $field['to'] - $field['from']);
        }
        return $result;
    }

}
