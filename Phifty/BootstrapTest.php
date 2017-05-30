<?php

namespace Phifty;

use PHPUnit\Framework\TestCase;
use ConfigKit\ConfigLoader;
use Universal\ClassLoader\Psr4ClassLoader;

class BootstrapTest extends TestCase
{
    public function testCreateConfigLoader()
    {
        $configLoader = Bootstrap::createConfigLoader(PH_APP_ROOT);
        $this->assertInstanceOf(ConfigLoader::class, $configLoader);
        return $configLoader;
    }

    /**
     * @depends testCreateConfigLoader
     */
    public function testCreateRuntimeKernel($configLoader)
    {
        $classLoader = new Psr4ClassLoader;
        $kernel = Bootstrap::createRuntimeKernel($configLoader, $classLoader);
        $this->assertInstanceOf(Kernel::class, $kernel);
    }
}
