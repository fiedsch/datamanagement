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


class TokenCreator {

    const LOWER = 1;
    const UPPER = 2;
    const MIXED = 3;

    const DEFAULT_LENGTH = 12;

    protected $length;

    protected $case;

    protected $generated;

    public function __construct($length = self::DEFAULT_LENGTH, $case = self::UPPER) {
        if (!is_int($length) || $length < 1) {
            throw new \RuntimeException("token length must be an integer");
        }
        if (!in_array($case, [self::LOWER, self::UPPER, self::MIXED])) {
            throw new \RuntimeException(sprintf("token case must be one of %s=LOWER, %s=UPPER or %s=MIXED",
                self::LOWER, self::UPPER, self::MIXED));
        }
        $this->length = $length;
        $this->case = $case;
        $this->generated = [ ];
    }

    /**
     * Generate a unique token (meaning: this instance has never created and issued
     * this token before).
     *
     * @return string the newly created unique token
     */
    public function getUniqueToken() {
        $tries = 0;
        $candidate = $this->cretateToken();
        // give up and throw an exception if it seems impossible to create a unique token
        while ($tries++ < 5 && in_array($candidate, $this->generated)) {
            $candidate = $this->cretateToken();
        }
        if (in_array($candidate, $this->generated)) {
            throw new \RuntimeException("failed to create a new unique token");
        }

        $this->generated[] = $candidate;

        return $candidate;
    }

    /**
     * Generate a random token according to the rules (length and case)
     *
     * @return string a randomly generated token
     */
    protected function cretateToken() {

        // characters we want to omit in generated tokens as they can be confused
        // if someone has to type the token

        $bad_characters = array(
            'i' => '2', // might be confused with 1
            'l' => 'a', // see 'i'
            'o' => '3', // might be confused with 0 (zero)
            '1' => 'b', // see 'i'
            '0' => '4', // see 'o'
            'e' => 'd', // if we use the results in Excel it might try to convert 'e123' to a number :-(
        );

        $token = '';
        while (strlen($token) < $this->length) {
            $token .= sha1(rand());
        }

        // replace characters that might be confusing

        $token = str_replace(array_keys($bad_characters), array_values($bad_characters), $token);

        // shorten to length

        $token = substr($token, 0, $this->length);

        // we don't want purely numeric tokens

        if (is_numeric($token)) { $token = 'x' . substr($token, 1); }

        // convert to case

        switch ($this->case) {
            case self::LOWER:
                $token = strtolower($token);
                break;
            case self::UPPER:
                $token = strtoupper($token);
                break;
            case self::MIXED:
                // Leave as is would be all lowercase. So transform ~ 50% of the
                // letters to uppercase
                $token = join('', array_map(function($letter) {
                    if (ctype_alpha($letter) && rand(0,1) > 0.5) {
                        return strtoupper($letter);
                    }
                    return $letter;
                }, str_split($token)));
        }

        return $token;
    }

}