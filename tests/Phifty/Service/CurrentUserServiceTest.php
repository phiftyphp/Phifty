<?php

class CurrentUserServiceTest extends PHPUnit_Framework_TestCase
{
    function test()
    {
        $kernel = kernel();
        $service = new Phifty\Service\CurrentUserService;
        $service->register( $kernel , array());
        ok( $service );
        ok( $kernel->currentUser );
    }
}

