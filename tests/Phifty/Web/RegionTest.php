<?php
use Phifty\Web\Region;

class RegionTest extends \PHPUnit\Framework\TestCase
{
    public function testGetRegionId()
    {
        $region = new Region('/bs/news/crud/edit',array( 
            'id' => 1
        ));
        $id = $region->getRegionId();
        ok($id);
        ok($region->render());
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

