<?php
namespace Phifty\Service;
use Universal\Event\PhpEvent;

class EventService
    implements ServiceInterface
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
