<?php

namespace Phifty\Web;

class RegionTest extends \PHPUnit\Framework\TestCase
{
    public function testGetRegionId()
    {
        $region = new Region('/bs/news/crud/edit',array( 
            'id' => 1
        ));
        $id = $region->getRegionId();
        $this->assertNotNull($id);
        $this->assertNotNull($region->render());
    }

    public function testRegionWithPath()
    {
        $region = new Region('/bs/product/crud/create');
        $html = $region->render();
        $this->assertNotNull($html);
    }

    public function testRegionIdFactory()
    {
        $id = Region::newRegionSerialId();
        $this->assertTrue(is_string($id));
    }
}

