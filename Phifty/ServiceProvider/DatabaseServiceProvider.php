<?php

namespace Phifty\ServiceProvider;

use Maghead\Runtime\Config\FileConfigLoader;
use Maghead\Runtime\Config\Config;

use Maghead\Manager\ConnectionManager;
use Maghead\Manager\DatabaseManager;
use Maghead\Runtime\Bootstrap;
use Phifty\Kernel;

class DatabaseServiceProvider extends BaseServiceProvider
{
    protected $config;

    public function getId()
    {
        return 'database';
    }

    public static function canonicalizeConfig(Kernel $kernel, array $config)
    {
        if (empty($config) || !isset($config['config'])) {
            $config['config'] = \Phifty\Utils::find_db_config($kernel->rootDir);
        }
        $config['config'] = realpath($config['config']);
        return $config;
    }

    public function register(Kernel $kernel, array $options = array())
    {
        $this->config = FileConfigLoader::load($options['config']);

        $kernel->db = function () {
            return DatabaseManager::getInstance()->getMasterConnection();
        };
    }

    public function boot(Kernel $kernel)
    {
        Bootstrap::setup($this->config);
    }
}
