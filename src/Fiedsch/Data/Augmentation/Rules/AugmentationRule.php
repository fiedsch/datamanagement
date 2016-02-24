<?php

namespace Fiedsch\Data\Augmentation\Rules;

use Fiedsch\Data\Augmentation\Augmentor;

interface AugmentationRule
{
    /**
     * @param Augmentor $augmentor
     *
     * @return array
     */
    public function __invoke(Augmentor $augmentor);
}
