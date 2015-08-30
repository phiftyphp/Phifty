<?php
namespace Phifty;
use Exception;
use ArrayAccess;
use IteratorAggregate;
use ArrayIterator;
use Phifty\Bundle;
use Phifty\Kernel;

class BundleManager implements ArrayAccess, IteratorAggregate
{

    /**
     * Bundle object stack
     */
    public $bundles = array();


    /**
     * @var string[]
     */
    public $bundleDirs = array();

    protected $kernel;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    public function isLoaded( $name )
    {
        return isset( $this->bundles[ $name ] );
    }

    public function registerBundleDir($dir)
    {
        $this->bundleDirs[] = $dir;
        if ( $twig = kernel()->twig ) {
            $twig->loader->addPath($dir);
        }
    }


    /**
     * get bundle names
     *
     * @return string[] bundle names
     */
    public function getList()
    {
        return array_keys( $this->bundles );
    }


    /**
     * @return Phifty\Bundle[] bundle objects
     */
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
     * Get Bundle object
     *
     * @param string $name return bundle object.
     */
    public function get( $name )
    {
        if ( isset( $this->bundles[ $name ] ) ) {
            return $this->bundles[ $name ];
        }
    }


    /**
     * load bundle object
     *
     * @param string $name
     */
    protected function _loadBundle($name)
    {
        # $name = '\\' . ltrim( $name , '\\' );
        $class = "$name\\$name";
        if ( class_exists($class,true) ) {
            return $class;
        } else {
            // try to require Bundle class from Bundle path
            $subpath = $name . DIRECTORY_SEPARATOR . $name . '.php';
            foreach ($this->bundleDirs as $dir) {
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
     *
     * @param string $name
     * @param array $config
     */
    public function load($name, $config = array())
    {
        if ($class = $this->_loadBundle($name)) {
            $bundle = $class::getInstance($this->kernel, $config);
            return $this->bundles[ $name ] = $bundle;
        }
        return false;
    }

    /**
     * Initialize all loaded bundles.
     */
    public function init()
    {
        // initialize bundle object.
        foreach ($this->bundles as $b) {
            $b->init();
        }
        // build routes
        foreach ($this->bundles as $b) {
            $b->routes();
        }
    }

    /**
     * Register Bundle to the Bundle pool.
     *
     * @param string               $name   Bundle id
     * @param Phifty\Bundle\Bundle $Bundle Bundle object
     */
    public function add($name, Bundle $bundle)
    {
        $this->bundles[ $name ] = $bundle;
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
        return new ArrayIterator($this->bundles);
    }
}
