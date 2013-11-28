<?php

class BrowserClientTest extends PHPUnit_Framework_TestCase
{
    function setUp() { 
        if( ! extension_loaded('geoip') ) {
            skip('geoip extension is required.');
        }
        if( ! dns_get_record('www.hinet.net') ) {
            skip('networking is required.');
        }
    }

    function test()
    {
        $client = new Phifty\Http\BrowserClient('96.126.103.155');
        ok( $client->geoipSupports );
        /*
        if( $client->geoipSupports ) {
            ok( $client->city );
            ok( $client->country );
        }
        */
    }
}

