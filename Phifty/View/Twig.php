<?php
namespace Phifty\View;
use Phifty\ClassUtils;
use Phifty\Kernel;
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

class Twig
{
    protected $env;

    protected $kernel;

    private $renderer;

    /*
     * Contructor
     *   TemplateDirs
     */
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /*
     * Return Renderer object, statical
     */
    public function getRenderer()
    {
        return $this->kernel->twig->env;
    }

    public function render($template, array $args = array())
    {
        $env = $this->kernel->twig->env;
        return $env->loadTemplate($template)->render($args);
    }
}
