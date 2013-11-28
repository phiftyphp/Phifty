<?php

class RegionTest extends PHPUnit_Framework_TestCase
{
    function test()
    {
        $region = new Phifty\Web\Region('/bs/news/crud/edit',array( 
            'id' => 1
        ));
        $id = $region->getRegionId();
        ok($id);
        ok($region->render());
    }
}

