<?php

namespace Phifty\ServiceProvider;

use Maghead\Manager\ConnectionManager;
use Maghead\Manager\DatabaseManager;
use Maghead\Runtime\Config\FileConfigLoader;
use Maghead\Runtime\Bootstrap;
use Phifty\Kernel;

class DatabaseServiceProvider extends BaseServiceProvider
{
    public function getId()
    {
        return 'database';
    }

    public function register(Kernel $kernel, $options = array())
    {
        $config = FileConfigLoader::load($this->config);
        Bootstrap::setup($config);
        $kernel->db = function () {
            return DatabaseManager::getInstance()->getConnection('default');
        };
    }
}
