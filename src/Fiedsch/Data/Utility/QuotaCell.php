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
    protected array $targets;

    protected array $counts;

    /**
     * @param array $targets
     */
    public function __construct(array $targets)
    {
        $this->targets = $targets;
        $this->counts = $this->targets;
        array_walk_recursive($this->counts, function(&$value) { $value = 0; });
    }

    /**
     * Try to change (typically increment) the counter and return
     * true if successful (i.e. there is an entry for the $key and
     * adding $amount does not reach the quota limit).
     *
     * @param int $amount the amount to add
     * @param string|array $key the key.
     * @param boolean $force set to true if $amount shall always be added regardless
     *                       of exceeding the quota limit
     * @return bool true if $amount could be added, false otherwise
     */
    public function add(int $amount, string|array $key = '', bool $force = false): bool
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
     * @param string|array $key
     *
     * @return bool
     */
    public function canAdd(int $amount, string|array $key = ''): bool
    {
        return $this->getCount($key) + $amount <= $this->getTarget($key, 0);
    }

    /**
     * @param string|array $key
     * @param array $target the target array from which we try to get the value for the $key
     * @param ?string $default the value that will be returned if there is no entry for the $key
     * @return ?string
     */
    protected function getDeepArrayValue(string|array $key, array &$target, ?string $default = null): ?string
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
     * @param string|array $key
     * @param array $target the target array from which we try to get the value for the $key
     * @param int $value
     * @param bool $force create entry in array if it did not exist yet
     * @throws RuntimeException
     */
    protected static function setDeepArrayValue(string|array $key, array &$target, int $value, bool $force = false): void
    {
        if (!is_array($key)) {
            if (!isset($target[$key])) {
                if (!$force) {
                    throw new RuntimeException("no entry for key '$key''");
                }
            }
            $target[$key] = $value;
            return;
        }
        $pointer = &$target;
        for ($i = 0; $i < count($key); $i++) {
            if (!isset($pointer[$key[$i]])) {
                if (!$force) {
                    $k = implode(';', $key);
                    throw new RuntimeException("no entry for key '$k''");
                }
            }
            $pointer = &$pointer[$key[$i]];
        }
        $pointer = $value;
    }

    /**
     * @param string|array $key
     * @return int
     */
    public function getCount(string|array $key): int
    {
        return $this->getDeepArrayValue($key, $this->counts, 0);
    }

    /**
     * @param string|array $key
     * @param string|null $default value that will be returned if there is no target for $key
     * @return int|null null if target is not set
     */
    public function getTarget(string|array $key, ?string $default = null): ?int
    {
        return $this->getDeepArrayValue($key, $this->targets, $default);
    }

    /**
     * @param string|array $key
     * @param int $value
     * @param bool $force
     * @throws RuntimeException
     */
    protected function setCount(string|array $key, int $value, bool $force = false): void
    {
        self::setDeepArrayValue($key, $this->counts, $value, $force);
    }

    /**
     * @param string $key
     * @param int $value
     * @param bool $force
     * @throws RuntimeException
     */
    protected function setTarget(string $key, int $value, bool $force = false): void
    {
        self::setDeepArrayValue($key, $this->targets, $value, $force);
    }

    /**
     * @return array
     */
    public function getCounts(): array
    {
        return $this->counts;
    }

    /**
     * @return array
     */
    public function getTargets(): array
    {
        return $this->targets;
    }

    /**
     * Did we already reach the quota limit for the cell defined by the $key?
     *
     * @param string $key
     *
     * @return bool
     * @throws RuntimeException
     */
    public function isFull(string $key): bool
    {
        if (!$this->hasTarget($key)) {
            throw new RuntimeException("undefined key '$key'");
        }
        return $this->getCount($key) >= $this->getTarget($key);
    }

    /**
     * Do we have a target configuration for the key?
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasTarget(string $key): bool
    {
        return null !== $this->getTarget($key);
    }

}