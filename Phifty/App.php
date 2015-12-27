<?php
namespace Phifty;
use Funk\Compositor;

class App implements \Funk\App
{

    public function call(array $environment, array $response)
    {
        return $response;
    }


    /**
     * The default PHPSGI application builder (logics for wrapping application with middlewares)
     *
     * @return callable
     */
    static public function build()
    {
        return new static;
        /*
        $compositor = new Compositor(new static);
        return $compositor->wrap();
        */
    }
}



