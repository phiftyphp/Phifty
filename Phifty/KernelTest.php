<?php

namespace Phifty;

use Phifty\Testing\TestCase;

class KernelTest extends TestCase
{
    public function testKernel()
    {
        $kernel = new \App\AppKernel($this->configLoader);
        $this->assertNotNull($kernel);
        $this->assertFileExists($kernel->webroot);
        $this->assertFileExists($kernel->rootDir);
    }
}

