<?php

class TwigServiceTest extends PHPUnit_Framework_TestCase
{
    public function testRegister()
    {
        $kernel = kernel();
        $twig = new Phifty\Service\TwigService();
        $twig->register($kernel, array(
            'Environment' => array(
                'debug' => true,
                'cache' => 'cache/path',
                'autoload' => 'auto_reload',
            ),
            'TemplateDirs' => array('applications','bundles'),
        ));
        ok($twig);
        ok($kernel->twig);
        ok($kernel->twig->env,'get environment');
        ok($kernel->twig->loader,'get loader');
    }
}

