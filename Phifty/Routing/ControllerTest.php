<?php

namespace Phifty\Routing;

use Phifty\Testing\TestCase;
use Funk\Testing\TestUtils;
use Pux\Mux;

class TestController extends Controller
{
    public function fooAction()
    {
        return $this->toYaml([
            'foo' => 1,
            'bar' => [ 'zoo' => 123 ],
        ]);
    }

    public function viewAction()
    {
        $view = $this->view();
    }
}

class ControllerTest extends TestCase
{
    public function testRenderYaml()
    {
        $environment = $this->createEnvironment('GET', '/foo', [  ]);

        $controller = new TestController;
        $mux = $controller->expand();
        $route = $mux->dispatch('/foo');

        $environment['pux.route'] = $route;
        $environment['pux.controller_action'] = 'fooAction';

        $response = $controller->call($environment, []);
        $this->assertNotEmpty($response);
    }

    public function testViewFactory()
    {
        $environment = $this->createEnvironment('GET', '/view', [  ]);

        $controller = new TestController;
        $mux = $controller->expand();
        $route = $mux->dispatch('/view');

        $environment['pux.route'] = $route;
        $environment['pux.controller_action'] = 'viewAction';
        $response = $controller->call($environment, []);

        $view = $controller->view();
        $this->assertInstanceOf(View::class, $view);

        $view = $controller->createView();
        $this->assertInstanceOf(View::class, $view);
    }

}
