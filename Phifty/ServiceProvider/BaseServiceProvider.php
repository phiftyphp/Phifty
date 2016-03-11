<?php
namespace Phifty\ServiceProvider;
use CodeGen\Expr\NewObject;
use Phifty\Kernel;

abstract class BaseServiceProvider implements ServiceProvider
{

    // XXX: should be removed
    public $options;

    protected $config;

    public function __construct(array $config = array())
    {
        $this->config = $config;
    }

    abstract public function getId();

    public function depends() { return []; }

    /**
     * register service
     *
     * XXX: we should set options in constructor
     */
    abstract public function register($kernel, $options = array());

    public static function isGeneratable(Kernel $kernel, array & $options = array())
    {
        return true;
    }

    public static function generatePrepare(Kernel $kernel, array & $options = array())
    {
        return null;
    }


    /**
     * rewriteConfig rewrites the config array passed to the constructor
     *
     * @return array
     */
    public static function rewriteConfig(array $config)
    {
        return $config;
    }


    /**
     * generateNew generates the code for constructing the object
     */
    public static function generateNew(Kernel $kernel, array & $options = array())
    {
        // (PHP 5 >= 5.3.0)
        $className = get_called_class();
        return new NewObject($className, [$options]);
    }
}
