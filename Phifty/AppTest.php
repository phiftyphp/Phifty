<?php

namespace Phifty;

use Universal\ClassLoader\Psr4ClassLoader;
use Funk\Environment;
use Phifty\Testing\TestCase;

class AppTest extends TestCase
{
    public function testBuild()
    {
        $app = new App($this->kernel, []);
        $this->assertNotNull($app);
        $app->boot();
        return $app;
    }

    /**
     * @depends testBuild
     */
    public function testCall($app)
    {
        $environment = $this->createEnvironment('GET', '/', []);
        $response = [];
        $sgi = $app->toSgi();
        $response = $sgi($environment, $response);
        $this->assertNotNull($response);
    }
}
