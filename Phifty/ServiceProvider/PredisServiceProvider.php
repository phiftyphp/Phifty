<?php
namespace Phifty\ServiceProvider;
use Phifty\ComposerConfigBridge;
use Phifty\Kernel;
use Predis\Client as PredisClient;
use Exception;

class PredisServiceProvider extends BaseServiceProvider implements ComposerConfigBridge
{
    public function getId() { return 'Predis'; }


    static public function canonicalizeConfig(Kernel $kernel, array $options)
    {
        return $options;
    }


    public function register(Kernel $kernel, $options = array() )
    {
        $kernel->redis = function() use ($options) {
            return new PredisClient($options);
        };
    }

    public function getComposerDependency() 
    {
        return ["predis/predis" => "@stable"];
    }
}



