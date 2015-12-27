<?php
namespace Phifty\ServiceProvider;
use Phifty\ServiceProvider\ServiceProvider;
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

    public function register($kernel, $options = array() )
    {
        $kernel->rootMux = function() use ($kernel) {
            return new Mux;
        };
        $kernel->restful = function() use ($kernel) {
            return new RESTfulMuxBuilder($kernel->rootMux, [ 'prefix' => '/=' ]);
        };
    }
}

