<?php

/**
 * @package    Datamanagement
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2016 Andreas Fieger
 * @license    MIT
 * @link       https://github.com/fiedsch/datamanagement
 */

namespace Fiedsch\Data\Augmentation\Provider;

use Fiedsch\Data\Utility\TokenCreator;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class TokenServiceProvider implements ServiceProviderInterface
{

    /**
     * @param Container $container the dependency injection container.
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     * @noinspection PhpUnused
     */
    public function register(Container $container): void
    {

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