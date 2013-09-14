<?php
namespace Phifty;
use ReflectionClass;

class ClassUtils
{
    public static function new_class( $class , $args = null )
    {
        if ($args) {
            $rc = new ReflectionClass( $class );
            // return $rc->newInstance();
            return $rc->newInstanceArgs( $args );
        } else {
            return new $class;
        }
    }

}
