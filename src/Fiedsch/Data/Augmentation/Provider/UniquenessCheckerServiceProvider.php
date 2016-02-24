<?php

/**
 * @package    Datamanagement
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
 * @link       https://github.com/fiedsch/datamanagement
 */

namespace Fiedsch\Data\Augmentation\Provider;

use Fiedsch\Data\Utility\UniquenessChecker;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class UniquenessCheckerServiceProvider implements ServiceProviderInterface
{

    /**
     * @param Container $container the dependency injection container.
     */
    public function register(Container $container)
    {

        $container['unique'] = function () {
            return new UniquenessChecker();
        };

    }

}