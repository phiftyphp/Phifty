<?php
/*
 * This file is part of Phifty package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace Phifty\JsonStore;

class JsonStoreFileTest extends \PHPUnit_Framework_TestCase
{

	function getStore()
	{
        $store = new \Phifty\JsonStore\FileJsonStore( 'User', 'tests/tmp' );
		return $store;
	}

    function test()
    {
        $store = $this->getStore();
        ok( $store );

		$store->destroy();
        $store->load();

        $user = new \Phifty\JsonStore\FileJsonModel( 'User' , $store );
        $id = $user->save(array( 'name' => 123 ));
        ok( $id );

		$store->save();

		$items = $store->items();
		ok( $items );
		count_ok( 1 , $items );


        $user = new \Phifty\JsonStore\FileJsonModel( 'User' , $store );
        $id = $user->save(array( 'name' => 333 ));
        ok( $id );

		$items = $store->items();
		ok( $items );
		count_ok( 2 , $items );


        $user = new \Phifty\JsonStore\FileJsonModel( 'User' , $store );
        $id = $user->save(array( 'id' => 99 , 'name' => 'with Id' ));
        ok( $id );

		ok( $store->get(99) );
		ok( $store->get("99") );


		$items = $store->items();
		ok( $items );
		count_ok( 3 , $items );


		ok( $store->destroy() );
    }
	
	function teardown()
	{
        $store = $this->getStore();
		$store->destroy();
	}
}

