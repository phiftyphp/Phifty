<?php

namespace Phifty\ServiceProvider;

use ActionKit\ActionRunner;
use ActionKit\ActionGenerator;
use Pimple\Container;
use Phifty\ServiceProvider\EventServiceProvider;
use Phifty\ServiceProvider\ActionServiceProvider;
use Phifty\Kernel;

class ActionServiceProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testActionService()
    {
        $kernel = new Kernel;
        $event = new EventServiceProvider;
        $kernel->registerService($event);

        $service = new ActionServiceProvider;
        $kernel->registerService($service);
        $this->assertNotNull($kernel->action instanceof ActionRunner);
        $this->assertNotNull($kernel->actionRunner instanceof ActionRunner);
        $this->assertNotNull($kernel->actionService instanceof Container);
        $this->assertNotNull($kernel->actionService['generator'] instanceof ActionGenerator);
    }
}
