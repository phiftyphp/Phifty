<?php

class ViewServiceTest extends PHPUnit_Framework_TestCase
{
    public function testBaseView()
    {
        $kernel = new Phifty\Kernel;
        $service = new Phifty\ServiceProvider\ViewServiceProvider;
        $kernel->registerService($service,array(
            'Class' => 'Phifty\\View',
        ));

        $view = $kernel->view;
        $this->assertNotNull($view);
        $this->assertInstanceOf('Phifty\View', $view);
    }

    public function testPageView()
    {
        $kernel = new Phifty\Kernel;
        $service = new Phifty\ServiceProvider\ViewServiceProvider;
        $kernel->registerService($service,array(
            'Class' => 'Phifty\\View\\Page',
        ));

        $view = $kernel->view;
        $this->assertNotNull($view);
        $this->assertInstanceOf('Phifty\View\Page', $view);
    }

}

