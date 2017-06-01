<?php

namespace Phifty;

use Phifty\Testing\TestCase;

class KernelTest extends TestCase
{
    public function testKernel()
    {
        $kernel = Kernel::dynamic($this->configLoader, 'development');
        $this->assertNotNull($kernel);
        $this->assertFileExists($kernel->webroot);
        $this->assertFileExists($kernel->rootDir);
    }
}

