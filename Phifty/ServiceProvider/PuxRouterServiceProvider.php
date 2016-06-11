<?php
namespace Phifty\ServiceProvider;
use Phifty\ServiceProvider\ServiceProvider;
use Phifty\Kernel;
use Universal\Http\HttpRequest;
use Pux\Mux;
use Pux\Dispatcher\Dispatcher;
use Pux\MuxBuilder\RESTfulMuxBuilder;
use Pux\RouteRequest;

class PuxRouterServiceProvider extends BaseServiceProvider
{
    public function getId()
    {
        return 'router';
    }

    public function register(Kernel $kernel, $options = array() )
    {
        $kernel->rootMux = function() {
            return new Mux;
        };
        $kernel->restful = function() use ($kernel) {
            return new RESTfulMuxBuilder($kernel->rootMux, [ 'prefix' => '/=' ]);
        };
    }
}

