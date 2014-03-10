<?php
namespace Phifty\Service;
use GearmanClient;
use GearmanWorker;
use ConfigKit\Accessor;
use Universal\Container\ObjectContainer;
use Exception;

/*
  FacebookService:
    appId: 327281450625323
    secret: 721be7a340cc0b4b5073071cabb8d26b
*/

class FacebookService implements ServiceInterface
{
    public function getId() { return 'Facebook'; }

    public function register($kernel, $options = array() )
    {
        $kernel->facebook = function() use ($options) {
            return new Facebook($options);
        };
    }
}
