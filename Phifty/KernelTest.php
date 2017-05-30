<?php

namespace Phifty;

class KernelTest extends \PHPUnit\Framework\TestCase
{
    public function testKernel()
    {
        $kernel = new \App\AppKernel;
        $this->assertNotNull($kernel);
        $this->assertFileExists($kernel->webroot);
        $this->assertFileExists($kernel->rootDir);
    }
}

