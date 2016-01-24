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
use Fiedsch\Data\Utility\UniquenessChecker;

class UniquenessCheckerServiceProvider implements ServiceProviderInterface {

    /**
     * @param Container $pimple the dependency injection container.
     */
    public function register(Container $container) {

        $container['unique'] = function () {
            return new UniquenessChecker();
        };

    }

}