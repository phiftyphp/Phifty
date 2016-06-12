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
    return ClassUtils::newClass($class,$args);
}

class Twig extends Engine
{
    protected $loader;

    protected $env;

    public function newRenderer()
    {
        $this->env = $this->kernel->twig->env;
        $this->loader = $this->kernel->twig->loader;
        return $this->env;
    }

    public function render($template, array $args = array())
    {
        return $this->getRenderer()->loadTemplate($template)->render($args);
    }
}
