<?php

namespace Phifty;

use PHPUnit\Framework\TestCase;
use Universal\ClassLoader\Psr4ClassLoader;
use Funk\Environment;

class AppTest extends TestCase
{
    public function testBuild()
    {
        $configLoader = Bootstrap::createConfigLoader(PH_APP_ROOT);
        $classLoader = new Psr4ClassLoader;
        $kernel = Bootstrap::createKernel($configLoader, $classLoader, 'development');
        $app = new App($kernel, []);
        $this->assertNotNull($app);
        $app->boot();
        return $app;
    }

    /**
     * @depends testBuild
     */
    public function testCall($app)
    {
        $environment = [
            'PATH_INFO' => '/',
            'parameters' => [],
            'queryParameter' => [],
            '_SESSION' => [],
            '_COOKIE' => [],
        ];
        $response = [];
        $sgi = $app->toSgi();
        $response = $sgi($environment, $response);
        $this->assertNotNull($response);
    }
}
