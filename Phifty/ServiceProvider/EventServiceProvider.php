<?php

namespace Phifty\ServiceProvider;

use Phifty\Kernel;
use Universal\Event\EventDispatcher;

class EventServiceProvider extends ServiceProvider
{
    public function getId()
    {
        return 'event';
    }

    public function register(Kernel $kernel, array $options = array())
    {
        // php event pool
        $kernel->event = function () {
            return new EventDispatcher;
        };
    }
}
