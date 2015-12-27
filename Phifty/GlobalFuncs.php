<?php


/**
 * get($hash, 'folder', 'key' );
 * get($hash, 'folder');
 */


function get()
{
    $args = func_get_args();
    $hash = array_shift($args);

    if ( 1 == count($args) ) {
        if ( isset($hash[$args[0]]) ) {
            return $hash[$args[0]];
        }
        return false; // not found
    }


    $b = & $hash;
    while( $a = array_shift($args) ) {
        if ( isset($b[$a]) ) {
            if ( is_array($b[$a]) ) {
                $b = $b[$a];
            } else {
                return $b[$a];
            }
        } else {
            return false;
        }
    }
    return false;
}
