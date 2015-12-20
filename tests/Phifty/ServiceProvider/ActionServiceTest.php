<?php
use ActionKit\ActionRunner;
use ActionKit\ActionGenerator;
use Pimple\Container;

class ActionServiceTest extends PHPUnit_Framework_TestCase
{
    public function testActionService()
    {
        $kernel = new Phifty\Kernel;
        $event = new Phifty\ServiceProvider\EventServiceProvider;
        $kernel->registerService($event);

        $service = new Phifty\ServiceProvider\ActionServiceProvider;
        $kernel->registerService($service);
        ok($kernel->action instanceof ActionRunner);
        ok($kernel->actionRunner instanceof ActionRunner);
        ok($kernel->actionService instanceof Container);
        ok($kernel->actionService['generator'] instanceof ActionGenerator);
    }
}

