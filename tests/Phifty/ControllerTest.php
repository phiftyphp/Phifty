<?php
use Phifty\Controller;

class ControllerTest extends \PHPUnit\Framework\TestCase
{
    public function testRenderYaml()
    {
        $controller = new Controller;
        $yaml = $controller->toYaml([ 
            'foo' => 1,
            'bar' => [ 'zoo' => 123 ],
        ]);
        $this->assertNotEmpty($yaml);
    }

    public function testViewFactory()
    {
        $controller = new Controller;
        $view = $controller->view();
        $this->assertInstanceOf('Phifty\View', $view);

        $view = $controller->createView();
        $this->assertInstanceOf('Phifty\View', $view);
    }
}

