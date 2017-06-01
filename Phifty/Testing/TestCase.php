<?php

namespace Phifty\Testing;

use Phifty\Bootstrap;
use ConfigKit\ConfigLoader;
use Universal\ClassLoader\Psr4ClassLoader;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected $configLoader;

    protected $kernel;

    public function setup()
    {
        $configLoader = new ConfigLoader;
        $configLoader->load('framework', "config/framework.yml");

        if (file_exists('config/application.yml')) {
            $configLoader->load('application', "config/application.yml");
        }
        if (file_exists('config/testing.yml')) {
            $configLoader->load('testing', "config/testing.yml");
        }

        $this->configLoader = $configLoader;

        // TODO: load Kernel from the generated app (global).
        $this->kernel = Bootstrap::createKernel($configLoader, new Psr4ClassLoader);
    }

    protected function createPostRequest($path, $parameters)
    {
        return [
            'PATH_INFO' => $path,
            'parameters' => $parameters,
            'body_parameters' => $parameters,
            'query_parameters' => $parameters,
            '_SESSION' => [],
            '_COOKIE' => [],
        ];
    }

}
