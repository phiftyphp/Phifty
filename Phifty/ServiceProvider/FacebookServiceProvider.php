<?php

namespace Phifty\ServiceProvider;

use Phifty\Kernel;

/*
  FacebookServiceProvider:
    appId: {appId}
    secret: {app secret}
  This class is @deprecated
*/

class FacebookServiceProvider extends BaseServiceProvider
{
    public function getId()
    {
        return 'Facebook';
    }

    public function register(Kernel $kernel, array $options = array())
    {
        $kernel->facebook = function () use ($options) {
            return new Facebook($options);
        };
    }
}
