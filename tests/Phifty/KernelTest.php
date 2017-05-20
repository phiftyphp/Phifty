<?php

class KernelTest extends \PHPUnit\Framework\TestCase
{
    public function testKernel()
    {
        $kernel = kernel();
        $this->assertNotNull($kernel);
        $this->assertFileExists( $kernel->webroot );
        $this->assertFileExists( $kernel->rootDir );
    }
}

