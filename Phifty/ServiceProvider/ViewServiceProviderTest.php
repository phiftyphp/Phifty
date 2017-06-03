<?php

namespace Phifty\ServiceProvider;

use Phifty\Kernel;
use Phifty\Testing\TestCase;

class ViewServiceTest extends TestCase
{
    public function testBaseView()
    {
        $kernel = Kernel::dynamic($this->configLoader);
        $service = new ViewServiceProvider;
        $kernel->registerServiceProvider($service, [
            'Class' => 'Phifty\\View',
        ]);

        $view = $kernel->view;
        $this->assertNotNull($view);
        $this->assertInstanceOf('Phifty\View', $view);
    }

    public function testPageView()
    {
        $kernel = Kernel::dynamic($this->configLoader);
        $service = new ViewServiceProvider;
        $kernel->registerServiceProvider($service, array(
            'Class' => 'Phifty\\View\\Page',
        ));

        $view = $kernel->view;
        $this->assertNotNull($view);
        $this->assertInstanceOf('Phifty\View\Page', $view);
    }
}
