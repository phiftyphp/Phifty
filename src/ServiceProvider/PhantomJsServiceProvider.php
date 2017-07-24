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
        $kernel->phantom = function($c) use ($options) {
            $client = Client::getInstance();

            if (isset($options['Path'])) {
                $client->getEngine()->setPath($options['Path']);
            }

            return $client;
        };
    }
}
