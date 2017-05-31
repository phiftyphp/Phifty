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
use Universal\Container\ObjectContainer;

use Maghead\Runtime\Config\FileConfigLoader;

use Phifty\Bootstrap;
use Phifty\Generator\BootstrapGenerator;
use Phifty\Bundle\BundleLoader;
use Phifty\ServiceProvider\BundleServiceProvider;
use Phifty\ServiceProvider\DatabaseServiceProvider;
use Phifty\ServiceProvider\ConfigServiceProvider;
use Phifty\ServiceProvider\EventServiceProvider;
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
     * Create a minimal runtime Kernel instance
     *
     * This runtime kernel instance load service providers and bundles
     *
     * 1. inject the config loader
     * 2. register core services
     * 3. register services from config
     * 4. load the bundles
     *
     * @return Phifty\Kernel
     */
    public static function createKernel(ConfigLoader $configLoader, Psr4ClassLoader $psr4ClassLoader)
    {
        $kernel = new \Phifty\Kernel;
        $kernel->prepare($configLoader);


        // Load core service providers:
        //   1. config service provider
        //   2. event service provider
        //   3. bundle service provider
        //   4. [ ] class loader service provider
        //
        // $kernel->registerService(new \Phifty\ServiceProvider\ClassLoaderServiceProvider($splClassLoader));
        $kernel->registerService(new ConfigServiceProvider($configLoader));
        $kernel->registerService(new EventServiceProvider);

        // Load extra service providers
        if ($services = $configLoader->get('framework', 'ServiceProviders')) {
            foreach ($services as $name => $config) {
                if (!$config) {
                    $config = [];
                }

                $serviceClass = \Maghead\Utils::resolveClass($name, [
                    "App\\ServiceProvider",
                    "Phifty\\ServiceProvider"
                ]);

                if (!$serviceClass) {
                    throw new LogicException("service class '$serviceClass' does not exist.");
                }

                $config = $serviceClass::canonicalizeConfig($kernel, $config);
                if ($config === null) {
                    throw new LogicException("$serviceClass::canonicalizeConfig should return an array for service config.");
                }
                $kernel->registerService(new $serviceClass($config), $config);
            }
        }

        // load bundle service provider
        $bundleService = new BundleServiceProvider();

        // Load the bundle list config
        // The config structure:
        //     BundleLoader:
        //       Paths:
        //       - app_bundles
        //       - bundles
        $bundleLoaderConfig = $configLoader->get('framework', 'BundleLoader') ?: new \ConfigKit\Accessor([ 'Paths' => ['app_bundles','bundles'] ]);
        $kernel->registerService($bundleService, $bundleLoaderConfig);

        // Load bundle objects into the runtimeKernel
        $bundleLoader = new BundleLoader($kernel, $bundleLoaderConfig['Paths']->toArray());
        $bundleList = $configLoader->get('framework', 'Bundles');


        // Generating registering code for bundle classes
        if ($bundleList) {
            foreach ($bundleList as $bundleName => $bundleConfig) {
                $autoload = $bundleLoader->registerAutoload($bundleName, $psr4ClassLoader);
                if (!$autoload) {
                    continue;
                }

                // Load the bundle class files into the Kernel
                $bundleClass = $bundleLoader->loadBundleClass($bundleName);
                if (false === $bundleClass) {
                    throw new Exception("Bundle $bundleName class file '$bundleClassFile' doesn't exist.");
                }

                // TODO: This line seems could be removed.
                $bundleConfigArray = ($bundleConfig instanceof \ConfigKit\Accessor) ? $bundleConfig->toArray() : $bundleConfig;
                $kernel->bundles[$bundleName] = $bundleClass::getInstance($kernel, $bundleConfigArray);
            }
        }



        return $kernel;
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
