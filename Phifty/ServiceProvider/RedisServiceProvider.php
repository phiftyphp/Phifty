<?php

namespace Phifty\ServiceProvider;

use Phifty\ComposerConfigBridge;
use Phifty\Kernel;

class RedisServiceProvider extends BaseServiceProvider implements ComposerConfigBridge
{
    public function getId()
    {
        return 'Redis';
    }

    public static function canonicalizeConfig(Kernel $kernel, array $options)
    {
        if (!isset($options['Host'])) {
            $options['Host'] = '127.0.0.1';
        }
        if (!isset($options['Port'])) {
            $options['Port'] = 6379;
        }
        return $options;
    }

    public function register(Kernel $kernel, $options = array())
    {
        $kernel->redis = function () use ($options) {
            $redis = new Redis();
            $redis->connect($options['Host'], $options['Port']);
            return $redis;
        };
    }

    public function getComposerDependency()
    {
        return ['ext-redis' => '*'];
    }
}
