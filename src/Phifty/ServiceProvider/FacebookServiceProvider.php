<?php
namespace Phifty\ServiceProvider;
use GearmanClient;
use GearmanWorker;
use ConfigKit\Accessor;
use Universal\Container\ObjectContainer;
use Exception;

/*
  FacebookServiceProvider:
    appId: {appId}
    secret: {app secret}
*/

class FacebookServiceProvider extends BaseServiceProvider
{
    public function getId() { return 'Facebook'; }

    public function register($kernel, $options = array() )
    {
        $kernel->facebook = function() use ($options) {
            return new Facebook($options);
        };
    }
}
