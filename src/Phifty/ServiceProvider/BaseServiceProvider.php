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

    /**
     * register service
     *
     * XXX: we should set options in constructor
     */
    abstract public function register($kernel, $options = array());

    public static function generateNew(Kernel $kernel, $args)
    {
        // (PHP 5 >= 5.3.0)
        $className = get_called_class();
        return new NewObject($className, $args);
    }
}
