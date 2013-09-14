<?php
namespace Phifty;
use Exception;
use ArrayAccess;
use IteratorAggregate;
use ArrayIterator;

class BundleManager
    implements ArrayAccess, IteratorAggregate
{

    /**
     * Bundle object stack
     */
    public $bundles = array();

    public $BundleDirs = array();

    public function isLoaded( $name )
    {
        return isset( $this->bundles[ $name ] );
    }

    public function registerBundleDir($dir)
    {
        $this->BundleDirs[] = $dir;
    }

    public function getList()
    {
        return array_keys( $this->bundles );
    }

    public function getBundles()
    {
        return array_values( $this->bundles );
    }

    /**
     * has Bundle
     */
    public function has( $name )
    {
        return isset($this->bundles[ $name ]);
    }

    /**
     * get Bundle object
     */
    public function get( $name )
    {
        if ( isset( $this->bundles[ $name ] ) )

            return $this->bundles[ $name ];
    }

    protected function _loadBundle($name)
    {
        # $name = '\\' . ltrim( $name , '\\' );
        $class = "$name\\$name";
        if ( class_exists($class,true) ) {
            return $class;
        } else {
            // try to require Bundle class from Bundle path
            $subpath = $name . DIRECTORY_SEPARATOR . $name . '.php';
            foreach ($this->BundleDirs as $dir) {
                $path = $dir . DIRECTORY_SEPARATOR . $subpath;
                if ( file_exists($path) ) {
                    require $path;

                    return $class;
                }
            }
        }
    }

    /**
     * Load Bundle
     */
    public function load( $name , $config = array() )
    {
        if ( $class = $this->_loadBundle($name) ) {
            $Bundle = $class::getInstance($config);
            return $this->bundles[ $name ] = $Bundle;
        }
        throw new Exception("Bundle $name not found.");

        return false;
    }

    /**
     * initialize all loaded bundles
     */
    public function init()
    {
        // TODO: initialize by config ordering
        foreach ($this->bundles as $name => $Bundle) {
            $Bundle->init();
        }
    }

    /**
     * Register Bundle to the Bundle pool.
     *
     * @param string               $name   Bundle id
     * @param Phifty\Bundle\Bundle $Bundle Bundle object
     */
    public function add($name,$Bundle)
    {
        $this->bundles[ $name ] = $Bundle;
    }

    public function offsetSet($name,$value)
    {
        $this->bundles[ $name ] = $value;
    }

    public function offsetExists($name)
    {
        return isset($this->bundles[ $name ]);
    }

    public function offsetGet($name)
    {
        return $this->bundles[ $name ];
    }

    public function offsetUnset($name)
    {
        unset($this->bundles[$name]);
    }

    public function getIterator()
    {
        return new ArrayIterator( $this->bundles );
    }

    public static function getInstance()
    {
        static $instance;
        return $instance ?: $instance = new static;
    }

}
