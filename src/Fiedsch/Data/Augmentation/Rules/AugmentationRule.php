<?php

/**
 * @package    Datamanagement
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
 * @link       https://github.com/fiedsch/datamanagement
 *
 * Marker interface
 * Objects implementing __invoke can be used as callables (since PHP 5.3)
 * see Augmentor::augment() and Augmentor::addRule()
 */

namespace Fiedsch\Data\Augmentation\Rules;

use Fiedsch\Data\Augmentation\Augmentor;

interface AugmentationRule
{
    /**
     * @param Augmentor $augmentor
     * @param array $data
     *
     * @return array
     */
    public function __invoke(Augmentor $augmentor, array $data): array;
}
