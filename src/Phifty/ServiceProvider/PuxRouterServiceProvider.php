<?php
namespace Phifty\ServiceProvider;
use Phifty\ServiceProvider\ServiceProvider;
use Pux\Mux;
use Pux\Dispatcher\Dispatcher;

class PuxRouterServiceProvider implements ServiceProvider
{
    public function getId()
    {
        return 'router';
    }

    public function register($kernel, $options = array() )
    {
        $kernel->router = function() use ($kernel) {
            return new Mux;
        };
    }
}

