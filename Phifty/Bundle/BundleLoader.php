<?php
namespace Phifty\Bundle;
use Phifty\Kernel;
use Universal\ClassLoader\Psr4ClassLoader;
use ReflectionObject;
use ReflectionClass;

class BundleLoader
{
    protected $kernel;

    protected $lookupDirectories = [];

    public function __construct(Kernel $kernel, array $lookupDirectories = [])
    {
        $this->lookupDirectories = $lookupDirectories;
    }

    /**
     * Try to get the autoload config from composer json
     *
     * If not, just return a general PSR-4 class loading config.
     *
     * Currently we only support PSR-4 class loading.
     *
     * @return array
     */
    private function getAutoloadConfig($name)
    {
        $class = "$name\\$name";

        // if we could find the class, we don't need custom class loader for this.
        // we can get the bundle from the composer config
        if (class_exists($class, true)) {
            $refl = new ReflectionClass($class);
            $classPath = $refl->getFileName();
            $bundleDir = dirname($classPath); // realpath already

            $composerFile = $bundleDir . DIRECTORY_SEPARATOR . 'composer.json';
            if (file_exists($composerFile)) {
                $composerConfig = json_decode(file_get_contents($composerFile), true);
                if (isset($composerConfig['autoload']['psr-4'])) {
                    $prefixes = [];
                    foreach ($composerConfig['autoload']['psr-4'] as $prefix => $subpath) {
                        $prefixes[$prefix] = rtrim($bundleDir . DIRECTORY_SEPARATOR . $subpath , DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                    }
                    return $prefixes;
                }
            }
        }

        if ($classPath = $this->findBundleClassFile($name)) {
            $bundleDir = dirname($classPath);
            return [ "$name\\" => $bundleDir . DIRECTORY_SEPARATOR ];
        }

        return false;
    }

    public function registerAutoload($name, Psr4ClassLoader $classLoader)
    {
        $prefixes = $this->getAutoloadConfig($name);
        if (!$prefixes) {
            return;
        }

        foreach ($prefixes as $prefix => $path) {
            $classLoader->addPrefix($prefix, $path);
        }
        return $prefixes;
    }


    public function getBundlePrefixes($bundleList, array $psr4Map = null)
    {
        $bundlePrefixes = [];
        foreach ($bundleList as $bundleName => $bundleConfig) {
            $autoload = $this->getAutoloadConfig($bundleName);
            if (!$autoload) {
                continue;
            }
            // Skip the prefixes that are already defined in the composer psr4 config.
            foreach ($autoload as $prefix => $path) {
                if ($psr4Map && isset($psr4Map[$prefix])) {
                    continue;
                }
                $bundlePrefixes[$prefix] = $path;
            }
        }
        return $bundlePrefixes;
    }




    /**
     * Load bundle by bundle name
     *
     * @param string $name
     * @param array|ConfigKit\Accessor $config
     */
    public function load($name, $config)
    {
        $class = $this->loadBundleClass($name);
        return $class::getInstance($this->kernel, $config);
    }

    /**
     * Find the bundle directory location based on the lookup paths
     *
     * @param string $name
     * @return string $className
     */
    public function findBundleClassFile($name)
    {
        $subpath = $name . DIRECTORY_SEPARATOR . $name . '.php';
        foreach ($this->lookupDirectories as $dir) {
            $classPath = $dir . DIRECTORY_SEPARATOR . $subpath;
            if (file_exists($classPath)) {
                return realpath($classPath);
            }
        }
        return false;
    }

    public function getBundleClass($bundleName)
    {
        return "$bundleName\\$bundleName";
    }


    /**
     * Require bundle class file and return the class name
     *
     * @param string $name
     */
    public function loadBundleClass($name)
    {
        $class = "$name\\$name";

        // Try spl autoload
        if (class_exists($class,true)) {
            return $class;
        }

        // Find the class file manually.
        if ($classPath = $this->findBundleClassFile($name)) {
            require $classPath;
            return $class;
        }

        return false;
    }

    /*
    $config = $kernel->config->get('framework','Bundles');
    $manager = new BundleManager($kernel);
    if ($config) {
        foreach ($config as $bundleName => $bundleConfig) {
            $kernel->classloader->addNamespace(array(
                $bundleName => $this->config["Paths"],
            ));
        }
    }
    */
}





