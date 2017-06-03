<?php

namespace Phifty\ServiceProvider;

use Phifty\Testing\TestCase;
use Phifty\Kernel;

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
        $this->assertNotNull($kernel->twig->env, 'get environment');
        $this->assertNotNull($kernel->twig->loader, 'get loader');
    }
}
