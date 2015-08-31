<?php

class RollerRouterServiceProviderTest extends PHPUnit_Framework_TestCase
{
    public function testRouterService()
    {
        if (!class_exists('Roller\\Router', true)) {
            $this->markTestSkipped('roller router is not installed.');
        }

        $kernel = new Phifty\Kernel;
        $service = new Phifty\ServiceProvider\RollerRouterServiceProvider;
        $kernel->registerService($service);
        ok($kernel->router);
        ok($kernel->router instanceof Roller\Router );
    }
}

