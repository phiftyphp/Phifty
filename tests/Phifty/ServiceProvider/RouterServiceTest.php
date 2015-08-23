<?php

class RouterServiceTest extends PHPUnit_Framework_TestCase
{
    public function testRouterService()
    {
        $kernel = new Phifty\Kernel;
        $service = new Phifty\ServiceProvider\RouterServiceProvider;
        $kernel->registerService($service);
        ok($kernel->router);
        ok($kernel->router instanceof Roller\Router );
    }
}

