<?php

namespace Phifty;

use Funk\Compositor;

class App extends Bundle implements \PHPSGI\App
{

    public function call(array & $environment, array $response)
    {
        return $response;
    }

    /**
     * The default PHPSGI application builder (logics for wrapping application with middlewares)
     *
     * @return callable
     */
    static public function build(Kernel $kernel, array $config)
    {
        return new static($kernel, $config);
        /*
        $compositor = new Compositor(new static);
        return $compositor->wrap();
        */
    }

    static public function getInstance(Kernel $kernel = null, $config = [])
    {
        static $singleton;
        if (!$singleton) {
            $singleton = static::build($kernel, $config);
        }
        return $singleton;
    }
}
