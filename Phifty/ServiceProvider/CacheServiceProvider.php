<?php
namespace Phifty\ServiceProvider;
use UniversalCache\ApcuCache;
use UniversalCache\FileSystemCache;
use UniversalCache\MemcacheCache;
use UniversalCache\UniversalCache;
use CodeGen\Expr\NewObject;
use CodeGen\UserClosure;
use CodeGen\Statement\Statement;
use CodeGen\Expr\MethodCall;
use Phifty\Kernel;

class CacheServiceProvider extends BaseServiceProvider
{
    protected $builder;

    public function getId() { return 'cache'; }

    public function __construct(array $config = array(), $builder)
    {
        $this->config = $config;
        $this->builder = $builder;
    }

    static public function generateNew(Kernel $kernel, array & $options = array())
    {
        // handle Memcache initialization
        if (isset($options['Memcached'])) {
            if (isset($options['Memcached']['Servers'])) {
                $options['Memcached']['Servers'] = array_map(function($item) {
                    if (is_array($item)) {
                        if (isset($item[0])) {
                            return $item;
                        } else {
                            return [$item['Host'], $item['Port']];
                        }
                    }

                }, $options['Memcached']['Servers']);
            } else {
                $options['Memcached']['Servers'] = [['localhost', 11211]];
            }
        }

        $builder = new UserClosure([], ['$kernel']);
        $builder[] = '$cache = new UniversalCache(array());';

        if (extension_loaded('apcu')) {
            $builder[] = new Statement(new MethodCall('$cache', 'addBackend', [
                new NewObject('UniversalCache\ApcuCache', [$kernel->getApplicationID()])
            ]));
        }

        if (extension_loaded('memcached') && isset($options['Memcached']['Servers']) ) {
            if (isset($options['Memcached']['PersistentId'])) {
                $builder[] = '$memcached = ' . new NewObject('Memcached', [$options['Memcached']['PersistentId']]) . ';';
            } else {
                $builder[] = '$memcached = new Memcached;';
            }
            foreach ($options['Memcached']['Servers'] as $server) {
                $builder[] = new Statement(new MethodCall('$memcached', 'addServer', $server));
            }
            $builder[] = new Statement(new MethodCall('$cache', 'addBackend', ['$memcached']));
        }

        if (isset($options['FileSystem'])) {
            $builder[] = new Statement(new MethodCall('$cache', 'addBackend', [
                new NewObject('UniversalCache\FileSystemCache', [$kernel->getCacheDir()])
            ]));
        }


        $builder[] = 'return $cache;';
        $className = get_called_class();
        return new NewObject($className, [$options, $builder]);
    }

    // XXX: we should provide config for get the cache object.
    public function register($kernel, $options = array() )
    {
        $kernel->cache = $this->builder || function() use ($kernel) {
            return new UniversalCache(array());
        };
    }
}
