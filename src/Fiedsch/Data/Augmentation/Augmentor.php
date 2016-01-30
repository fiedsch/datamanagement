<?php

/**
 * @package    Datamanagement
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
 * @version    0.0.1
 * @link       https://github.com/fiedsch/datamanagement
 */


namespace Fiedsch\Data\Augmentation;

use Pimple\Container;

/**
 * Class Augmentor
 * @package Fiedsch\Data
 *
 * An Augmentor augments data according to specified rules.
 * The rules operate on a line by line basis (data record), i.e. only values
 * of the current line can be used to augment the line.
 */
class Augmentor extends Container {

    const PREFIX_RULE = 'rule.';

    const KEY_DATA = 'data';

    const KEY_AUGMENTED = 'augmented';

    const KEY_COLNAMES = 'required_columns';

    /**
     * Constructor
     *
     * @param array $values
     */
    public function __construct($values = array()) {
        parent::__construct($values);
    }


    /**
     * Augment data according to the rules and return the result.
     *
     * @param array $data contains the data of the current row.
     *
     * @return array the original data (left unchanged) and the augmented data.
     */
    public function augment($data) {
        // initialize
        $this[self::KEY_AUGMENTED] = array();
        // get rules
        $rulekeys = array_filter($this->keys(), function ($key) {
            return strpos($key, self::PREFIX_RULE) === 0;
        });
        // apply rules
        foreach ($rulekeys as $rulename) {
            $augmentation_step = $this[$rulename]($this, $data);
            // make the augmented data so far available to the next rule
            $this[self::KEY_AUGMENTED] = array_merge($this[self::KEY_AUGMENTED], $augmentation_step);
        }
        $this->checkAugmented();
        return array(
            self::KEY_DATA => $data,
            self::KEY_AUGMENTED => $this[self::KEY_AUGMENTED],
        );
    }

    /**
     * @param array $colnames the names of the columns that have to be set during the
     *   augmentation steps.
     */
    public function setRequiredColumns(array $colnames) {
        $this[self::KEY_COLNAMES] = $colnames;
    }

    /**
     * Check if all of the required columns (fields) have been set during the augmentaion steps.
     * Throw an exception if a column is missing.
     */
    protected function checkAugmented() {
        if ($this->offsetExists(self::KEY_COLNAMES) && is_array($this[self::KEY_COLNAMES])) {
            foreach ($this[self::KEY_COLNAMES] as $key) {
                if (!array_key_exists($key, $this[self::KEY_AUGMENTED])) {
                    throw new \RuntimeException("required field '$key' does not exist in augmented data'");
                }
            }
        }
    }

    /**
     * Access the data that has been augmented so far in the previous augmentation steps.
     *
     * @return array the augmented data so far (or an empty array, should this be called in the
     *   first augmentation step).
     */
    public function getAugmentedSoFar() {
        if (!$this->offsetExists(self::KEY_AUGMENTED)) {
            $this[self::KEY_AUGMENTED] = array();
        }
        return $this[self::KEY_AUGMENTED];

    }

    /**
     * Syntactic sugar. Use Augmentor::rule('foo') to get the proper key for the 'foo' rule
     * (identical to using Augmentor::PREFIX_RULE.$name, but hopefully easier to read).
     *
     * @param string $name the rule's name
     *
     * @return string key used to store the rule
     */
    protected static function rule($name) {
        return self::PREFIX_RULE . $name;
    }

    /**
     * Add an augmentation rule.
     *
     * @param string $name the name of the augmentation rule
     * @param callable $rule the code that will be executed
     */
    public function addRule($name, $rule) {
        $this[self::rule($name)] = $this->protect($rule);
    }

}
