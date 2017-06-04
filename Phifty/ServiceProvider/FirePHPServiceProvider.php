<?php

namespace Phifty\ServiceProvider;

use Phifty\ComposerConfigBridge;
use Phifty\Kernel;

class FirePHPServiceProvider extends ServiceProvider implements ComposerConfigBridge
{
    public function getId()
    {
        return 'firephp';
    }

    public function register(Kernel $k, array $options = array())
    {
        // skip this plugin if we are not in development mode
        // or if we are in command-line mode.
        if ($k->environment !== 'development' || $k->isCLI) {
            return;
        }

    }

    public function boot(Kernel $k)
    {
        // @see http://www.firephp.org/HQ/Use.htm
        if (function_exists('fb')) {
            $k->event->register('request.end', function () use ($k) {
                fb((memory_get_usage() / 1024 / 1024).' MB', 'Memory Usage');
                fb((memory_get_peak_usage() / 1024 / 1024).' MB', 'Memory Peak Usage');

                // FIXME: use environment
                if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
                    fb((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000 .' ms', 'Request time');
                }
            });
        }
    }



    public function getComposerRequire()
    {
        return ['firephp/firephp-core' => 'dev-master'];
    }
}
