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
     * @param array $data
     *
     * @return array
     */
    public function __invoke(Augmentor $augmentor, $data = null)
    {
        return ['token' => $augmentor['token']->getUniqueToken()];
    }
}
