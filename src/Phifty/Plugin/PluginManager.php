<?php
namespace Phifty\Plugin;
use Exception;
use ArrayAccess;
use IteratorAggregate;
use ArrayIterator;

class PluginManager
    implements ArrayAccess, IteratorAggregate
{

    /**
     * plugin object stack
     */
    public $plugins = array();

    public $pluginDirs = array();

    public function isLoaded( $name )
    {
        return isset( $this->plugins[ $name ] );
    }

    public function registerPluginDir($dir)
    {
        $this->pluginDirs[] = $dir;
    }

    public function getList()
    {
        return array_keys( $this->plugins );
    }

    public function getPlugins()
    {
        return array_values( $this->plugins );
    }

    /**
     * has plugin
     */
    public function has( $name )
    {
        return isset($this->plugins[ $name ]);
    }

    /**
     * get plugin object
     */
    public function get( $name, $lookup = false )
    {
        if ( isset( $this->plugins[ $name ] ) ) {
            return $this->plugins[ $name ];
        } elseif ( $lookup ) {
            return $this->lookup( $name );
        }
    }

    protected function _loadPlugin($name)
    {
        # $name = '\\' . ltrim( $name , '\\' );
        $class = "$name\\$name";
        if ( class_exists($class,true) ) {
            return $class;
        } else {
            // try to require plugin class from plugin path
            return $this->tryRequire($name);
        }
    }

    public function tryRequire($name)
    {
        $class = "$name\\$name";
        $subpath = $name . DIRECTORY_SEPARATOR . $name . '.php';
        foreach ($this->pluginDirs as $dir) {
            $path = $dir . DIRECTORY_SEPARATOR . $subpath;
            if ( file_exists($path) ) {
                require $path;
                return $class;
            }
        }
    }

    /**
     * Load plugin
     */
    public function load( $name , $config = array() )
    {
        if ( $class = $this->_loadPlugin($name) ) {
            $plugin = $class::getInstance($config);
            return $this->plugins[ $name ] = $plugin;
        }
        throw new Exception("Plugin $name not found.");

        return false;
    }

    /**
     * initialize all loaded plugins
     */
    public function init()
    {
        // TODO: initialize by config ordering
        foreach ($this->plugins as $name => $plugin) {
            $plugin->init();
        }
    }

    /**
     * Register plugin to the plugin pool.
     *
     * @param string               $name   plugin id
     * @param Phifty\Plugin\Plugin $plugin plugin object
     */
    public function add($name,$plugin)
    {
        $this->plugins[ $name ] = $plugin;
    }

    public function offsetSet($name,$value)
    {
        $this->plugins[ $name ] = $value;
    }

    public function offsetExists($name)
    {
        return isset($this->plugins[ $name ]);
    }

    public function offsetGet($name)
    {
        return $this->plugins[ $name ];
    }

    public function offsetUnset($name)
    {
        unset($this->plugins[$name]);
    }

    public function getIterator()
    {
        return new ArrayIterator( $this->plugins );
    }

    public static function getInstance()
    {
        static $instance;
        return $instance ?: $instance = new static;
    }

}
