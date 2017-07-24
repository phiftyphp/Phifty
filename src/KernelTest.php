<?php

namespace Phifty;

use Phifty\Testing\TestCase;
use Phifty\Environment\Development;

class KernelTest extends TestCase
{
    public function testKernel()
    {
        $kernel = Kernel::dynamic($this->configLoader, new Development);
        $this->assertNotNull($kernel);
        $this->assertFileExists($kernel->webroot);
        $this->assertFileExists($kernel->rootDir);
    }
}

