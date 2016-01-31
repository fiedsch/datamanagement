<?php
/**
 * @package    Datamanagement
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
 * @version    0.0.1
 * @link       https://github.com/fiedsch/datamanagement
 */

namespace Fiedsch\Data\Utility;

class Validator {

    /**
     * @param string $email the email address to validate
     *
     * @return string '1' (true) if the supplied email address is (syntactically) valid
     *  '0' (false) otherwise. (string instead of boolean as the result will be written
     *   to as new data file and false would result in '').
     */
    public function isValidEmail($email) {

        return $email === filter_var($email, FILTER_VALIDATE_EMAIL) ? '1' : '0';

    }

}