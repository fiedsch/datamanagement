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
     * @var int
     */
    protected $counts;

    /**
     * @var array
     */
    protected $targets;

    /**
     * @var string
     */
    protected $cellPathSeparator = '.';

    /**
     * @param array|int $target
     */
    public function __construct($target)
    {
        $this->targets = is_array($target) ? $target : [$target];
        $this->targets = $this->flattenArray($this->targets);
        $this->counts = array_map(function () {
            return 0;
        }, $this->targets);

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
     * Could we add $amount without exceeding the quota?
     *
     * @param int $amount
     * @param mixed $key
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
     * Do we have a target configuration for the key?
     * @param mixed $key
     *
     * @return bool
     */
    public function hasTarget($key)
    {
        return isset($this->targets[$key]);
    }

    /**
     * Create the key for a multidimensional target entry by
     * concatinating them -- separated by whatever is stored in
     * $this->keySeparator (a '.' be default).
     *
     * @param array $nodeNames
     * @return string
     */
    public function makeArrayKey($nodeNames)
    {
        return implode($this->cellPathSeparator, $nodeNames);
    }

    /**
     * @param string $separator
     */
    public function setCellPathSeparator($separator)
    {
        $this->cellPathSeparator = $separator;
    }

    /**
     * Is the supplied array flat. I.e. are all its values scalars?
     *
     * @param array $data
     * @return bool
     */
    protected static function isFlatArray($data)
    {
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Create a flat version of the supplied data where the keys
     * are created by concatenating the original keys "of the path"
     * to the scalar value.
     *
     * Example:
     * $data = [
     *            'a' => 1,
     *            'b' => [
     *                    'x' => 'A',
     *                    'y'=> [
     *                            'B' => 1,
     *                            'C' => 2,
     *                          ]
     *                   ],
     *          ]
     * becomes
     * $data = [
     *          'a' => 1,
     *          'c.x' => 'A',
     *          'c.y.B' => 1,
     *          'c.y.C' => 2,
     *          ],
     *
     * @param array $data
     * @return array
     */
    protected function flattenArray($data)
    {
        if (self::isFlatArray($data)) {
                return $data;
        }
        $result = [];
        foreach ($data as $k => $v) {
            if (!is_array($v)) {
                $result[$k] = $v;
            } else {
                foreach ($v as $kk => $vv) {
                    $result[$this->makeArrayKey([$k,$kk])] = $vv;
                }
            }
        }
        if (!self::isFlatArray($data)) {
            return $this->flattenArray($result);
        }
        return $result;
    }

}