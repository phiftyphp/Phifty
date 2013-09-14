<?php
namespace Phifty\Service;
use LazyRecord\ConnectionManager;

class DatabaseService
    implements ServiceInterface
{

    public function getId() { return 'database'; }

    public function register($kernel, $options = array() )
    {
        $config = $kernel->config->stashes['database'];
        $loader = \LazyRecord\ConfigLoader::getInstance();
        if (! $loader->loaded) {
            $loader->load( $config );
            $loader->init();  // init data source and connection
        }
        $kernel->db = function() {
            return ConnectionManager::getInstance()->getConnection();
        };
    }

}
