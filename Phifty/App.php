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
        // Here is where you can wrap your app with the middlewares
        /*
            $compositor = new Compositor;
            $compositor->enable(TryCatchMiddleware::class, [ 'throw' => true ]);
            $compositor->enable(function($app) {
                return function(array & $environment, array $response) use ($app) { 
                    $environment['middleware.app'] = true;
                    return $app($environment, $response);
                };
            });

            $compositor->app(function(array & $environment, array $response) {
                $request = RouteRequest::createFromEnv($environment);
                if ($request->pathStartWith('/foo')) {

                }

                $response[0] = 200;
                return $response;
            });

            $response = $compositor($env, []);
        */
        return new static($kernel, $config);
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
