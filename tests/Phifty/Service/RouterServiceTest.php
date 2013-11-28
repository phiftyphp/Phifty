<?php

class RouterServiceTest extends PHPUnit_Framework_TestCase
{
    public function testRouterService()
    {
        $kernel = new Phifty\Kernel;
        ok($kernel);

        $service = new Phifty\Service\RouterService;
        ok($service);
        $kernel->registerService($service);

        ok($kernel->router);
        ok($kernel->router instanceof Roller\Router );
    }
}

