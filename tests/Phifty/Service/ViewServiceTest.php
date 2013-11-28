<?php

class ViewServiceTest extends PHPUnit_Framework_TestCase
{
    public function testTwigViewService()
    {
        $kernel = new Phifty\Kernel;
        ok($kernel,'got kernel');

        $service = new Phifty\Service\ViewService;
        ok($service);
        $kernel->registerService($service,array(
            'Backend' => 'twig',
            'Class' => 'Phifty\\View',
            'TemplateDirs' => 'bundles/CoreBundle/template',
        ));

        $view = $kernel->view;
        ok($view);
        ok($view instanceof Phifty\View);

        $adminUIView = $kernel->getObject('view', array('AdminUI\\View'));
        ok($adminUIView);
    }
}

