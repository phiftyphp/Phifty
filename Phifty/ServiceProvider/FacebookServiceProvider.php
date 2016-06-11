<?php
namespace Phifty\ServiceProvider;
use GearmanClient;
use GearmanWorker;
use ConfigKit\Accessor;
use Universal\Container\ObjectContainer;
use Exception;
use Phifty\Kernel;

/*
  FacebookServiceProvider:
    appId: {appId}
    secret: {app secret}
*/

class FacebookServiceProvider extends BaseServiceProvider
{
    public function getId() { return 'Facebook'; }

    public function register(Kernel $kernel, $options = array() )
    {
        $kernel->facebook = function() use ($options) {
            return new Facebook($options);
        };
    }
}
