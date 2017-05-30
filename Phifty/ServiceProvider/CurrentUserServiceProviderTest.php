<?php

namespace Phifty\ServiceProvider;

use Phifty\Kernel;

class CurrentUserProviderServiceTest extends \PHPUnit\Framework\TestCase
{
    public function testCurrentUserService()
    {
        $kernel = new Kernel;
        $kernel->registerService(new EventServiceProvider, []);

        $config = [
            'Class' => \Phifty\Security\CurrentUser::class,
            'Model' => \UserBundle\Model\User::class,
        ];

        $config = CurrentUserServiceProvider::canonicalizeConfig($kernel, $config);

        $service = new CurrentUserServiceProvider;
        $service->register($kernel , $config);

        $this->assertNotNull($service);
        $this->assertNotNull($kernel->currentUser);
    }
}

