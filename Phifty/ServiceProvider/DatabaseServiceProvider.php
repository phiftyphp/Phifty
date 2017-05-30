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
    public function getId()
    {
        return 'database';
    }

    public function register(Kernel $kernel, $config = array())
    {
        $config = FileConfigLoader::load($config['config']);
        Bootstrap::setup($config);

        $kernel->db = function () {
            return DatabaseManager::getInstance()->getMasterConnection();
        };
    }

    public static function canonicalizeConfig(Kernel $kernel, array $config)
    {
        if (empty($config) || !isset($config['config'])) {
            $config['config'] = \Phifty\Utils::find_db_config($kernel->rootDir);
        }
        return $config;
    }
}
