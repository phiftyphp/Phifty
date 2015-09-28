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
    if ($appconfigs = $kernel->config->get('framework', 'Applications')) {
        foreach ($appconfigs as $appname => $appconfig) {
            $kernel->classloader->addNamespace(array(
                $appname => array(PH_APP_ROOT.'/applications', PH_ROOT.'/applications'),
            ));
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
