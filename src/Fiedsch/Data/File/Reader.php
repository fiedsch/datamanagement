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
 * Class Reader
 * @package Fiedsch\Data
 *
 * Read text files line by line.
 *
 * As we are Linux centric, we assume that the following conditions always hold true:
 *
 * - line endings are LF
 *
 * - encoding is utf-8
 *
 * If this is not the case: convert input data first! iconv will be your friend.
 */

class Reader extends File
{

    const STRICT_EMPTY = true;

    const RETURN_EVERY_LINE = 1;
    const SKIP_EMPTY_LINES = 2;


    /**
     * @param string $filepath an absolute or relative path to a file. In case of a relative path
     *        the current working directory is prefixed to the path to make it absolute.
     */
    public function __construct(string $filepath)
    {
        parent::__construct($filepath, 'r');
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Read and return the next line from the file.
     *
     * @param int $mode (SKIP_EMPTY_LINES or RETURN_EVERY_LINE which is the default)
     * @return string|null the next line of the file or null if there are no more lines
     */
    public function getLine(int $mode = Reader::RETURN_EVERY_LINE): mixed
    {
        if ($this->handle) {
            $line = fgets($this->handle);
            if ($line === false) {
                $this->close();
                return null;
            }
            ++$this->lineNumber;
            if ($mode === Reader::SKIP_EMPTY_LINES && self::isEmpty($line)) {
                return self::getLine($mode);
            }
            return rtrim($line, "\r\n");
        }
        return null;
    }

    /**
     * Check whether a line is to be considered empty.
     *
     * @param string $line the line to check.
     *
     * @param bool $strict if $strict is set to true, ' ' is not considered empty.
     *
     * @return bool
     */
    public static function isEmpty(string $line, bool $strict = !self::STRICT_EMPTY): bool
    {
        if ($strict) {
            return $line === '';
        }
        return trim($line) === '';
    }

}
