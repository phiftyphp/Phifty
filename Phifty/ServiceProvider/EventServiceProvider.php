<?php
namespace Phifty\ServiceProvider;
use Universal\Event\PhpEvent;

class EventServiceProvider extends BaseServiceProvider
{

    public function getId() { return 'event'; }

    public function register($kernel, $options = array() )
    {
        // php event pool
        $kernel->event = function() {
            return new \Universal\Event\PhpEvent;
        };
    }

}
