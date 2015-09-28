<?php
use ConfigKit\ConfigCompiler;
use ConfigKit\ConfigLoader;

// get PH_ROOT from phifty-core
defined('PH_ROOT')     || define('PH_ROOT', getcwd());
defined('PH_APP_ROOT') || define('PH_APP_ROOT', getcwd());
defined('DS')          || define('DS', DIRECTORY_SEPARATOR);

function initConfigLoader()
{

    // We load other services from the definitions in config file
    // Simple load three config files (framework.yml, database.yml, application.yml)
    $loader = new ConfigLoader();
    if (file_exists(PH_APP_ROOT.'/config/framework.yml')) {
        $loader->load('framework', PH_APP_ROOT.'/config/framework.yml');
    }

    // This is for DatabaseService
    if (file_exists(PH_APP_ROOT.'/db/config/database.yml')) {
        $loader->load('database', PH_APP_ROOT.'/db/config/database.yml');
    } elseif (file_exists(PH_APP_ROOT.'/config/database.yml')) {
        $loader->load('database', PH_APP_ROOT.'/config/database.yml');
    }

    // Config for application, services does not depends on this config file.
    if (file_exists(PH_APP_ROOT.'/config/application.yml')) {
        $loader->load('application', PH_APP_ROOT.'/config/application.yml');
    }

    // Only load testing configuration when environment
    // is 'testing'
    if (getenv('PHIFTY_ENV') === 'testing') {
        if (file_exists(PH_APP_ROOT.'/config/testing.yml')) {
            $loader->load('testing', ConfigCompiler::compile(PH_APP_ROOT.'/config/testing.yml'));
        }
    }
    return $loader;
}

function getSplClassLoader()
{
    $loader = null;
    if (0 && extension_loaded('apc')) {
        // require PH_APP_ROOT . '/vendor/corneltek/universal/src/Universal/ClassLoader/ApcClassLoader.php';
        $loader = new \Universal\ClassLoader\ApcClassLoader(PH_ROOT);
    } else {
        // require PH_APP_ROOT . '/vendor/corneltek/universal/src/Universal/ClassLoader/SplClassLoader.php';
        $loader = new \Universal\ClassLoader\SplClassLoader();
    }
    $loader->useIncludePath(false);
    $loader->register(false);

    return $loader;
}

// Load Kernel so we don't need to load by classloader.
require __DIR__.'/GlobalFuncs.php';
// require __DIR__ . '/Kernel.php';

global $kernel;
$kernel = new \Phifty\Kernel;
$kernel->prepare(); // prepare constants

// register default classloader service
// $composerLoader = require PH_ROOT . '/vendor/autoload.php';
$kernel->registerService(new \Phifty\ServiceProvider\ClassLoaderServiceProvider(getSplClassLoader()));

// load config service.
$configLoader = initConfigLoader();
$kernel->registerService(new \Phifty\ServiceProvider\ConfigServiceProvider($configLoader));

// load event service, so that we can bind events in Phifty
$kernel->registerService(new \Phifty\ServiceProvider\EventServiceProvider());

// if the framework config is defined.
if ($configLoader->isLoaded('framework')) {
    // we should load database service before other services
    // because other services might need database service
    if ($configLoader->isLoaded('database')) {
        $kernel->registerService(new \Phifty\ServiceProvider\DatabaseServiceProvider());
    }

    if ($appconfigs = $kernel->config->get('framework', 'Applications')) {
        foreach ($appconfigs as $appname => $appconfig) {
            $kernel->classloader->addNamespace(array(
                $appname => array(PH_APP_ROOT.'/applications', PH_ROOT.'/applications'),
            ));
        }
    }

    if ($services = $kernel->config->get('framework', 'ServiceProviders')) {
        foreach ($services as $name => $options) {
            // not full qualified classname
            $class = (false === strpos($name, '\\')) ? ('Phifty\\ServiceProvider\\'.$name) : $name;
            $kernel->registerService(new $class(), $options);
        }
    }
}

/**
 * kernel() is a global shorter helper function to get Phifty\Kernel instance.
 *
 * Initialize kernel instance, classloader, bundles and services.
 *
 * @return Phifty\Kernel
 */
function kernel()
{
    global $kernel;
    if ($kernel) {
        return $kernel;
    }
    $kernel->init();
    return $kernel;
}

return kernel();
