<?php

class BundleTest extends PHPUnit_Framework_TestCase
{
    function testBasicAPI()
    {
        $bundle = new Phifty\Bundle;
        ok($bundle);
    }

    function testCoreBundle()
    {
        $core = new CoreBundle\CoreBundle;
        ok($core);

        $paths = $core->getAssetDirs();
        ok($paths);

    }

}

