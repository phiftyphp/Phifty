<?php

namespace Phifty\ServiceProvider;

use Phifty\Testing\TestCase;
use Phifty\Kernel;
use Twig_Environment;
use Twig_LoaderInterface;

class TwigServiceProviderTest extends TestCase
{
    public function testRegisterTwigService()
    {
        $kernel = Kernel::minimal($this->configLoader);

        $config = [
            'Environment' => [
                'debug' => true,
                'cache' => 'cache/path',
                'autoload' => 'auto_reload',
            ],
            'TemplateDirs' => ['app','bundles'],
        ];
        $config = TwigServiceProvider::canonicalizeConfig($kernel, $config);
        $twig = new TwigServiceProvider($config);

        $twig->register($kernel, array(
            'Environment' => array(
                'debug' => true,
                'cache' => 'cache/path',
                'autoload' => 'auto_reload',
            ),
            'TemplateDirs' => array('app','bundles'),
        ));
        $this->assertNotNull($kernel->twig);
        $this->assertInstanceOf(Twig_Environment::class, $kernel->twig->env, 'get twig environment');
        $this->assertInstanceOf(Twig_LoaderInterface::class, $kernel->twig->loader, 'get twig loader');
    }
}
