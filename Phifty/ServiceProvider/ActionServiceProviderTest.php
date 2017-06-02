<?php

namespace Phifty\ServiceProvider;

use ActionKit\ActionRunner;
use ActionKit\ActionGenerator;
use Pimple\Container;
use Phifty\ServiceProvider\EventServiceProvider;
use Phifty\ServiceProvider\ActionServiceProvider;
use Phifty\Kernel;
use Phifty\Testing\TestCase;

class ActionServiceProviderTest extends TestCase
{
    public function testActionService()
    {
        $kernel = Kernel::dynamic($this->configLoader);
        $event = new EventServiceProvider;
        $kernel->registerServiceProvider($event);

        $service = new ActionServiceProvider;
        $kernel->registerServiceProvider($service);
        $this->assertNotNull($kernel->action instanceof ActionRunner);
        $this->assertNotNull($kernel->actionRunner instanceof ActionRunner);
        $this->assertNotNull($kernel->actionService instanceof Container);
        $this->assertNotNull($kernel->actionService['generator'] instanceof ActionGenerator);
    }
}
