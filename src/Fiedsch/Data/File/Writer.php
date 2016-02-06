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
 * Class Writer
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

class Writer extends File
{

    /**
     * @param string $filepath an absolute or relative path to a file. In case of a relative path
     *        the current working directory is prefixed to the path to make it absolute.
     *
     * @param string $mode the file mode (see PHPs fopen() $mode parameter;
     *   http://php.net/manual/de/function.fopen.php)
     */
    public function __construct($filepath, $mode = 'w')
    {
        parent::__construct($filepath, $mode);
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Write to the file.
     *
     * @param string|null the line to be written to the file or null to write an empty line to the file.
     *  Note that a newline char is automatically appended!
     */
    public function printLine($line)
    {
        if (!$this->handle) {
            throw new \RuntimeException('can not write to file: invalid file handle');
        }
        fwrite($this->handle, $line."\n");
    }

}