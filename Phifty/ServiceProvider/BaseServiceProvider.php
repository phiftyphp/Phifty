<?php

namespace Phifty\ServiceProvider;

use CodeGen\Expr\NewObject;
use Phifty\Kernel;
use Closure;

abstract class BaseServiceProvider implements ServiceProvider
{
    abstract public function getId();


    public function boot(Kernel $kernel)
    {

    }

    /**
     * Register this service provider.
     *
     * XXX: we should set options in constructor
     */
    abstract public function register(Kernel $kernel, $options = array());

    /**
     * See if this service supports code generator.
     *
     * @param Kernel $kernel
     * @param array  $options
     *
     * @return bool
     */
    public static function Generatable(Kernel $kernel, array $options = array())
    {
        return true;
    }

    /**
     * generatePrepare generates the statements before registering the service.
     *
     * @param Kernel $kernel
     * @param array  $options
     *
     * @return null|array
     */
    public static function generatePrepare(Kernel $kernel, array &$options = array())
    {
        return;
    }

    /**
     * canonicalizeConfig canonialize the config array before genearting the config array.
     *
     * @return array
     */
    public static function canonicalizeConfig(Kernel $kernel, array $options)
    {
        return $options;
    }

    /**
     * generateNew generates the code for constructing the object.
     */
    public static function generateNew(Kernel $kernel, array &$options = array())
    {
        // (PHP 5 >= 5.3.0)
        $className = get_called_class();
        // Use late static binding to call the canonicalizeConfig from different instance.
        return new NewObject($className, []);
    }
}
