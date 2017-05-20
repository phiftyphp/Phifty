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

class JsonStoreFileTest extends \PHPUnit\Framework\TestCase
{

	function getStore()
	{
        $store = new \Phifty\JsonStore\FileJsonStore( 'User', 'tests/tmp' );
		return $store;
	}

    function test()
    {
        $store = $this->getStore();
        $this->assertNotNull( $store );

		$store->destroy();
        $store->load();

        $user = new \Phifty\JsonStore\FileJsonModel( 'User' , $store );
        $id = $user->save(array( 'name' => 123 ));
        $this->assertNotNull( $id );

		$store->save();

		$items = $store->items();
		$this->assertNotNull( $items );
		$this->assertCount( 1 , $items );


        $user = new \Phifty\JsonStore\FileJsonModel( 'User' , $store );
        $id = $user->save(array( 'name' => 333 ));
        $this->assertNotNull( $id );

		$items = $store->items();
		$this->assertNotNull( $items );
		$this->assertCount( 2 , $items );


        $user = new \Phifty\JsonStore\FileJsonModel( 'User' , $store );
        $id = $user->save(array( 'id' => 99 , 'name' => 'with Id' ));
        $this->assertNotNull( $id );

		$this->assertNotNull( $store->get(99) );
		$this->assertNotNull( $store->get("99") );


		$items = $store->items();
		$this->assertNotNull( $items );
		$this->assertCount( 3 , $items );


		$this->assertNotNull( $store->destroy() );
    }
	
	function teardown()
	{
        $store = $this->getStore();
		$store->destroy();
	}
}

