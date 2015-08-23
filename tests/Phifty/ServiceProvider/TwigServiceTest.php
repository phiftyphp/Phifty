<?php

class TwigServiceTest extends PHPUnit_Framework_TestCase
{
    public function testRegisterTwigService()
    {
        $kernel = kernel();
        $twig = new \Phifty\ServiceProvider\TwigServiceProvider();
        $twig->register($kernel, array(
            'Environment' => array(
                'debug' => true,
                'cache' => 'cache/path',
                'autoload' => 'auto_reload',
            ),
            'TemplateDirs' => array('applications','bundles'),
        ));
        $this->assertNotNull($kernel->twig);
        $this->assertNotNull($kernel->twig->env,'get environment');
        $this->assertNotNull($kernel->twig->loader,'get loader');
    }
}

