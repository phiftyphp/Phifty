<?php

namespace Phifty;

use ConfigKit\ConfigCompiler;
use ConfigKit\ConfigLoader;
use CodeGen\Generator\AppClassGenerator;
use CodeGen\Block;
use CodeGen\Raw;
use CodeGen\Statement\RequireStatement;
use CodeGen\Statement\RequireComposerAutoloadStatement;
use CodeGen\Statement\RequireClassStatement;
use CodeGen\Statement\ConstStatement;
use CodeGen\Statement\DefineStatement;
use CodeGen\Statement\AssignStatement;
use CodeGen\Statement\Statement;
use CodeGen\Expr\NewObject;
use CodeGen\Expr\MethodCall;
use CodeGen\Expr\StaticMethodCall;
use CodeGen\Variable;
use CodeGen\Comment;
use CodeGen\CommentBlock;
use Universal\ClassLoader\Psr4ClassLoader;
use Universal\ClassLoader\SplClassLoader;

use Phifty\Bootstrap;
use Phifty\Generator\BootstrapGenerator;
use Phifty\Bundle\BundleLoader;
use Phifty\ServiceProvider\BundleServiceProvider;
use Phifty\ServiceProvider\DatabaseServiceProvider;
use Phifty\ServiceProvider\ConfigServiceProvider;
use Phifty\ServiceProvider\EventServiceProvider;
use Phifty\ServiceProvider\SessionServiceProvider;
use Phifty\Kernel;
use Phifty\Utils;


use Phifty\Environment\Production;
use Phifty\Environment\Development;

use ReflectionClass;
use Exception;
use LogicException;

class Bootstrap
{

    /**
     * Create a minimal dynamic Kernel instance
     *
     * This dynamic kernel instance load service providers and bundles
     *
     * 1. inject the config loader
     * 2. register core services
     * 3. register services from config
     * 4. load the bundles
     *
     * @return Phifty\Kernel
     */
    public static function createKernel(ConfigLoader $configLoader, Psr4ClassLoader $psr4ClassLoader, $environment)
    {
        $k = Kernel::dynamic($configLoader, $environment);
        static::loadServices($k, $configLoader);
        static::loadBundleService($k, $configLoader, $psr4ClassLoader);
        return $k;
    }

    /**
     * Load core service providers:
     *
     * 1. config service provider
     * 2. event service provider
     * 3. bundle service provider
     * 4. [ ] class loader service provider
     *
     */
    private static function loadServices(Kernel $k, ConfigLoader $configLoader)
    {
        $k->registerServiceProvider(new ConfigServiceProvider($configLoader));
        $k->registerServiceProvider(new EventServiceProvider);

        // Load extra service providers
        if ($services = $configLoader->get('framework', 'ServiceProviders')) {
            foreach ($services as $name => $config) {
                if (!$config) {
                    $config = [];
                }

                $class = \Maghead\Utils::resolveClass($name, [
                    "App\\ServiceProvider",
                    "Phifty\\ServiceProvider"
                ]);

                if (!$class) {
                    throw new LogicException("service class '$class' does not exist.");
                }

                $config = $class::canonicalizeConfig($k, $config);
                if ($config === null) {
                    throw new LogicException("$class::canonicalizeConfig should return an array for service config.");
                }

                $provider = new $class;
                $k->registerServiceProvider($provider, $config);
            }
        }
    }


    /**
     * Load the bundle list config
     *
     * The config structure:
     *
     *     BundleLoader:
     *       Paths:
     *       - app_bundles
     *       - bundles
     *
     */
    private static function loadBundleService(Kernel $k, ConfigLoader $configLoader, Psr4ClassLoader $psr4ClassLoader)
    {
        // load bundle service provider
        $serviceProvider = new BundleServiceProvider();

        $loaderConfig = $configLoader->get('framework', 'BundleLoader') ?: new \ConfigKit\Accessor([ 'Paths' => ['app_bundles','bundles'] ]);
        $k->registerServiceProvider($serviceProvider, $loaderConfig->toArray());

        // Load bundle objects into the runtimeKernel
        $loader = new BundleLoader($k, $loaderConfig['Paths']->toArray());
        $configBundles = $configLoader->get('framework', 'Bundles') ?: [];

        self::setupBundleAutoload($loader, $configBundles, $psr4ClassLoader);

        // Generating registering code for bundle classes
        foreach ($configBundles as $bundleName => $bundleConfig) {
            // Load the bundle class files into the Kernel
            $bundleClass = $loader->loadBundleClass($bundleName);
            if (false === $bundleClass) {
                throw new Exception("Can't find bundle $bundleName class file. '$bundleClass' doesn't exist.");
            }

            $bundleConfigArray = ($bundleConfig instanceof \ConfigKit\Accessor) ? $bundleConfig->toArray() : $bundleConfig;
            $k->bundles[$bundleName] = $bundleClass::getInstance($k, $bundleConfigArray);
        }
    }

    private static function setupBundleAutoload(BundleLoader $loader, $bundles, Psr4ClassLoader $psr4ClassLoader)
    {
        foreach ($bundles as $name => $config) {
            $loader->registerAutoload($name, $psr4ClassLoader);
        }
    }


    public static function createConfigLoader($baseDir, $env = null)
    {
        // We load other services from the definitions in config file
        // Simple load three config files (framework.yml, database.yml, application.yml)
        $loader = new ConfigLoader;

        if (file_exists("$baseDir/config/framework.yml")) {
            $loader->load('framework', "$baseDir/config/framework.yml");
        }

        // Config for application, services does not depends on this config file.
        if (file_exists("$baseDir/config/application.yml")) {
            $loader->load('application', "$baseDir/config/application.yml");
        }

        if (file_exists("$baseDir/config/database.yml")) {
            $loader->load('database', "$baseDir/config/database.yml");
        }

        // Only load testing configuration when environment
        // is 'testing'
        if ($env === 'testing') {
            if (file_exists("$baseDir/config/testing.yml")) {
                $loader->load('testing', "$baseDir/config/testing.yml");
            }
        }

        return $loader;
    }
}
