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


class FileReader {

    const OPEN = 1;
    const DONE = 2;

    /**
     * @var string the absolute path of the file we are working on
     */
    private $filepath;

    /**
     * @var int status of the file. One of self::OPEN or self::CLOSE
     */
    private $status;

    private $lineNumnber;

    /**
     * @param string $filepath an absolute or relative path to a file. In case of a relative path
     *        the current working directory is prefixed to the path to make it absolute
     */
    public function __construct($filepath) {

        $realpath = $filepath;

        // is $filepath a relative path? We are considering unix like systems only
        // and ignore things like "C:\".
        if (substr($filepath, 0, 1) !== DIRECTORY_SEPARATOR)
        {
            $realpath = realpath(null) . DIRECTORY_SEPARATOR . $filepath;
        }

        if (!$realpath)
        {
            throw new \RuntimeException("file '$filepath' does not exist. checked '$realpath'.");
        }

        if (!file_exists($realpath))
        {
            throw new \RuntimeException("file '$filepath' does not exist. checked '$realpath'.");
        }

        if (is_dir($realpath))
        {
            throw new \RuntimeException("'$realpath' is a directory.");
        }

        $this->filepath = $realpath;

        $this->handle = fopen($this->filepath, "r");

        if (!$this->handle)
        {
            throw new\RuntimeException("invalid file handle.");
        }

        $this->status = self::OPEN;

        $this->lineNumnber = 0;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @return string return the absolute path for the file
     */
    public function getFilePath()
    {
        return $this->filepath;
    }

    /**
     * @return string|null thre next line of the file or null if there are no more lines
     */
    public function getLine()
    {
        if ($this->handle) {
            $line = fgets($this->handle);
            if ($line === false) {
                $this->close();
                return null;
            }
            ++$this->lineNumnber;
            return preg_replace("/\r?\n$/", '', $line);
        }
        return null;
    }

    /**
     * @return int the recently read line number
     */
    public function getLineNumber()
    {
        return $this->lineNumnber;
    }

    /**
     * close the file
     */
    public function close() {
        if ($this->handle && get_resource_type($this->handle) === 'file') {
            fclose($this->handle);
            $this->status = self::DONE;
        }
    }

}