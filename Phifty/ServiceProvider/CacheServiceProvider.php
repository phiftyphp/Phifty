<?php
namespace Phifty\ServiceProvider;
use UniversalCache\ApcCache;
use UniversalCache\FileSystemCache;
use UniversalCache\MemcacheCache;
use UniversalCache\UniversalCache;

class CacheServiceProvider extends BaseServiceProvider
{
    public function getId() { return 'cache'; }

    // XXX: we should provide config for get the cache object.
    public function register($kernel, $options = array() )
    {
        $kernel->cache = function() use ($kernel) {
            $cache = new UniversalCache(array());
            if (extension_loaded('apc') || extension_loaded('apcu')) {
                $cache->addBackend(new ApcCache( array( 'namespace' => $kernel->getApplicationID() ) ));
            }
            if ( extension_loaded('memcache') ) {
                $cache->addBackend(new MemcacheCache( array( 
                    'servers' => array( array('localhost',11211) )
                )));
            }
            return $cache;
        };
    }
}
