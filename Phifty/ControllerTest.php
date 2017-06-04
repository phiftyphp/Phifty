<?php

namespace Phifty;

use Phifty\Testing\TestCase;
use Funk\Testing\TestUtils;

class ControllerTest extends TestCase
{
    public function testRenderYaml()
    {
        $environment = $this->createEnvironment('GET', '/', [  ]);
        $controller = new Controller($environment, []);
        $yaml = $controller->toYaml([
            'foo' => 1,
            'bar' => [ 'zoo' => 123 ],
        ]);
        $this->assertNotEmpty($yaml);
    }

    public function testViewFactory()
    {
        $environment = $this->createEnvironment('GET', '/', [  ]);
        $controller = new Controller($environment, []);
        $view = $controller->view();
        $this->assertInstanceOf(View::class, $view);

        $view = $controller->createView();
        $this->assertInstanceOf(View::class, $view);
    }
}
