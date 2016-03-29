<?php
namespace Phifty\View;

use Phifty\FileUtils;
use Phifty\Kernel;

abstract class Engine
{
    public $kernel;
    public $options = array();
    public $templateDirs = array();

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

    /*
     * Method for creating new renderer object
     */
    abstract public function newRenderer();

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

    /* refactor to Phifty\View\Smarty and Phifty\View\Twig */
    public static function createEngine(Kernel $kernel, array $options = array())
    {
        return new \Phifty\View\Twig($kernel, $options);
    }
}
