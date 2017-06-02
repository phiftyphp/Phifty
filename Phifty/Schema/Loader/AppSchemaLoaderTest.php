<?php

namespace Phifty\Schema\Loader;

use Phifty\Testing\TestCase;

class AppSchemaLoaderTest extends TestCase
{
    public function test()
    {
        $loader = new AppSchemaLoader([], $this->kernel);
        $files = $loader->load();
        $this->assertNotEmpty($files);
    }
}
