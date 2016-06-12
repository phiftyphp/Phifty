<?php
namespace Phifty;
use ReflectionClass;

class ClassUtils
{
    public static function newClass($class , $args = null )
    {
        $rc = new ReflectionClass($class);
        if ($args) {
            return $rc->newInstanceArgs($args);
        }
        return $rc->newInstance();
    }

}
