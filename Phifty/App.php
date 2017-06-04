<?php

namespace Phifty;

use Funk\Compositor;
use Phifty\Routing\RouteExecutor;
use Phifty\Environment\CommandLine;
use Pux\RouteRequest;

class App extends Bundle implements \PHPSGI\App
{
    /**
     * @override Bundle::boot
     */
    public function boot()
    {
        parent::boot();
        if ($this->kernel->isCli) {
            CommandLine::boot($this->kernel);
        }
        $this->kernel->boot();
    }

    public function __invoke(array & $environment, array $response)
    {
        return $this->call($environment, $response);
    }

    /**
     * @override \PHPSGI\App::call
     */
    public function call(array & $environment, array $response)
    {
        // Extend phifty kernel to the environment
        $environment['phifty.kernel'] = $this->kernel;

        $this->kernel->event->trigger('request.start');
        $request = RouteRequest::createFromEnv($environment);
        if ($route = $this->kernel->mux->dispatchRequest($request)) {
            $response = RouteExecutor::execute($route, $environment, $response, $route);
        } else {
            $response = [
                404,
                ['Content-Type: text/html;'],
                ['<h3>Page not found.</h3>'],
            ];
        }

        $this->kernel->event->trigger('request.end');
        return $response;
    }

    /**
     * Return default PHPSGI application (logics for wrapping application with middlewares)
     *
     * @return callable
     */
    public function toSgi()
    {
        // Here is where you can wrap your app with the middlewares
        // Please note that currently the returned app needs to be called with ::boot method .
        $compositor = new Compositor($this);
        return $compositor->enable(function ($app) {
            return function (array & $environment, array $response) use ($app) {
                $response[1][] = 'P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"';
                $response[1][] = 'X-FRAME-OPTIONS: SAMEORIGIN';
                $response[1][] = 'Pragma: No-cache';
                $response[1][] = 'Cache-Control: no-cache';
                $response[1][] = 'Expires: 0';
                return $app($environment, $response);
            };
        });
        
        /*
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
        */
    }

    static public function getInstance(Kernel $kernel = null, $config = [])
    {
        static $singleton;
        if (!$singleton) {
            $singleton = new static($kernel, $config);
        }
        return $singleton;
    }
}
