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

use Fiedsch\Data\File\CsvReader;

/*
 * FIXME: in case of self::MIXED case tokens make sure we will note generate
 * 'Abc1' and 'aBc1' which are technically two destinct tokens but look the
 * same to humans (or software that does its comparisons case insensitive).
 *
 * If we use all LOWER or all UPPER tokens this can not happen.
 * So maybe MIXED is not a good idea and should be removed?
 */


class TokenCreator {

    const LOWER = 1;
    const UPPER = 2;
    const MIXED = 3;

    const DEFAULT_LENGTH = 12;

    protected $length;

    protected $case;

    protected $generated;

    protected $readFromFile;

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
        $this->readFromFile = null;
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

        // if we have read tokens from file, use them
        if (null !== $this->readFromFile) {
            if (count($this->readFromFile) == 0) {
                throw new \LogicException("you requested more tokens than were read from file");
            }
            $token = array_shift($this->readFromFile);
            // check requirements
            if (strlen($token) < $this->length) {
                throw new \LogicException(
                    sprintf("tokens read from file are too short (current length setting is '%s').",
                        $this->length
                    ));
            }
            if ($this->case == self::LOWER && preg_match("/[A-Z]/", $token)) {
                throw new \LogicException("you requestet LOWERcase tokens but the file contsins uppercase letters");
            }
            if ($this->case == self::UPPER && preg_match("/[a-z]/", $token)) {
                throw new \LogicException("you requestet UPPERcase tokens but the file contsins lowercase letters");
            }
            return $token;
        }

        // characters we want to omit in generated tokens as they can be confused
        // if someone has to type the token

        $bad_characters = array(
            'i' => '2', // 'i'  (esp. 'I') might be confused with '1' (one) or 'l' (lowercase L)
            'l' => 'a', // see 'i'
            '1' => '3', // see 'i'
            'o' => 'b', // 'o' might be confused with '0' (zero)
            '0' => '4', // see 'o'
            'e' => 'c', // if we use the results in Excel and the like, they might try
                        // to convert '123e4' to a number :-(
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

    /**
     * @param string $filepath the path to a file containing tokens (one on every line
     *  with no header!).
     *
     * @param string $delimiter as expected by Fiedsch\Data\File\CsvReader
     */
    public function readFromFile($filepath, $delimiter = "\t") {
        $reader = new CsvReader($filepath, $delimiter);
        $result = [ ];
        while (($line = $reader->getLine()) !== null) {
            if (!$reader->isEmpty($line)) {
                $result[] = $line[0]; // we expect the token in the first column
                // the second column might contain further info such as "use this many times"
            }
        }

        $this->readFromFile = $result;
    }

}