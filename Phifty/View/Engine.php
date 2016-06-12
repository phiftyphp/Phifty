<?php
namespace Phifty\View;

use Phifty\FileUtils;
use Phifty\Kernel;

abstract class Engine
{
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
}
