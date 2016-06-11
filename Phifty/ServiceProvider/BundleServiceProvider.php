<?php
namespace Phifty\ServiceProvider;
use Phifty\Bundle\BundleManager;
use Phifty\Kernel;
use ConfigKit\Accessor;
use CodeGen\Block;
use CodeGen\Statement\Statement;
use CodeGen\Statement\RequireStatement;
use CodeGen\Expr\MethodCall;

class BundleServiceProvider extends BaseServiceProvider
{

    public function getId() { return 'Bundle'; }

    static public function generateNew(Kernel $kernel, array & $options = array())
    {
        return parent::generateNew($kernel, $options);
    }

    static public function canonicalizeConfig(Kernel $kernel, array $options)
    {
        if (isset($options["Paths"])) {
            $options["Paths"] = array_map('realpath', $options["Paths"]);
        }
        return $options;
    }


    /**
     *
     * @param Phifty\Kernel $kernel  Kernel object.
     * @param array         $options Plugin service options.
     */
    public function register(Kernel $kernel, $options = array())
    {
        // plugin manager depends on classloader,
        // register plugin namespace to classloader.
        $kernel->bundles = function() use ($kernel, $options) {
            return new BundleManager($kernel);
        };
    }

}
