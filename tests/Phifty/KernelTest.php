<?php

class KernelTest extends PHPUnit_Framework_TestCase
{
    public function testKernel()
    {
        $kernel = kernel();
        $this->assertNotNull($kernel);
        $this->assertFileExists( $kernel->webroot );
        $this->assertFileExists( $kernel->rootDir );
    }
}

