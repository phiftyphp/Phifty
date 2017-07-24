<?php

namespace Phifty\ServiceProvider;

use Phifty\ComposerConfigBridge;
use Phifty\Kernel;
use Predis\Client as PredisClient;

class PredisServiceProvider extends ServiceProvider implements ComposerConfigBridge
{
    public function getId()
    {
        return 'Predis';
    }

    public static function canonicalizeConfig(Kernel $kernel, array $options)
    {
        return $options;
    }

    public function register(Kernel $kernel, array $options = array())
    {
        $kernel->predis = function () use ($options) {
            return new PredisClient($options);
        };
    }

    public function getComposerRequire()
    {
        return ['predis/predis' => '@stable'];
    }
}
