<?php

/**
 * @package    Datamanagement
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
 * @link       https://github.com/fiedsch/datamanagement
 */

namespace Fiedsch\Data\Augmentation\Provider;

use Fiedsch\Data\Utility\Validator;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/*
 * Note: with the current Validator there is not really a point in having
 * a service. One could simply call Validator::isValidEmail() instead.
 * We'll see if there will be real use cases.
 */

class ValidationServiceProvider implements ServiceProviderInterface
{

    /**
     * @param Container $container the dependency injection container.
     */
    public function register(Container $container)
    {

        $container['validation'] = function ($container) {
            return new Validator();
        };

    }

}