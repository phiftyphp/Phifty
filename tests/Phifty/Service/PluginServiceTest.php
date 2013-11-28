<?php

class PluginServiceTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $s = new Phifty\Service\PluginService;
        ok($s);
    }
}

