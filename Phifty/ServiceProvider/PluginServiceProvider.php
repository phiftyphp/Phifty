<?php

namespace Phifty\ServiceProvider;

use Phifty\Plugin\PluginManager;
use Phifty\Kernel;

class PluginServiceProvider extends BaseServiceProvider
{
    public function getId()
    {
        return 'Plugin';
    }

    /**
     * @param Phifty\Kernel $kernel  Kernel object.
     * @param array         $options Plugin service options.
     */
    public function register(Kernel $kernel, array $options = array())
    {
        // here we check plugins stash to decide what to load.
        $config = $kernel->config->get('framework', 'Plugins');
        if (!$config || $config->isEmpty()) {
            return;
        }

        // plugin manager depends on classloader,
        // register plugin namespace to classloader.
        $manager = PluginManager::getInstance();
        $kernel->plugins = function () use ($manager) {
            return $manager;
        };

        // default plugin paths
        if (PH_APP_ROOT !== PH_ROOT) {
            $manager->registerPluginDir($kernel->rootBundleDir);
        }
        $manager->registerPluginDir($kernel->frameworkBundleDir);

        if (isset($options['Dirs'])) {
            foreach ($options['Dirs'] as $dir) {
                $manager->registerPluginDir($dir);
            }
        }

        foreach ($config as $pluginName => $config) {
            $kernel->classloader->addNamespace(array(
                $pluginName => array(
                    $kernel->rootBundleDir,
                    $kernel->frameworkBundleDir,
                ),
            ));
            $manager->load($pluginName, $config);
        }

        // initialize all loaded plugin
        $manager->init();
    }
}
