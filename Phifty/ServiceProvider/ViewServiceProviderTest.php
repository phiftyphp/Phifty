<?php

namespace Phifty\ServiceProvider;

use Phifty\Kernel;
use Phifty\Testing\TestCase;

class ViewServiceTest extends TestCase
{
    public function testBaseView()
    {
        $kernel = Kernel::minimal($this->configLoader);

        $service = new ViewServiceProvider;
        $kernel->registerServiceProvider($service, [
            'Class' => 'Phifty\\View',
        ]);
        $kernel->boot();

        $view = $kernel->view;
        $this->assertNotNull($view);
        $this->assertInstanceOf('Phifty\View', $view);
    }

    public function testPageView()
    {
        $kernel = Kernel::minimal($this->configLoader);
        $service = new ViewServiceProvider;
        $kernel->registerServiceProvider($service, [
            'Class' => 'Phifty\\View\\Page',
        ]);
        $kernel->boot();

        $view = $kernel->view;
        $this->assertNotNull($view);
        $this->assertInstanceOf('Phifty\View\Page', $view);
    }
}
