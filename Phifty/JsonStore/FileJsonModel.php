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

/*
 * Store data in json file.
 *
 * $record = new FileJsonModel('ModelName',$store);
 *
 * save object in path/to/dir/1.json
 *
 *      $record->column = value;
 *
 * load object
 *
 *      $record->load( $id );
 *
 * update object
 *
 *      $record->update( $id , array( ... ) );
 *
 */
class FileJsonModel
{
    protected $name;
    protected $store;
    protected $data;

    public function __construct($name,$store,$data = null)
    {
        $this->name = $name;
        $this->store = $store;
            $this->data = $data ? $data : array();
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __get($name)
    {
        if ( isset($this->data[$name] ) )

            return $this->data[$name];
    }

    public function __set($name,$value)
    {
        $this->data[$name] = $value;
    }

    public function hasId()
    {
        return isset($this->data['id']) && $this->data['id'];
    }

    public function getData()
    {
        return $this->data;
    }

    public function save($data = null)
    {
        if ( $data )
            $this->data = $data;

        if ( $this->hasId() )

            return $this->store->update($this);
        else
            return $this->store->add($this);
    }

    public function load($id)
    {
        $data = $this->store->get($id);
        $this->data = $data;
    }

    public function update($data)
    {
        $id = $data['id'];
        $orig_data = $this->load($id);
        if ($orig_data) {
            $data = array_merge( $orig_data, $data );
            $this->store($data);
        }
    }

}
