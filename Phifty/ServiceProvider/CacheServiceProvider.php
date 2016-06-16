<?php
namespace Phifty\ServiceProvider;
use UniversalCache\ApcuCache;
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
                $cache->addBackend(new ApcuCache($kernel->getApplicationID()));
            }
            return $cache;
        };
    }
}
