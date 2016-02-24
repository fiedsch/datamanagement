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
 * @package Fiedsch\Data\Utility
 */
class QuotaCell
{

    /**
     * @var int
     */
    protected $counts;

    /**
     * @var array
     */
    protected $targets;

    /**
     * @param array|int $target
     */
    public function __construct($target)
    {
        $this->targets = is_array($target) ? $target : [ $target ];
        $this->counts  = array_map(function() { return 0; }, $this->targets);

    }

    /**
     * Try to change (typically increment) the counter and return
     * true if successfull (i.e. adding $amount doesnot reach the
     * quota).
     *
     * @param int $amount the amount to add
     * @param mixed $key the key in case of multidimensional targets
     * @param boolean $force set to true if $amount shall always added regardless of exceeding the quota
     *
     * @return boolean
     */
    public function add($amount, $key = 0, $force = false)
    {
        if ($force || $this->canAdd($amount, $key)) {
            $this->counts[$key] += $amount;
            return true;
        }
        return false;
    }

    /**
     * @param mixed $key
     *
     * @return int
     * @throws \RuntimeException
     */
    public function getCount($key = 0)
    {
        if (!isset($this->targets[$key])) {
            throw new \RuntimeException("undefined key '$key'");
        }
        return $this->counts[$key];
    }

    /**
     *
     * @return int
     */
    public function getCounts()
    {
        return $this->counts;
    }

    /**
     * Did we already reach the quota? (In case of multidimensional
     * cells: reach the quota of the specified cell).
     *
     * @param mixed $key
     *
     * @return boolean
     * @throws \RuntimeException
     */
    public function isFull($key = 0)
    {
        if (!isset($this->targets[$key])) {
            throw new \RuntimeException("undefined key '$key'");
        }
        return $this->counts[$key] >= $this->targets[$key];
    }

    /**
     * Could we add $amount without exceeding the quota?
     *
     * @param mixed $key
     * @param int $amount
     *
     * @return boolean
     * @throws \RuntimeException
     */
    public function canAdd($amount, $key = 0)
    {
        if (!isset($this->targets[$key])) {
            throw new \RuntimeException("undefined key '$key'");
        }
        return $this->counts[$key] + $amount <= $this->targets[$key];
    }

}