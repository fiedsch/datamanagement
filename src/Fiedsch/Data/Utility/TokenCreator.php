<?php
/**
 * @package    Datamanagement
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
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


class TokenCreator
{
    const LOWER = 1;
    const UPPER = 2;
    const MIXED = 3;

    const DEFAULT_LENGTH = 12;

    /**
     * @var int
     */
    protected $length;

    /**
     * @var int
     */
    protected $case;

    /**
     * @var array
     */
    protected $generated;

    /**
     * @var array
     */
    protected $tokensReadFromFile;

    /**
     * @var array
     */
    protected $tokenChars;

    /**
     * @param int $length
     * @param int $case
     *
     * @throws \LogicException
     */
    public function __construct($length = self::DEFAULT_LENGTH, $case = self::UPPER)
    {
        if (!is_int($length) || $length < 1) {
            throw new \LogicException("token length must be a positive integer");
        }
        if (!in_array($case, [self::LOWER, self::UPPER, self::MIXED])) {
            throw new \LogicException(sprintf("token case must be one of %s=LOWER, %s=UPPER or %s=MIXED",
                self::LOWER, self::UPPER, self::MIXED));
        }
        $this->length = $length;
        $this->case = $case;
        $this->generated = [];
        $this->tokensReadFromFile = null;
        $this->initializeTokenCharacters();
    }

    /**
     * Initialize the list of characters that are used to create tokens
     */
    protected function initializeTokenCharacters()
    {
        $allowed  = 'abcdefghijklmnopqrstuvwxyz';
        $allowed .= strtoupper($allowed);
        $allowed .= '0123456789';
        //$allowed .= '!§$%&?';
        $this->tokenChars = str_split($allowed);
        shuffle($this->tokenChars);
    }

    /**
     * Generate a unique token (meaning: this instance has never created and issued
     * this token before).
     *
     * @return string the newly created unique token
     *
     * @throws \RuntimeException
     */
    public function getUniqueToken()
    {
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
     * or return the next token read from a file. Tokens read from file will
     * will be left as they are (e.g. will not be shortened if they are longer
     * than the specified length). Still length and case are checked and will
     * throw exceptions if the rules are not satisfied.
     *
     * @return string a randomly generated token
     *
     * @throws \LogicException
     */
    protected function cretateToken()
    {
        // if we have read tokens from file: use them
        if (null !== $this->tokensReadFromFile) {
            if (count($this->tokensReadFromFile) == 0) {
                throw new \LogicException("you requested more tokens than were read from file");
            }
            $token = array_shift($this->tokensReadFromFile);
            // check requirements
            if (strlen($token) < $this->length) {
                throw new \LogicException(
                    sprintf("tokens read from file are too short (current length setting is '%s').",
                        $this->length
                    ));
            }
            if ($this->case == self::LOWER && preg_match("/[A-Z]/", $token)) {
                throw new \LogicException("you requestet lowercase tokens but the token contains uppercase letters");
            }
            if ($this->case == self::UPPER && preg_match("/[a-z]/", $token)) {
                throw new \LogicException("you requestet uppercase tokens but the token contains lowercase letters");
            }
            return $token;
        }

        // characters we want to omit in generated tokens as they can be confused
        // if someone has to type the token

        $bad_characters = array(
            'i' => 'a', // 'i'  (esp. 'I') might be confused with '1' (one) or 'l' (lowercase L)
            'I' => 'b', // see 'i'
            'l' => 'c', // see 'i'
            '1' => 'd', // see 'i'
            'o' => 'f', // 'o' might be confused with '0' (zero)
            '0' => 'g', // '0' (zero), see 'o'
            'e' => 'h', // if we use the results in Excel and the like, they might try
                        // to convert '123e4' to a number :-(
        );

        // create a new token

        $token = $this->getTokenCharacters($this->length);

        // replace characters that might be confusing

        $token = str_replace(array_keys($bad_characters), array_values($bad_characters), $token);

        // shorten to length

        $token = substr($token, 0, $this->length);

        // we don't want purely numeric tokens

        if (is_numeric($token)) {
            $token = 'x' . substr($token, 1);
        }

        // convert to case

        switch ($this->case) {
            case self::LOWER:
                $token = strtolower($token);
                break;
            case self::UPPER:
                $token = strtoupper($token);
                break;
            case self::MIXED:
                // Leave as is might be all lowercase. So transform ~ 50% of the
                // letters to uppercase
                $token = join('', array_map(function ($letter) {
                    if (ctype_alpha($letter)) {
                        return rand(0, 1) > 0.5 ? strtoupper($letter) : strtolower($letter);
                    }
                    return $letter;
                }, str_split($token)));
        }

        return $token;
    }

    /**
     * Get a random character
     *
     * @param int $count number of characters to return
     *
     * @return string
     */
    protected function getTokenCharacters($count = 1)
    {
        $result = '';
        for ($i=0; $i< $count; $i++) {
            $result .= $this->tokenChars[mt_rand(0, count($this->tokenChars) - 1)];
        }
        return $result;
    }

    /**
     * @param string $filepath the path to a file containing tokens (one on every line
     *  with no header!).
     *
     * @param string $delimiter as expected by Fiedsch\Data\File\CsvReader
     */
    public function readFromFile($filepath, $delimiter = "\t")
    {
        $reader = new CsvReader($filepath, $delimiter);
        $this->tokensReadFromFile = [];
        while (($line = $reader->getLine()) !== null) {
            if (!$reader->isEmpty($line)) {
                $this->tokensReadFromFile[] = $line[0]; // we expect the token in the first column
                // the second column might contain further info such as "use this many times"
            }
        }
    }

}