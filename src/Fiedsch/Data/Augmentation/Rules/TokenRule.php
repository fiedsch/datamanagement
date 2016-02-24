<?php

namespace Fiedsch\Data\Augmentation\Rules;

/**
 * Usage example:
 *
 * $augmentor->addRule('token', new TokenRule());
 */

use Fiedsch\Data\Augmentation\Augmentor;

class TokenRule implements AugmentationRule
{
    /**
     * @param Augmentor $augmentor
     * 
     * @return array
     */
    public function __invoke(Augmentor $augmentor)
    {
        return [ 'token' => $augmentor['token']->getUniqueToken() ];
    }
}
