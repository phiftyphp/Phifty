<?php

namespace Phifty\ServiceProvider;

use Phifty\Kernel;
use JonnyW\PhantomJs\Client;

class PhantomJsServiceProvider extends ServiceProvider
{

    public function getId()
    {
        return 'phantomjs';
    }

    public function register(Kernel $kernel, array $options = array())
    {
        $kernel->phantom = function($c) {
            return Client::getInstance();
        };
    }
}
