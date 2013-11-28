<?php

class KernelTest extends PHPUnit_Framework_TestCase
{


    function testRouter()
    {
        $app = kernel();
        $router = $app->router;
        ok( $router );
        is( $router , $app->router );
    }

    function testKernel()
    {
        // write your test code here
        $kernel = kernel();
        ok( $kernel );

        ok( $kernel->webroot );
        ok( $kernel->frameworkDir );
        ok( $kernel->rootDir );


        ok( $kernel->isCLI );

        path_ok( $kernel->webroot );
        path_ok( $kernel->rootDir );


        ok( $kernel->getApplicationUUID() );
        ok( $kernel->getApplicationName() );

        $kernel = kernel();
        ok( $kernel );

		# var_dump( $kernel->pluginList() ); 

        /* should be in dev mode */
        ok( $kernel->isDev , 'Please run tests in development mode.' );
        ok( $cache = $kernel->cache );
    }
}

