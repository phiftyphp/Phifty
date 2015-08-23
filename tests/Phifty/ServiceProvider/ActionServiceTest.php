<?php

class ActionServiceTest extends PHPUnit_Framework_TestCase
{
    public function testActionService()
    {
        $kernel = new Phifty\Kernel;
        ok($kernel);

        $event = new Phifty\ServiceProvider\EventServiceProvider;
        ok($event);
        $kernel->registerService($event);

        $service = new Phifty\ServiceProvider\ActionServiceProvider;
        $kernel->registerService($service);
        ok($service);
        ok($kernel->action instanceof ActionKit\ActionRunner);
    }
}

