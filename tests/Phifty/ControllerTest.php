<?php
use Phifty\Controller;

class ControllerTest extends PHPUnit_Framework_TestCase
{
    public function testRenderYaml()
    {
        $controller = new Controller;
        $yaml = $controller->toYaml([ 
            'foo' => 1,
            'bar' => [ 'zoo' => 123 ],
        ]);
        $this->assertNotEmpty($yaml);
    }
}

