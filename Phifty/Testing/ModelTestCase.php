<?php

namespace Phifty\Testing;

use Phifty\Bootstrap;
use ConfigKit\ConfigLoader;
use Universal\ClassLoader\Psr4ClassLoader;
use Maghead\Runtime\Config\FileConfigLoader;

abstract class ModelTestCase extends \Maghead\Testing\ModelTestCase
{
    protected $configLoader;

    protected $kernel;

    public function setup()
    {
        $env = getenv("PHIFTY_ENV") ?: 'development';
        $this->configLoader = Bootstrap::createConfigLoader(PH_APP_ROOT, $env);
        $this->kernel = Bootstrap::createKernel($this->configLoader, new Psr4ClassLoader, $env);
        parent::setup();
    }

    /**
     * use the config/database.yml instead of the default config from maghead
     * model test case to avoid side effect.
     */
    public function config()
    {
        return FileConfigLoader::load('config/database.yml');
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
