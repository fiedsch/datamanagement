<?php

/**
 * @package    Datamanagement
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
 * @link       https://github.com/fiedsch/datamanagement
 */


namespace Fiedsch\Data\Augmentation;

use Pimple\Container;
use RuntimeException;

/**
 * Class Augmentor
 *
 * @package Fiedsch\Data
 *
 * An Augmentor augments data according to specified rules.
 * The rules operate on a line by line basis (data record), i.e. only values
 * of the current line can be used to augment the line.
 */
class Augmentor extends Container
{

    const PREFIX_RULE = 'rule.';

    const KEY_DATA = 'data';

    const KEY_AUGMENTED = 'augmented';

    const KEY_REQUIRED_COLNAMES = 'required_columns';

    const KEY_COLOUMN_ORDER = 'column_order';

    /**
     * Constructor
     *
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        parent::__construct($values);
    }


    /**
     * Augment data according to the rules and return the result.
     *
     * @param array $data contains the data of the current row.
     *
     * @return array the original data (left unchanged) and the augmented data.
     */
    public function augment(array $data): array
    {
        // initialize
        $this[self::KEY_AUGMENTED] = [];
        // get rules
        $rulekeys = array_filter($this->keys(), function($key) {
            return str_starts_with($key, self::PREFIX_RULE);
        });
        // apply rules
        foreach ($rulekeys as $rulename) {
            $augmentation_step = $this[$rulename]($this, $data);
            if (!is_array($augmentation_step)) {
                throw new RuntimeException("augmentation rule '$rulename' did not produce data. Make sure to return the array of augmented data.");
            }
            // make the augmented data so far available to the next rule
            $this[self::KEY_AUGMENTED] = array_merge($this[self::KEY_AUGMENTED], $augmentation_step);
        }
        $this->checkAugmented();

        if ($this->hasColumnOrderSpecification()) {
            $result = [];
            foreach ($this[self::KEY_COLOUMN_ORDER] as $key) {
                $result[$key] = $this[self::KEY_AUGMENTED][$key];
            }
            return $result;
        } else {
            return $this[self::KEY_AUGMENTED];
        }
    }

    /**
     * Check if the required columns (fields) have been set during the augmentation steps.
     * Throw an exception if a column is missing.
     * Also check if additional columns (not specified in the required columns) are present.
     * This will also throw an exception.
     *
     * @throws RuntimeException
     */
    protected function checkAugmented(): void
    {
        if ($this->hasRequiredColumnsSpecification()) {
            foreach ($this[self::KEY_REQUIRED_COLNAMES] as $key) {
                if (!array_key_exists($key, $this[self::KEY_AUGMENTED])) {
                    throw new RuntimeException("required column '$key' does not exist in augmented data'");
                }
            }
            // Additionally check if we have data for columns not specified in $this[self::KEY_REQUIRED_COLNAMES]
            $key_mismatch = array_diff(array_keys($this[self::KEY_AUGMENTED]), $this[self::KEY_REQUIRED_COLNAMES]);
            if (!empty($key_mismatch)) {
                throw new RuntimeException("found keys not specified as required field: " . json_encode(array_values($key_mismatch)));
            }
        }

        if ($this->hasColumnOrderSpecification()) {
            // Side effect(?): specifying column order has the same effect as setting required columns (see above).
            foreach ($this[self::KEY_COLOUMN_ORDER] as $key) {
                if (!array_key_exists($key, $this[self::KEY_AUGMENTED])) {
                    throw new RuntimeException("required column '$key' does not exist in augmented data'");
                }
            }
            // Check if we have data for columns not specified in $this[self::KEY_COLOUMN_ORDER]
            $key_mismatch = array_diff(array_keys($this[self::KEY_AUGMENTED]), $this[self::KEY_COLOUMN_ORDER]);
            if (!empty($key_mismatch)) {
                throw new RuntimeException("found keys not specified in column order: " . json_encode(array_values($key_mismatch)));
            }
        }

        if ($this->hasRequiredColumnsSpecification() && $this->hasColumnOrderSpecification()) {
            // check if bot specifications do not contradict
            if (array_diff($this[self::KEY_COLOUMN_ORDER], $this[self::KEY_REQUIRED_COLNAMES])) {
                throw new RuntimeException("specification mismatch required columns and column order do not match");
            }
        }
    }

    /**
     * Specify column names that have to be present (as array keys) in the augmentation result.
     *
     * @param array $colnames the names of the columns that have to be set during the augmentation steps.
     */
    public function setRequiredColumns(array $colnames): void
    {
        $this[self::KEY_REQUIRED_COLNAMES] = $colnames;
    }

    /**
     * @return array
     */
    public function getRequiredColumns(): array
    {
        return $this[self::KEY_REQUIRED_COLNAMES];
    }

    /**
     * Set the order in which the generated columns will be "output" (order of
     * the keys in the augmented data array).
     *
     * Also makes sure, all specified columns are present in the augmentation result.
     * Hence, if you use setColumnOutputOrder() you can omit setRequiredColumns().
     *
     * @param array $colnames determines the order of the column output.
     */
    public function setColumnOutputOrder(array $colnames): void
    {
        $this[self::KEY_COLOUMN_ORDER] = $colnames;
    }

    public function getColumnOutputOrder(): array
    {
        return $this[self::KEY_COLOUMN_ORDER];
    }

    /**
     * Do we have the required column names set in $this[self::KEY_COLNAMES]?
     *
     * @return boolean
     */
    public function hasRequiredColumnsSpecification(): bool
    {
        return $this->offsetExists(self::KEY_REQUIRED_COLNAMES) && is_array($this[self::KEY_REQUIRED_COLNAMES]);
    }

    /**
     * Do we have the specification for the order in which the generated columns have to be output?
     *
     * @return bool
     */
    public function hasColumnOrderSpecification(): bool
    {
        return $this->offsetExists(self::KEY_COLOUMN_ORDER) && is_array($this[self::KEY_COLOUMN_ORDER]);
    }

    /**
     * Access the data that has been augmented so far in the previous augmentation steps.
     *
     * @return array the augmented data so far (or an empty array, should this be called in the
     *   first augmentation step).
     */
    public function getAugmentedSoFar(): array
    {
        if (!$this->offsetExists(self::KEY_AUGMENTED)) {
            $this[self::KEY_AUGMENTED] = [];
        }
        return $this[self::KEY_AUGMENTED];
    }

    /**
     * Add an augmentation rule.
     *
     * @param string $name the name of the augmentation rule
     * @param callable $rule the code that will be executed
     * @throws RuntimeException
     */
    public function addRule(string $name, callable $rule): void
    {
        if (isset($this[self::rule($name)])) {
            throw new RuntimeException("rule '$name' already exists'");
        }
        $this[self::rule($name)] = $this->protect($rule);
    }

    /**
     * Syntactic sugar. Use Augmentor::rule('foo') to get the proper key for the 'foo' rule
     * (identical to using Augmentor::PREFIX_RULE.$name, but hopefully easier to read).
     *
     * @param string $name the rule's name
     *
     * @return string key used to store the rule
     */
    protected static function rule(string $name): string
    {
        return self::PREFIX_RULE . $name;
    }

    /**
     * Append to an already stored array.
     *
     * @param string $key the key under which we have previously stored data.
     * @param mixed $value the value which we want to append to the existing data.
     *
     * Note, that the following will not work:
     * <code>
     * $container['foo'] = array('bar','baz');
     * $container['foo'][] = 42;
     * </code>
     *
     * See also: https://github.com/silexphp/Pimple/issues/149
     * <quote>
     * fabpot commented on Jul 15, 2014
     * To be more precise, Pimple stores parameters, but it should have
     * no knowledge of the parameter value; Pimple just stores what you give it.
     * </quote>
     */
    public function appendTo(string $key, mixed $value): void
    {
        if (!$this->offsetExists($key)) {
            $this[$key] = [$value];
            return;
        }
        $old_value = $this[$key];
        if (!is_array($old_value)) {
            $old_value = [$old_value];
        }
        if (is_array($value)) {
            $old_value = array_merge($old_value, $value);
        } else {
            $old_value[] = $value;
        }
        $this[$key] = $old_value;
    }

    /**
     * Overwrite a previously augmented value
     *
     * @param $key
     * @param $value
     */
    public function overwriteValue($key, $value): void
    {
        $augmented = $this[self::KEY_AUGMENTED];
        $augmented[$key] = $value;
        $this[self::KEY_AUGMENTED] = $augmented;
    }

}
