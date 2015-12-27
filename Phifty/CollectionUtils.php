<?php
namespace Phifty;

class CollectionUtils
{
    static function aggregateByLang( $langs , $collectionClass ) {
        $collectionsByLangCode = array();
        foreach( $langs as $code => $name ) {
            $collection = new $collectionClass;
            $collection->where(array( 'lang' => $code ));
            $collectionsByLangCode[ $code ] = $collection;
        }
        return $collectionsByLangCode;
    }
}



