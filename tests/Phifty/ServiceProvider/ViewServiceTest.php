<?php

class ViewServiceTest extends PHPUnit_Framework_TestCase
{
    public function testBaseView()
    {
        $kernel = new Phifty\Kernel;
        $service = new Phifty\ServiceProvider\ViewServiceProvider;
        $kernel->registerService($service,array(
            'Backend' => 'twig',
            'Class' => 'Phifty\\View',
            'TemplateDirs' => 'bundles/CoreBundle/template',
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
            'Backend' => 'twig',
            'Class' => 'Phifty\\View\\Page',
            'TemplateDirs' => 'bundles/CoreBundle/template',
        ));

        $view = $kernel->view;
        $this->assertNotNull($view);
        $this->assertInstanceOf('Phifty\View\Page', $view);
    }

}

