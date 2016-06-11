<?php

namespace Phifty\ServiceProvider;

use Phifty\Kernel;

class EventServiceProvider extends BaseServiceProvider
{
    public function getId()
    {
        return 'event';
    }

    public function register(Kernel $kernel, $options = array())
    {
        // php event pool
        $kernel->event = function () {
            return new \Universal\Event\PhpEvent();
        };
    }
}
