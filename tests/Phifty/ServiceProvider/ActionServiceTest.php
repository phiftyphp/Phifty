<?php
use ActionKit\ActionRunner;
use ActionKit\ActionGenerator;
use Pimple\Container;
use Phifty\ServiceProvider\EventServiceProvider;
use Phifty\ServiceProvider\ActionServiceProvider;

class ActionServiceTest extends PHPUnit_Framework_TestCase
{
    public function testActionService()
    {
        $kernel = new Phifty\Kernel;
        $event = new EventServiceProvider;
        $kernel->registerService($event);

        $service = new ActionServiceProvider;
        $kernel->registerService($service);
        ok($kernel->action instanceof ActionRunner);
        ok($kernel->actionRunner instanceof ActionRunner);
        ok($kernel->actionService instanceof Container);
        ok($kernel->actionService['generator'] instanceof ActionGenerator);
    }
}

