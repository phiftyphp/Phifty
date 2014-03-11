<?php
namespace Phifty\Service;
use GearmanClient;
use GearmanWorker;
use ConfigKit\Accessor;
use Universal\Container\ObjectContainer;
use Exception;

/*
  FacebookService:
    appId: {appId}
    secret: {app secret}
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
