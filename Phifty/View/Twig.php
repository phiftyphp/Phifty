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
    protected $loader;

    protected $env;

    protected $kernel;

    protected $options = array();

    private $renderer;

    /*
     * Contructor
     *   TemplateDirs
     */
    public function __construct(Kernel $kernel, array $options = array())
    {
        $this->kernel = $kernel;
        $this->options = $options;
    }

    public function newRenderer()
    {
        $this->env = $this->kernel->twig->env;
        $this->loader = $this->kernel->twig->loader;
        return $this->env;
    }

    /*
     * Return Renderer object, statical
     */
    public function getRenderer()
    {
        if ($this->renderer) {
            return $this->renderer;
        }
        return $this->renderer = $this->newRenderer();
    }

    public function render($template, array $args = array())
    {
        $env = $this->kernel->twig->env;
        return $env->loadTemplate($template)->render($args);
    }
}
