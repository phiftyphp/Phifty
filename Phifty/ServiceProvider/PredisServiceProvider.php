<?php

namespace Phifty\ServiceProvider;

use Phifty\ComposerConfigBridge;
use Phifty\Kernel;
use Predis\Client as PredisClient;

class PredisServiceProvider extends BaseServiceProvider implements ComposerConfigBridge
{
    public function getId()
    {
        return 'Predis';
    }

    public static function canonicalizeConfig(Kernel $kernel, array $options)
    {
        return $options;
    }

    public function register(Kernel $kernel, $options = array())
    {
        $kernel->redis = function () use ($options) {
            return new PredisClient($options);
        };
    }

    public function getComposerDependency()
    {
        return ['predis/predis' => '@stable'];
    }
}
