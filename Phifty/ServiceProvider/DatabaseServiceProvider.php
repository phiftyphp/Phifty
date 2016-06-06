<?php
namespace Phifty\ServiceProvider;
use LazyRecord\ConnectionManager;
use ConfigKit\ConfigLoader;

class DatabaseServiceProvider extends BaseServiceProvider
{
    public function getId() { return 'database'; }

    public function register($kernel, $options = array() )
    {
        // TODO: move to generate prepare...
        $loader = \LazyRecord\ConfigLoader::getInstance();
        if (!$loader->loaded) {
            $loader->load($this->config);
            $loader->init();  // init data source and connection
        }
        $kernel->db = function() {
            return ConnectionManager::getInstance()->getConnection('default');
        };
    }

}
