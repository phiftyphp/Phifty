<?php
/*
 * This file is part of the Phifty package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace Phifty\JsonStore;

/**
 * $store = new FileJsonStore('ModelName');
 *
 * $store->add(array( .... ));
 * $store->add(array( .... ));
 * $store->add(array( .... ));
 *
 * $store->insert(0,array( .... ));
 *
 * $model = $store->get(1);
 *
 * $store->remove($id);
 * $store->remove($model);
 *
 * $store->save();
 *
 * $list = $store->load();
 *
    // use JsonStore to save schema
    $store = new FileJsonStore('SpecSchema', FileUtils::path_join( PH_APP_ROOT , 'webroot', 'spec' , 'schema' ) );
    $store->load();
    $record = $store->newModel();
    $record->save( array( 'id' => $product_id, 'fields' => $spec_data ) );
 *
 */

use Phifty\JsonStore\FileJsonModel;

class FileJsonStore
{
    public $name;
    public $rootDir;
    public $items;
    public $setname;

    public function __construct($name,$rootDir)
    {
        $this->name = $name;
        $this->rootDir = $rootDir;
        $this->setname = 'global';
        $this->items = array();
        if ( ! file_exists($this->rootDir ) )
            mkdir( $this->rootDir , 0755 , true ); // recursive
    }

    public function getStoreFile()
    {
        return $this->rootDir . DIRECTORY_SEPARATOR . $this->name . '_' . $this->setname . '.json';
    }

    public function load()
    {
        $file = $this->getStoreFile();
        if ( file_exists($file) ) {
            $data = json_decode(file_get_contents($file),true);
            if ( isset($data['items']) ) {
                $this->items = $data['items'];

                return count($this->items);
            }
        }
    }

    public function save()
    {
        $file = $this->getStoreFile();
        $string = json_encode( array( 'items' => $this->items ) );
        if ( file_put_contents( $file, $string ) === false )

            return false;
        return true;
    }

    public function add($record)
    {
        if ($this->items) {
            $keys = array_keys($this->items);
            sort($keys);
            $last_key = (int) end($keys);
            $last_key++;
            $record->id = $last_key;
        } else {
            $this->items = array();
            $last_key = 1;
        }
        $this->items[$last_key] = $record->getData();

        return $last_key;
    }

    public function update($record)
    {
        if ( is_object($record) ) {
            $id = $record->id;
            $data = $record->getData();
            $this->items[$id] = $data;
        }

        return true;
    }

    public function get($id)
    {
        if ( isset($this->items[$id]) )

            return new FileJsonModel( $this->name, $this, $this->items[$id] );
    }

    public function newModel($data = null)
    {
        return new FileJsonModel( $this->name , $this , $data );
    }

    public function items()
    {
        $that = $this;

        return array_map( function($e) use ($that) {
            return new FileJsonModel( $that->name, $that, $e );
        }, array_values($this->items) );
    }

    public function remove($id)
    {
        unset($this->items[$id]);
    }

    public function destroy()
    {
        $file = $this->getStoreFile();
        if ( file_exists($file) ) {
            unlink( $file );
            $this->items = null;

            return true;
        }
    }

    public function __destruct()
    {
        if ( $this->items )
            $this->save();
    }
}
