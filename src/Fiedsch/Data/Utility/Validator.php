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
     * @return bool true if the supplied email address is (syntactically) valid
     */
    public function isValidEmail($email) {

        return $email === filter_var($email, FILTER_VALIDATE_EMAIL);

    }

}