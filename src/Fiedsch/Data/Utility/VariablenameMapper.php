<?php

/**
 * @package    Datamanagement
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
 * @link       https://github.com/fiedsch/datamanagement
 */

namespace Fiedsch\Data\Utility;

use RuntimeException;

class VariablenameMapper
{
    protected array $lookup;

    protected bool $throwException;

    /**
     * @param array $names
     * @param bool $throwException throw an exception when a lookup fails
     */
    public function __construct(array $names, bool $throwException = false)
    {
        $this->lookup =
            array_filter(
                array_flip(
                    array_map(
                        function($element) {
                            return trim($element);
                        },
                        $names
                    )
                ),
                function($element) {
                    return trim($element) !== '';
                },
                ARRAY_FILTER_USE_KEY
            );
        $this->throwException = $throwException;
        if (count($this->lookup) != count($names)) {
            throw new RuntimeException("supplied array of names contained invalid values or duplicates.");
        }
    }

    /**
     * @param string $name
     *
     * @return int index of $name in the argument passed to the constructor or -1 if
     *             $name is not found
     * @throws RuntimeException
     */
    public function getColumnNumber(string $name): int
    {
        $name = trim($name);
        if (array_key_exists($name, $this->lookup)) {
            return $this->lookup[$name];
        }
        if ($this->throwException) {
            throw new RuntimeException("name '$name' not found");
        }
        return -1;
    }

    /**
     * @return array the mapping of column names to column indexes
     */
    public function getMapping(): array
    {
        return $this->lookup;
    }
}