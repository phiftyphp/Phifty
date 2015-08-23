<?php

class CurrentUserServiceTest extends PHPUnit_Framework_TestCase
{
    public function testCurrentUserService()
    {
        $kernel = kernel();
        $service = new Phifty\ServiceProvider\CurrentUserServiceProvider;
        $service->register( $kernel , array());
        ok($service);
        ok($kernel->currentUser);
    }
}

