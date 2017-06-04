<?php

namespace Phifty\Testing;

trait EnvironmentFactory
{
    public function createEnvironment($method, $path, array $parameters = [])
    {
        $env = [
            // framework variables
            'phifty.kernel'  => $this->kernel,

            // general variables
            'SCRIPT_NAME' => 'index.php',
            'REQUEST_METHOD' => $method,
            'PATH_INFO'      => $path,
        ];
        $env['_GET']     = [];
        $env['_POST']    = [];

        $env['_REQUEST'] = $parameters;
        $env['parameters'] = $parameters;

        if ('GET' === strtolower($method)) {
            $env['_GET'] = $parameters;
            $env['query_parameters'] = $parameters;

            // rebuild the request_url from query parameters
            $env['REQUEST_URI'] = $path . '?' . http_build_query($parameters);

        } else if ('POST' === strtolower($method)) {
            $env['_POST'] = $parameters;
            $env['body_parameters'] = $parameters;

            $env['REQUEST_URI'] = $path;
        }

        $env['_COOKIE']     = [];
        $env['_SESSION']    = [];

        // fallback (backware compatible for $GLOBALS)
        $env['_SERVER']     = [];
        return $env;
    }
}
