<?php
global $kernel;
global $splClassLoader;
global $composerClassLoader;
$kernel = new \App\AppKernel;
$kernel->registerService(new \Phifty\ServiceProvider\ClassLoaderServiceProvider($splClassLoader));

$configLoader = new \App\AppConfigLoader;
$kernel->registerService(new \Phifty\ServiceProvider\ConfigServiceProvider($configLoader));

// load event service, so that we can bind events in Phifty
$kernel->registerService(new \Phifty\ServiceProvider\EventServiceProvider());

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
    return $kernel;
}
