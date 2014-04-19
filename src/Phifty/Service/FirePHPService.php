<?php
namespace Phifty\Service;
use Phifty\ComposerConfigBridge;

class FirePHPService
    implements ServiceInterface, ComposerConfigBridge
{

    public function getId() { return 'FirePHP'; }

    public function register($kernel, $options = array() )
    {
        // skip this plugin if we are not in development mode
        // or if we are in command-line mode.
        if ($kernel->environment !== 'development' || $kernel->isCLI) {
            return;
        }

        /**
         * http://www.firephp.org/HQ/Use.htm
         **/
        // if firebug supports
        $kernel->event->register('phifty.after_page', function() use ($kernel) {
            if ( function_exists('fb') ) {
                fb( (memory_get_usage() / 1024 / 1024 ) . ' MB'  , 'Memory Usage' );
                fb( (memory_get_peak_usage() / 1024 / 1024 ) . ' MB'  , 'Memory Peak Usage' );
                if ( isset($_SERVER['REQUEST_TIME_FLOAT']) ) {
                    fb( (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000 . ' ms' , 'Request time' );
                }
            }
        });
    }

    public function getComposerDependency() 
    {
        return ["firephp/firephp-core" => "dev-master"];
    }
}
