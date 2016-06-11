<?php

namespace Phifty\ServiceProvider;

use Phifty\Kernel;
use Roller\Router;

class RollerRouterServiceProvider extends BaseServiceProvider
{
    public function getId()
    {
        return 'Router';
    }

    public function register(Kernel $kernel, $options = array())
    {
        $kernel->restful = function () use ($kernel) {
            $restful = new \Roller\Plugin\RESTful(array(
                'prefix' => '/restful',
            ));

            return $restful;
        };
        $kernel->router = function () use ($kernel) {
            $router = null;
            if ('production' === $kernel->environment) {
                $router = new Router(null, array(
                    'route_class' => 'Phifty\\Routing\\Route',
                    'cache_id' => PH_APP_ROOT ? PH_APP_ROOT : $kernel->config->get('framework', 'uuid'),
                ));
            } else {
                $router = new Router(null, array(
                    'route_class' => 'Phifty\\Routing\\Route',
                ));
            }
            $router->addPlugin($kernel->restful);

            return $router;
        };
    }
}
