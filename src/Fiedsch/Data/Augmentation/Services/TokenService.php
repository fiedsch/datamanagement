<?php

/**
 * @package    Datamanagement
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
 * @version    0.0.1
 * @link       https://github.com/fiedsch/datamanagement
 */

namespace Fiedsch\Data\Augmentation\Services;

use Pimple\Container;
use  Pimple\ServiceProviderInterface;

class TokenService implements ServiceProviderInterface {

    protected $container;

    protected $issued_tokens;

    public function __construct() {

        $this->issued_tokens = array();
    }

    /**
     * @param Container $pimple the dependency injection container.
     */
    public function register(Container $container) {

        $this->container = $container;

        if (!$this->container->offsetExists('token.length')) {
            $this->container['token.length'] = 10;
        }
        if (!$this->container->offsetExists('token.case')) {
            $this->container['token.case'] = 'uc';
        }

        $this->container['token'] = $container->protect(function () {
            return $this->getUniqueToken();
        });


    }

    /**
     * Generate a unique token.
     *
     * @return string creates a new token that has not been issued before
     */
    protected function getUniqueToken() {
        $tries = 0;
        $candidate = $this->cretateToken();
        // give up and throw an exception if it seems impossible to create a unique token
        while ($tries++ < 5 && in_array($candidate, $this->issued_tokens)) {
            $candidate = $this->cretateToken();
        }
        if (in_array($candidate, $this->issued_tokens)) {
            throw new \RuntimeException("failed to create unique token");
        }

        $this->issued_tokens[] = $candidate;

        return $candidate;
    }

    /**
     * Generate a token
     *
     * @return string a Token
     */
    protected function cretateToken() {

        // characters we want to omit in generated tokens as they can be confused
        // if someone has to type the token

        $bad_characters = array(
            'i' => 'a', // might be confused with 1
            'l' => 'b', // see 'i'
            'o' => 'c', // might be confused with 0 (zero)
            '1' => 'd', // see 'i'
            '0' => 'f', // see 'o'
            'e' => 'g', // if we use the results in Excel it might try to convert 'e123' to a number :-(
        );

        $token = sha1(rand());

        $token_length = $this->container['token.length'];
        while (strlen($token) < $token_length) {
            $token .= sha1(rand());
        }

        foreach ($bad_characters as $bad => $replacement) {
            $token = preg_replace("/$bad/", $replacement, $token);
        }

        // shorten to token.length

        $token = substr($token, 0, $this->container['token.length']);

        // convert to token.case
        $case = strtolower($this->container['token.case']);
        if (!in_array($case, array('uc', 'lc'))) {
            throw new \RuntimeException("invalid case specificationn '$case'");
        }

        switch ($case) {
            case 'lc':
                $token = strtolower($token);
                break;
            case 'uc':
                $token = strtoupper($token);
                break;
        }

        return $token;
    }

}