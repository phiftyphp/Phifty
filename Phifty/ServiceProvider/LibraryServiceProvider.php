<?php

namespace Phifty\ServiceProvider;

use Exception;
use Phifty\Kernel;

class LibraryLoader
{
    public $kernel;

    public $throwOnFail = true;

    public $paths = array();

    public $loaded = array();

    public function __construct($kernel)
    {
        $this->kernel = $kernel;
        $this->paths[] = PH_APP_ROOT.DS.'libraries';
        $this->paths[] = PH_ROOT.DS.'libraries';
    }

    public function getPaths()
    {
        return $this->paths;
    }

    public function addPath($path)
    {
        $this->paths[] = $path;
    }

    public function insertPath($path)
    {
        $this->paths = array_unshift($this->paths, $path);
    }

    public function load($name, $script = 'init.php')
    {
        if (isset($this->loaded[ $name ])) {
            return $this->loaded[ $name ];
        }

        foreach ($this->getPaths() as $path) {
            $dir = $path.DS.$name;
            $initFile = $dir.DS.$script;
            if (file_exists($dir) && is_dir($dir)) {
                if (!file_exists($initFile)) {
                    throw new Exception("$initFile not found.");
                }
                $return = require($initFile) ?: $dir;
                $this->loaded[ $name ] = $return;

                return $return;
            }
        }

        if ($this->throwOnFail) {
            throw new Exception("Can not load library $name");
        }

        return false;
    }
}

/***
 * if ( $dir = kernel()->library->load('google-recaptcha') ) {
 *
 * }
 */
class LibraryServiceProvider extends BaseServiceProvider
{
    public $classloader;

    public function getId()
    {
        return 'LibraryLoader';
    }

    public function register(Kernel $kernel, $options = array())
    {
        $self = $this;
        $kernel->library = function () use ($self, $kernel) {
            return new LibraryLoader($kernel);
        };
    }
}
