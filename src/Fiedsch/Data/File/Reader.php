<?php

/**
 * @package    Datamanagement
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
 * @version    0.1.1
 * @link       https://github.com/fiedsch/datamanagement
 */

namespace Fiedsch\Data\File;

/**
 * Class FileReader
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

class Reader {

    const STRICT_EMPTY = true;

    /**
     * @var string the absolute path of the file we are working on
     */
    protected $filepath;

    /**
     * @var int the most recently read line of the file.
     */
    protected $lineNumber;

    /**
     * @param string $filepath an absolute or relative path to a file. In case of a relative path
     *        the current working directory is prefixed to the path to make it absolute.
     */
    public function __construct($filepath) {

        $realpath = $filepath;

        // is $filepath a relative path? We are considering unix like systems only
        // and ignore things like "C:\".
        if (substr($filepath, 0, 1) !== DIRECTORY_SEPARATOR) {
            $realpath = realpath(null) . DIRECTORY_SEPARATOR . $filepath;
        }

        if (!$realpath) {
            throw new \RuntimeException("file '$filepath' does not exist. checked '$realpath'.");
        }

        if (!file_exists($realpath)) {
            throw new \RuntimeException("file '$filepath' does not exist. checked '$realpath'.");
        }

        if (is_dir($realpath)) {
            throw new \RuntimeException("'$realpath' is a directory.");
        }

        $this->filepath = $realpath;

        $this->handle = fopen($this->filepath, "r");

        if (!$this->handle) {
            throw new\RuntimeException("invalid file handle.");
        }

        $this->lineNumber = 0;

    }

    /**
     * Destructor
     */
    public function __destruct() {
        $this->close();
    }

    /**
     * Access the file path.
     *
     * @return string return the absolute path for the file.
     */
    public function getFilePath() {
        return $this->filepath;
    }

    /**
     * Read and return the next line from the file.
     *
     * @return string|null the next line of the file or null if there are no more lines
     */
    public function getLine() {
        if ($this->handle) {
            $line = fgets($this->handle);
            if ($line === false) {
                $this->close();
                return null;
            }
            ++$this->lineNumber;
            return rtrim($line, "\r\n");
        }
        return null;
    }

    /**
     * The number of the most recently read line.
     *
     * @return int the number of the most recently read line.
     */
    public function getLineNumber() {
        return $this->lineNumber;
    }

    /**
     * Close the file.
     */
    public function close() {
        if ($this->handle && get_resource_type($this->handle) === 'file') {
            fclose($this->handle);
        }
    }

    /**
     * Check whether a line is to be considered empty.
     *
     * @param string $line the line to check.
     *
     * @param boolean $strict if $strict is set to true, ' ' is not considered empty.
     */
    // NOTE to self: this function is not static as child classes such as CsvFileReader
    // need to access class properties as e.g. the delimiter.
    public function isEmpty($line, $strict = false) {
        if ($strict) {
            return $line === '';
        }
        return trim($line) === '';
    }

}