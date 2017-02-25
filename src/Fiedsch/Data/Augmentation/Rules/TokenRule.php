<?php
/**
 * @package    Datamanagement
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
 * @link       https://github.com/fiedsch/datamanagement
 *
 * Usage example:
 * $augmentor->addRule('token', new TokenRule());
 */

namespace Fiedsch\Data\Augmentation\Rules;

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
