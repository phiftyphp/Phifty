<?php

class ViewServiceTest extends PHPUnit_Framework_TestCase
{
    public function testTwigViewService()
    {
        $kernel = new Phifty\Kernel;
        $service = new Phifty\Service\ViewService;
        $kernel->registerService($service,array(
            'Backend' => 'twig',
            'Class' => 'Phifty\\View',
            'TemplateDirs' => 'bundles/CoreBundle/template',
        ));

        $view = $kernel->view;
        $this->assertNotNull($view);
        $this->assertInstanceOf('Phifty\View', $view);
        // $adminUIView = $kernel->getObject('view', array('AdminUI\\View'));
        // ok($adminUIView);
    }
}

