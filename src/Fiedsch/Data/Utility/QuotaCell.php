<?php
/**
 * @package    Datamanagement
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
 * @link       https://github.com/fiedsch/datamanagement
 */

namespace Fiedsch\Data\Utility;

/**
 * Class QuotaCell
 *
 * Manage quota on defined cells that can be multidimensional
 *
 * TODO: rename this class. It manages quota for various cells at at time
 * so it should rather be something like QuotaCellManager or the like.
 *
 * @package Fiedsch\Data\Utility
 */
class QuotaCell
{
    /**
     * @var array
     */
    protected $targets;

    /**
     * @var array
     */
    protected $counts;

    /**
     * @param array $targets
     */
    public function __construct(array $targets)
    {
        $this->targets = $targets;
        $this->counts = $this->targets;
        array_walk_recursive($this->counts, function(&$value, $key) { $value = 0;  });
    }

    /**
     * Try to change (typically increment) the counter and return
     * true if successfull (i.e. there is an entry for the $key and
     * adding $amount does not reach the quota limit).
     *
     * @param int $amount the amount to add
     * @param int|string|array $key the key.
     * @param boolean $force set to true if $amount shall always added regardless
     *                       of exceeding the quota limit
     * @return boolean true if $amount could be added, false otherwise
     */
    public function add($amount, $key = '', $force = false)
    {
        if ($force || $this->canAdd($amount, $key)) {
            $this->setCount($key, $this->getCount($key) + $amount, true);
            return true;
        }
        return false;
    }

    /**
     * Could we add($amount, $key) without exceeding the quota?
     *
     * @param int $amount
     * @param int|string|array $key
     *
     * @return boolean
     */
    public function canAdd($amount, $key = '')
    {
        return $this->getCount($key) + $amount <= $this->getTarget($key, 0);
    }

    /**
     * @param int|string|array $key
     * @param array $target the target array from which we try to get the value for the $key
     * @param null|int $default the value that will be returned if there is no entry for the $key
     */
    protected function getDeepArrayValue($key, &$target, $default = null)
    {
        if (!is_array($key)) {
            if (!isset($target[$key])) {
                return $default;
            }
            return $target[$key];
        }
        $pointer = &$target;
        for ($i = 0; $i < count($key); $i++) {
            if (!isset($pointer[$key[$i]])) {
                return $default;
            }
            $pointer = &$pointer[$key[$i]];
        }
        return $pointer;
    }

    /**
     * @param int|string|array $key
     * @param array $target the target array from which we try to get the value for the $key
     * @param null|int $default the value that will be returned if there is no entry for the $key
     * @param boolean $force create entry in array if it did not exist yet
     * @throws \RuntimeException
     */
    protected function setDeepArrayValue($key, &$target, $value, $force = false)
    {
        if (!is_array($key)) {
            if (!isset($target[$key])) {
                if (!$force) {
                    throw new \RuntimeException("no entry for key '$key''");
                }
            }
            $target[$key] = $value;
        }
        $pointer = &$target;
        for ($i = 0; $i < count($key); $i++) {
            if (!isset($pointer[$key[$i]])) {
                if (!$force) {
                    $k = implode(';', $key);
                    throw new \RuntimeException("no entry for key '$k''");
                }
            }
            $pointer = &$pointer[$key[$i]];
        }
        $pointer = $value;
    }

    /**
     * @param int|string|array $key
     * @return int
     */
    public function getCount($key)
    {
        return $this->getDeepArrayValue($key, $this->counts, 0);
    }

    /**
     * @param int|string|array $key
     * @param null|int $default value that will be returned if there is no target for $key
     * @return int|null null if target is not set
     */
    public function getTarget($key, $default = null)
    {
        return $this->getDeepArrayValue($key, $this->targets, $default);
    }

    /**
     * @param int|string|array $key
     * @apram boolean $force
     * @throws \RuntimeException
     */
    protected function setCount($key, $value, $force = false)
    {
        $this->setDeepArrayValue($key, $this->counts, $value, $force);
    }

    /**
     * @param int|string|array $key
     * @apram boolean $force
     * @throws \RuntimeException
     */
    protected function setTarget($key, $value, $force = false)
    {
        $this->setDeepArrayValue($key, $this->targets, $value, $force);
    }

    /**
     * @return array
     */
    public function getCounts()
    {
        return $this->counts;
    }

    /**
     * @return array
     */
    public function getTargets()
    {
        return $this->targets;
    }

    /**
     * Did we already reach the quota limit for the cell defined by the $key?
     *
     * @param int|string|array $key
     *
     * @return boolean
     * @throws \RuntimeException
     */
    public function isFull($key)
    {
        if (!$this->hasTarget($key)) {
            throw new \RuntimeException("undefined key '$key'");
        }
        return $this->getCount($key) >= $this->getTarget($key);
    }

    /**
     * Do we have a target configuration for the key?
     *
     * @param int|string|array $key
     *
     * @return bool
     */
    public function hasTarget($key)
    {
        return null !== $this->getTarget($key, null);
    }

}