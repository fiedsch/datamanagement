<?php

/**
 * @package    Datamanagement
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
 * @link       https://github.com/fiedsch/datamanagement
 */

namespace Fiedsch\Data\Utility;


class UniquenessChecker
{

    const NO_KEY = "_no_key_";

    protected $data;

    public function __construct()
    {
        $this->data = [];
    }

    /**
     * Check whether the value has been seen (checked) before.
     *
     * @param string $value the value to check
     *
     * @param string $category the category to which the $value belongs
     *
     * @param boolean $strict if set to true string comparisons will be case sensitive
     *
     * @return string '0' or '1' indicating false or true respectively.
     *   (string instead of boolean as the result will be written
     *   to a new data file and false would result in '').
     */
    public function isNew($value, $category = self::NO_KEY, $strict = false)
    {

        $result = '1';

        $value = $strict ? $value : strtolower($value);

        if (!isset($this->data[$category]) || !is_array($this->data[$category])) {
            $this->data[$category] = [];
        }

        if (array_key_exists($value, $this->data[$category])) {
            $result = '0';
        }

        if (!isset($this->data[$category][$value])) {
            $this->data[$category][$value] = 1;
        } else {
            $this->data[$category][$value]++;
        }

        return $result;
    }

    /**
     * Return the entries that were duplicates. The result's keys are the
     *  categories from isNew().
     *
     * @return array the duplicates found while calling isNew()
     */
    public function getDuplicates()
    {
        $result = [];
        foreach ($this->data as $key => $data) {
            $result[$key] = array_filter($data, function ($v /*, $k*/) {
                return $v > 1;
            }, ARRAY_FILTER_USE_BOTH);

        }
        return $result;
    }
}