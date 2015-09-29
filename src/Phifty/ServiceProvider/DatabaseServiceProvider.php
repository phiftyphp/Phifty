<?php
namespace Phifty\ServiceProvider;
use LazyRecord\ConnectionManager;
use ConfigKit\ConfigLoader;

class DatabaseServiceProvider extends BaseServiceProvider
{

    protected $config;

    public function getId() { return 'database'; }

    public function __construct(array $config = array())
    {
        $this->config = $config;
    }

    public function register($kernel, $options = array() )
    {
        $loader = \LazyRecord\ConfigLoader::getInstance();
        if (! $loader->loaded) {
            $loader->load($this->config);
            $loader->init();  // init data source and connection
        }
        $kernel->db = function() {
            return ConnectionManager::getInstance()->getConnection('default');
        };
    }

}
