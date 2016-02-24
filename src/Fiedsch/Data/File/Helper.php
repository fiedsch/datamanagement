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
 * Class Helper
 * @package Fiedsch\Data
 *
 * A variety of helper functions that might be useful when working with data files.
 *
 * TODO: should we rename SC() and getBySC() to Sc() and getBySc()?
 */

class Helper
{

    /**
     * Access an array value at a specific index position specified by its name (cf. Helper::SC()).
     * See also Helper::getByIndex().
     *
     * @param string $name the column name.
     *
     * @param array|null $data the data array that supposedly contains the value at index position Helper::SC($name).
     *
     * @param boolean $trim apply trim() to the value. Defaults to true as surrounding whitespace is
     * normally not significant.
     *
     * @return string|null the value or null if the column does not exist.
     */
    public static function getBySC($data, $name, $trim = true)
    {
        return self::getByIndex($data, self::SC($name), $trim);
    }

    /**
     * Access an array value at a specific index position. Does not issue a warning like
     *  directly accessing $data[$index] when $data[$index] is not set!
     *
     * @param int $index the numerical column index.
     *
     * @param array|null $data the data array that supposedly contains the value at index position $index.
     *
     * @param boolean $trim apply trim() to the value. Defaults to true as surrounding whitespace is
     * normally not significant.
     *
     * @return string|null the value or null if the column does not exist.
     */
    public static function getByIndex($data, $index, $trim = true)
    {
        if (null === $data) {
            return null;
        }
        if (!is_array($data)) {
            return null;
        }
        if (!isset($data[$index])) {
            return null;
        }

        return $trim ? trim($data[$index]) : $data[$index];
    }

    /**
     * Get the zero based index corresponding to the spreadsheet column (A, B, ..., Z, AA, AB, ...).
     *
     * @param string $name Name of the column, case insensitive.
     *
     * @return int|number zero based index that corresponds to the `$name`
     */
    public static function SC($name)
    {
        // name consists of a single letter
        if (!preg_match("/^[A-Z]+$/i", $name)) {
            throw new \RuntimeException("invalid column name '$name'");
        }

        // solve longer names recursively
        if (preg_match("/^([A-Z])([A-Z]+)$/i", $name, $matches)) {
            return pow(26, strlen($matches[2])) * (self::SC($matches[1]) + 1) + self::SC($matches[2]);
        }

        return ord(strtoupper($name)) - 64 - 1;
    }

}