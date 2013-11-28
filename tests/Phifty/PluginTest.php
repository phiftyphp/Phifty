<?php
use Phifty\FileUtils;

class PluginTest extends PHPUnit_Framework_TestCase
{
    function testPlugin()
    {
        $plugin = new \Phifty\Plugin\Plugin;
        $plugin->setConfig( array('foo' => 1 ) );
        is( 1, $plugin->config( 'foo' ) );
    }
}
