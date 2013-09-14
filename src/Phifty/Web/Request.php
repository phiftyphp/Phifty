<?php

namespace Phifty\Web;

class Request
{
    public $hash;

    public function __construct( & $hash = array() )
    {
        $this->hash = $hash;
    }

    public function has( $name )
    {
        return isset( $this->hash[ $name ] );
    }

    public function __isset( $name )
    {
        return isset( $this->hash[ $name ] );
    }

    public function __get( $name )
    {
        return @$this->hash[ $name ];
    }

    public function __set( $name , $value )
    {
        $this->hash[ $name ] = $value;
    }
}

class Request
{
    public $requestVars = array();

    public function __get( $name )
    {
        return $this->getRequestVar( $name );
    }

    public function getRequestVar( & $name )
    {
        if ( isset($this->requestVars[ $name ]) ) {
            return $this->requestVars[ $name ];
        }

        $vars = null;
        switch ($name) {
            case 'post':
                $vars = new RequestVar($_POST);
                break;
            case 'get':
                $vars = new RequestVar($_GET);
                break;
            case 'session':
                $vars = new RequestVar($_SESSION);
                break;
            case 'server':
                $vars = new RequestVar($_SERVER);
                break;
            case 'request':
                $vars = new RequestVar($_REQUEST);
                break;
            case 'cookie':
                $vars = new RequestVar($_COOKIE);
                break;
        }

        return $this->requestVars[ $name ] = $vars;
    }

}
