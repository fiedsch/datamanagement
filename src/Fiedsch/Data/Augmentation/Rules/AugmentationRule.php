<?php

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
    public function __invoke(Augmentor $augmentor, $data = null);
}
