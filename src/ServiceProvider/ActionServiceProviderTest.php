<?php

namespace Phifty\ServiceProvider;

use WebAction\ActionRunner;
use WebAction\ActionGenerator;
use WebAction\ActionRequest;
use WebAction\Result;

use Pimple\Container;
use Phifty\ServiceProvider\EventServiceProvider;
use Phifty\ServiceProvider\ActionServiceProvider;
use Phifty\Kernel;
use Phifty\Testing\TestCase;
use Phifty\Testing\ModelTestCase;

use UserBundle\Model\UserSchema;
use UserBundle\Model\User;
use UserBundle\Action\CreateUser;

class ActionServiceProviderTest extends ModelTestCase
{


    public function models()
    {
        return [new UserSchema];
    }

    public function testActionService()
    {
        $kernel = Kernel::minimal($this->configLoader);

        $service = new ActionServiceProvider;
        $kernel->registerServiceProvider($service);
        $this->assertNotNull($kernel->action instanceof ActionRunner);
        $this->assertNotNull($kernel->actionRunner instanceof ActionRunner);
        $this->assertNotNull($kernel->actionService instanceof Container);
        $this->assertNotNull($kernel->actionService['generator'] instanceof ActionGenerator);

        $result = $kernel->actionRunner->run("UserBundle::Action::CreateUser", new ActionRequest([
            'account' => 'timcook',
            'password' => 'cooktim',
            'email' => 'timcook@apple.com',
        ]));

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals('success', $result->type);
    }
}
