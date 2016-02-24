<?php

/**
 * @package    Datamanagement
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
 * @link       https://github.com/fiedsch/datamanagement
 */

namespace Fiedsch\Data\Augmentation\Provider;

use Fiedsch\Data\Utility\QuotaCell;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class QuotaCellServiceProvider implements ServiceProviderInterface
{

    /**
     * @param Container $container the dependency injection container.
     */
    public function register(Container $container)
    {
        if (!$container->offsetExists('quota.targets')) {
            $container['quota.targets'] = [];
        }

        $container['quota'] = function ($container) {
            return new QuotaCell($container['quota.targets']);
        };

    }

}
