<?php

namespace Phifty\ServiceProvider;

use Phifty\Testing\TestCase;
use Phifty\Kernel;
use SessionKit\Session;

class SessionServiceProviderTest extends TestCase
{
    public function testSessionServiceProvider()
    {
        $this->markTestSkipped('require session service to support pure PHP array backend.');

        $kernel = Kernel::dynamic($this->configLoader);
        $config = [];
        $provider = new SessionServiceProvider($config);
        $kernel->registerServiceProvider($provider, []);
        $this->assertInstanceOf(Session::class, $kernel->session);
    }
}




