<?php

namespace Phifty\Schema\Finder;

use Phifty\Testing\TestCase;

class AppSchemaLoaderTest extends TestCase
{
    public function test()
    {
        $finder = new AppSchemaFinder([], $this->kernel);
        $files = $finder->find();
        $this->assertNotEmpty($files);
    }
}
