<?php

namespace Phifty\Schema\Loader;

use Phifty\Testing\TestCase;

class AppSchemaLoaderTest extends TestCase
{
    public function test()
    {
        $kernel = $this->kernel;
        $loader = new AppSchemaLoader([], $kernel);
        $files = $loader->load();
        $this->assertNotEmpty($files);
    }
}

