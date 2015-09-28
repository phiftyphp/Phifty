<?php
global $kernel;
global $splClassLoader;
global $composerClassLoader;
$kernel = new \Phifty\Kernel;
$kernel->prepare(); // prepare constants
$kernel->registerService(new \Phifty\ServiceProvider\ClassLoaderServiceProvider($splClassLoader));

$configLoader = new \App\ConfigLoader;
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
