<?php
namespace Phifty\View;
use Phifty\View\Engine;
use Phifty\ClassUtils;
use Twig_Environment;

/**
 * Rewrite this as an extension.
 *
 * {% set obj = new('InputSystem\\Model\\Patient') %}
 * {% set obj = new('InputSystem\\Model\\PatientSchema') %}
 */
function newObject($class)
{
    $args = func_get_args();
    array_shift($args);
    return ClassUtils::new_class($class,$args);
}




class Twig extends Engine
//    implements \Phifty\View\EngineInterface
{
    public $loader;
    public $env;

    public function newRenderer()
    {
        $kernel = kernel();
        $this->env = $kernel->twig->env;
        $this->loader = $kernel->twig->loader;
        return $this->env;
    }

    public function render( $template,$args = array() )
    {
        return $this->getRenderer()->loadTemplate( $template )->render( $args );
    }

    public function display( $template , $args = array() )
    {
        $this->getRenderer()->loadTemplate( $template )->display($args);
    }
}
