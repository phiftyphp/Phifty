<?php

namespace Phifty\Schema\Finder;

use Phifty\Testing\TestCase;

class AppSchemaFinderTest extends TestCase
{
    public function test()
    {
        $finder = new AppSchemaFinder([], $this->kernel);
        $files = $finder->find();
        $this->assertNotEmpty($files);
    }
}
