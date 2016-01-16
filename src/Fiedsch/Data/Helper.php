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

class Helper {

    /**
     * Spreadsheet Column Number
     *
     * @param string $name Name of column. (A, B, ..., Z, AA, AB, ...). Case insensitive!
     * @return int|number zero based index that corresponds to the `$name`
     */
    public static function SC($name)
    {

        // name consists of a single letter

        if (!preg_match("/^[A-Z]+$/i", $name))
        {
            throw new \RuntimeException("invalid column name '$name'");
        }

        // solve longer names recursively

        if (preg_match("/^([A-Z])([A-Z]+)$/i", $name, $matches))
        {
            return pow(26, strlen($matches[2])) * (self::SC($matches[1])+1) + self::SC($matches[2]);
        }

        return ord(strtoupper($name)) - 64 - 1;
    }
}