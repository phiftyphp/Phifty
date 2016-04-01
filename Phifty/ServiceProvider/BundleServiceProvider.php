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
    public function register($kernel, $options = array())
    {
        // plugin manager depends on classloader,
        // register plugin namespace to classloader.
        $self = $this;
        $kernel->bundles = function() use ($kernel, $self, $options) {
            return new BundleManager($kernel);
        };
    }

}
