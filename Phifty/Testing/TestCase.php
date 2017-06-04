<?php

namespace Phifty\Testing;

use Phifty\Bootstrap;
use ConfigKit\ConfigLoader;
use Universal\ClassLoader\Psr4ClassLoader;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    use EnvironmentFactory;

    protected $configLoader;

    protected $kernel;

    public function setup()
    {
        $env = getenv("PHIFTY_ENV") ?: 'development';
        $this->configLoader = Bootstrap::createConfigLoader(PH_APP_ROOT, $env);
        $this->kernel = Bootstrap::createKernel($this->configLoader, new Psr4ClassLoader, $env);
    }

}
