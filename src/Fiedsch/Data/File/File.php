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
 * Class File
 * @package Fiedsch\Data
 *
 * Basic functionality for files (read and write).
 *
 * As we are Linux centric, we assume that the following conditions always hold true:
 *
 * - line endings are LF
 *
 * - encoding is utf-8
 *
 * If this is not the case: convert input data first! iconv will be your friend.
 */

class File
{

    /**
     * @var string the absolute path of the file we are working on
     */
    protected $filepath;

    /**
     * @var int number of the most recently read or writtenline of the file.
     */
    protected $lineNumber;

    /**
     * @var resource
     */
    protected $handle;

    /**
     * @param string $filepath an absolute or relative path to a file. In case of a relative path
     *        the current working directory is prefixed to the path to make it absolute.
     *
     * @param string $mode the file mode (see PHPs fopen() $mode parameter;
     *   http://php.net/manual/de/function.fopen.php)
     */
    public function __construct($filepath, $mode)
    {

        $realpath = $filepath;

        // is $filepath a relative path? We are considering unix like systems only
        // and ignore things like "C:\".
        if (substr($filepath, 0, 1) !== DIRECTORY_SEPARATOR) {
            $realpath = realpath(null) . DIRECTORY_SEPARATOR . $filepath;
        }

        if (!$realpath) {
            throw new \RuntimeException("failed constructing the path to file '$filepath'.");
        }

        if ($mode === 'r' && !file_exists($realpath)) {
            throw new \RuntimeException("file '$filepath' does not exist. checked '$realpath'.");
        }

        if (is_dir($realpath)) {
            throw new \RuntimeException("'$realpath' is a directory.");
        }

        $this->filepath = $realpath;

        if (!in_array($mode, ['r', 'w', 'a', 'x', 'c'])) {
            throw new \RuntimeException("invalid mode '$mode' for file.");
        }

        $this->handle = fopen($this->filepath, $mode);

        if (!$this->handle) {
            throw new\RuntimeException("invalid file handle.");
        }

    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Close the file.
     */
    public function close()
    {
        if ($this->handle && get_resource_type($this->handle) === 'file') {
            fclose($this->handle);
        }
    }

    /**
     * Access the file path.
     *
     * @return string return the absolute path for the file.
     */
    public function getFilePath()
    {
        return $this->filepath;
    }

    /**
     * The number of the most recently read or written line.
     *
     * @return int the number of the most recently read or written line.
     */
    public function getLineNumber()
    {
        return $this->lineNumber;
    }


}