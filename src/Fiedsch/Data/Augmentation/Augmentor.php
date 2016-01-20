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

    const PREFIX_GLOBAL_ARRAY = '_global_array.';

    const PREFIX_RULE = 'rule.';

    const KEY_DATA = 'data';

    const KEY_AUGMENTED = 'augmented';

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
     * @return array the augmented data.
     */
    public function augment($data) {
        // initialize augmented data
        $augmented = array();
        $this[self::KEY_AUGMENTED] = $augmented;
        $rulekeys = array_filter($this->keys(), function($key) { return strpos($key, self::PREFIX_RULE) === 0; });
        foreach ($rulekeys as $rulename) {
            $augmented = array_merge($augmented, $this[$rulename]($this, $data));
            $this[self::KEY_AUGMENTED] = $augmented; // make the augmented data so far available to the next rule
        }
        return array(
            self::KEY_DATA      => $data,
            self::KEY_AUGMENTED => $augmented,
        );
    }


    /**
     * Helper/Hack. Needed, as
     * ```
     * $this[$name][] = $value;
     * ```
     * won't work.
     *
     * Plus: avoid accessing a (previously) not set property that would throw an exception.
     *
     * @param string $name
     * @param string $value
     */
    public function appendToGlobal($name, $value) {
        $key = self::PREFIX_GLOBAL_ARRAY.$name;
        if (!$this->offsetExists($key)) {
            $this[$key] = array();
        }
        $temp = $this[$key];
        $temp[] = $value;
        $this[$key] = $temp;
    }

    /**
     * @param string $name key for the globally stored array value
     *
     * @return array|mixed
     */
    public function getGlobal($name) {
        $key = self::PREFIX_GLOBAL_ARRAY.$name;
        if (!$this->offsetExists($key)) { return array(); }
        return $this[$key];
    }

    /**
     * Access the data that has been augmented so far in the previous augmentation steps.
     *
     * @return array the augmented data so far
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
     */
    public static function rule($name) {
        return self::PREFIX_RULE.$name;
    }

}
