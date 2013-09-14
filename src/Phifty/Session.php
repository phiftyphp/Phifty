<?php
namespace Phifty;

class Session
{
    public $sessionPrefix;

    public function __construct( $sessionPrefix = '' )
    {
        $this->sessionPrefix = $sessionPrefix;
    }

    public function __set( $name , $value )
    {
        $this->set( $name , $value );
    }

    public function __get( $name )
    {
        return $this->get( $name );
    }

    public function __isset( $name )
    {
        return isset( $_SESSION[ $this->sessionPrefix . $name ] );
    }

    public function set($name,$value)
    {
        $key = $this->sessionPrefix . $name;
        @$_SESSION[ $key ] = $value;
    }

    public function get($name)
    {
        $key = $this->sessionPrefix . $name;

        return @$_SESSION[ $key ];
    }

    public function remove($name)
    {
        $key = $this->sessionPrefix . $name;
        unset( $_SESSION[ $key ] );
    }

    public function getAll()
    {
        $args = array();
        foreach( $_SESSION as $key => $value )
            if ( strpos( $key , $this->sessionPrefix ) === 0 )
                $args[ $key ] = $value;

        return $args;
    }

    public function has($name)
    {
        return isset( $_SESSION[ $this->sessionPrefix . $name ] );
    }

    public function setArgs( $args )
    {
        foreach ($args as $key => $value) {
            @$_SESSION[ $this->sessionPrefix . $key ] = $value;
        }
    }

    public function doExpire( $minutes )
    {
        session_cache_expire( $minutes );
    }

    public function getExpire()
    {
        return session_cache_expire();
    }

    public function getId()
    {
        return session_id();
    }

    public function decode( $data )
    {
        return session_decode( $data );
    }

    public function encode()
    {
        return session_encode();
    }

    public function clear()
    {
        session_unset();

        /* force session var */
        $_SESSION = array();
    }

    public function destroy()
    {
        @session_destroy();
        $this->clear();
    }
}
