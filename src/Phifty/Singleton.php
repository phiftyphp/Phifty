<?php

namespace Phifty;

class Singleton
{
    /* seems AppKernel use the same $self */
    public static function getInstance()
    {
        static $instance;
        if ( $instance )

            return $instance;
        $class = get_called_class();
        # echo "new $class\n";

        return $instance = new $class;
    }

    /* alias of getInstance() */
    public static function one()
    {
        return static::getInstance();
    }
}
