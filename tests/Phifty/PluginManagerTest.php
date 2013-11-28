<?php

class PluginManagerTest extends PHPUnit_Framework_TestCase
{
    function test()
    {
        $pm = new Phifty\Plugin\PluginManager;
        $obj = new stdClass;
        $pm->add( 'foo' , $obj );

        $cnt = 0;
        foreach( $pm as $name => $plugin ) {
            $cnt++;
        }

        ok( $cnt );
        
    }
}

