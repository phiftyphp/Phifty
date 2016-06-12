<?php
namespace Phifty\View;
use Phifty\View\Engine;
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

class Twig extends Engine
{
    protected $loader;

    protected $env;

    protected $kernel;

    protected $options = array();

    protected $templateDirs = array();

    private $renderer;

    /*
     * Contructor
     *   template_dirs
     *   cache_dir
     */
    public function __construct(Kernel $kernel, array $options = array())
    {
        $this->kernel = $kernel;
        $this->options = $options;
        if (isset($options['template_dirs'])) {
            $this->templateDirs = (array) $options['template_dirs'];
        }
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
