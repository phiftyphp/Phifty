<?php
namespace Phifty\ServiceProvider;
use Phifty\BundleManager;
use Phifty\Kernel;
use ConfigKit\Accessor;
use CodeGen\Block;
use CodeGen\Statement\Statement;
use CodeGen\Statement\RequireStatement;
use CodeGen\Expr\MethodCall;

class BundleServiceProvider extends BaseServiceProvider
{

    public function getId() { return 'Bundle'; }

    public static function generatePrepare(Kernel $kernel, array & $options = array())
    {
        $prepareBlock = new Block;
        $bundleDirs = [];
        if (isset($options["Paths"])) {
            $bundleDirs = array_map('realpath', $options["Paths"]);
        }
        $bundleList = $kernel->config->get('framework','Bundles');
        // var_dump($bundleList);
        foreach ($bundleList as $bundleName => $bundleConfig) {
            foreach ($bundleDirs as $bundleDir) {
                $bundlePath = $bundleDir . DIRECTORY_SEPARATOR . $bundleName;
                if (is_dir($bundlePath)) {
                    // if the bundle directory exists, we can generate class loaderr path.
                    $prepareBlock[] = new Statement(new MethodCall('$composerClassLoader', 'addPsr4', [
                        $bundleName . '\\',
                        $bundlePath . DIRECTORY_SEPARATOR,
                    ]));

                    // If the bundle main class exists, just require it.
                    $bundleClassPath = $bundlePath . DIRECTORY_SEPARATOR . $bundleName . '.php';
                    if (file_exists($bundleClassPath)) {
                        $prepareBlock[] = new RequireStatement($bundleClassPath);
                    }
                    break;
                }
            }
        }
        return $prepareBlock;
    }

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
                $config instanceof \ConfigKit\Accessor && !$config->isEmpty())
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

        if ($config) {
            foreach ($config as $bundleName => $bundleConfig) {
                $kernel->classloader->addNamespace(array(
                    $bundleName => $this->config["Paths"],
                ));
            }
        }

        // plugin manager depends on classloader,
        // register plugin namespace to classloader.
        $self = $this;
        $kernel->bundles = function() use ($self, $manager, $config, $options) {
            if ($config) {
                foreach ($config as $bundleName => $bundleConfig) {
                    if ($bundle = $manager->load($bundleName, $bundleConfig)) {
                        $dir = $bundle->locate();
                        $manager->registerBundleDir($dir);
                    }
                }
            }
            return $manager;
        };
    }

}
