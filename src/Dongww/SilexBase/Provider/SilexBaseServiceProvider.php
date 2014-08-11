<?php
/**
 * User: dongww
 * Date: 14-7-8
 * Time: 上午11:06
 */

namespace Dongww\SilexBase\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class SilexBaseServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {

    }

    public function boot(Application $app)
    {
        $app['twig']->addExtension(new TwigCoreExtension());
    }
}
