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
     * @param array $data the data array that supposedly contains the value at index position Helper::SC($name).
     *
     * @param string $name the column name.
     *
     * @param bool $trim apply trim() to the value. Defaults to true as surrounding whitespace is
     * normally not significant.
     *
     * @return string|null the value or null if the column does not exist.
     */
    public static function getBySC(array $data, string $name, bool $trim = true): ?string
    {
        return self::getByIndex($data, self::SC($name), $trim);
    }

    /**
     * Access an array value at a specific index position. Does not issue a warning like
     *  directly accessing $data[$index] when $data[$index] is not set!
     *
     * @param array $data the data array that supposedly contains the value at index position $index.
     *
     * @param int $index the numerical column index.
     *
     * @param bool $trim apply trim() to the value. Defaults to true as surrounding whitespace is
     * normally not significant.
     *
     * @return string|null the value or null if the column does not exist.
     */
    public static function getByIndex(array $data, int $index, bool $trim = true): ?string
    {
        if (!isset($data[$index])) {
            return null;
        }

        return $trim ? trim($data[$index]) : $data[$index];
    }

    /**
     * Get the zero based index corresponding to the spreadsheet column (A, B, ..., Z, AA, AB, ...).
     *
     * @param string $name Name of the column, case-insensitive.
     *
     * @return int zero based index that corresponds to the `$name`
     */
    public static function SC(string $name): int
    {
        // name consists of a single letter
        if (!preg_match("/^[A-Z]+$/i", $name)) {
            throw new RuntimeException("invalid column name '$name'");
        }

        // solve longer names recursively
        if (preg_match("/^([A-Z])([A-Z]+)$/i", $name, $matches)) {
            return pow(26, strlen($matches[2])) * (self::SC($matches[1]) + 1) + self::SC($matches[2]);
        }

        return ord(strtoupper($name)) - 64 - 1;
    }

    /**
     * Map an array with numeric indices to an array with string keys
     *
     * @param array $data
     * @param array $names
     * @return array
     * @throws RuntimeException
     * @deprecated Use toNamedArray() instead
     */
    public static function setArrayKeys(array $data, array $names): array
    {
        @trigger_error("Deprecated. Use toNamedArray() instead.", E_USER_DEPRECATED);
        return self::toNamedArray($data, $names);
    }


    /**
     * Return an array with string keys generated from the input array with numerical keys
     *
     * @param array $data
     * @param array $names
     * @return array
     * @throws RuntimeException
     */
    public static function toNamedArray(array $data, array $names): array
    {
        $tmp = [];
        if (count($data) !== count($names)) {
            throw new RuntimeException("data and names lengths do not match");
        }
        $i = 0;
        foreach ($names as $name) {
            if (!isset($data[$i])) {
                throw new RuntimeException("data is not indexed form 0,1, ... Index $i is missing!");
            }
            $tmp[$name] = $data[$i];
            ++$i;
        }
        return $tmp;
    }
}