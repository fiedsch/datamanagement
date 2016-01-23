<?php

/**
 * @package    Datamanagement
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
 * @version    0.0.1
 * @link       https://github.com/fiedsch/datamanagement
 */

namespace Fiedsch\Data\Augmentation\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Fiedsch\Data\Utility\TokenCreator;

class TokenServiceProvider implements ServiceProviderInterface {

    /**
     * @param Container $pimple the dependency injection container.
     */
    public function register(Container $container) {

        if (!$container->offsetExists('token.length')) {
            $container['token.length'] = TokenCreator::DEFAULT_LENGTH;
        }
        if (!$container->offsetExists('token.case')) {
            $container['token.case'] = TokenCreator::UPPER;
        }

        $container['token'] = function ($container) {
            return new TokenCreator($container['token.length'], $container['token.case']);
        };

    }

}