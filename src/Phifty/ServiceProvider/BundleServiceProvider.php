<?php
namespace Phifty\ServiceProvider;
use Phifty\BundleManager;
use Phifty\Kernel;
use ConfigKit\ConfigAccessor;

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


    static public function isGeneratable(Kernel $kernel, array & $options = array())
    {
        $config = $kernel->config->get('framework','Bundles');
        return $config && (
            (
                $config instanceof ConfigAccessor && !$config->isEmpty()) 
                    || (is_array($config) && !empty($config)
            )
        );
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
            foreach ($config as $bundleName => $bundleConfig) {
                if ($bundle = $manager->load($bundleName, $bundleConfig)) {
                    $dir = $bundle->locate();
                    $manager->registerBundleDir($dir);
                }
            }
            return $manager;
        };
    }

}
