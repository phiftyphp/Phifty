<?php
namespace Phifty\ServiceProvider;
use Phifty\BundleManager;
use Phifty\Kernel;

class BundleServiceProvider extends BaseServiceProvider
{

    public function getId() { return 'Bundle'; }


    static public function generateNew(Kernel $kernel, array & $options = array())
    {
        if (isset($options["Paths"])) {
            $options["Paths"] = array_map('realpath', $options["Paths"]);
        }
        return parent::generateNew($kernel, $options);
    }

    /**
     *
     * @param Phifty\Kernel $kernel  Kernel object.
     * @param array         $options Plugin service options.
     */
    public function register($kernel, $options = array() )
    {
        // here we check bundles stash to decide what to load.
        $config = $kernel->config->get('framework','Bundles');
        if ( ! $config || $config->isEmpty() ) {
            return;
        }

        $manager = new BundleManager($kernel);
        foreach ($config as $bundleName => $bundleConfig) {
            $kernel->classloader->addNamespace(array(
                $bundleName => $this->config["Paths"],
            ));
        }

        // plugin manager depends on classloader,
        // register plugin namespace to classloader.
        $self = $this;
        $kernel->bundles = function() use ($self, $manager, $config, $options) {
            $paths = array();
            if (isset($self->options["Paths"])) {
                foreach ($self->options["Paths"] as $dir) {
                    $manager->registerBundleDir($dir);
                }
            }
            foreach ($config as $bundleName => $bundleConfig) {
                $manager->load($bundleName, $bundleConfig);
            }
            return $manager;
        };
    }

}
