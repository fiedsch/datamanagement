<?php

/**
 * @package    Datamanagement
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
 * @link       https://github.com/fiedsch/datamanagement
 */

namespace Fiedsch\Data\Utility;

class VariablenameMapper
{

    /**
     * @var array
     */
    protected $lookup;

    /**
     * @var boolean
     */
    protected $throwException;

    /**
     * @param $names
     * @param $throwException when a lookup fails
     */
    public function __construct($names, $throwException = false)
    {
        $this->lookup = array_flip($names);
        $this->throwException = $throwException;
        if (count($this->lookup) != count($names)) {
            throw new \RuntimeException("supplied array of names contained invalid values");
        }
    }

    /**
     * @param string $name
     *
     * @return int index of $name in the argument passed to the constructor or -1 if
     *             $name is not found
     * @throws \RuntimeException
     */
    public function getColumnNumber($name)
    {
        if (array_key_exists($name, $this->lookup)) {
            return $this->lookup[$name];
        }
        if ($this->throwException) {
            throw new \RuntimeException("name '$name' not found");
        }
        return -1;
    }

}