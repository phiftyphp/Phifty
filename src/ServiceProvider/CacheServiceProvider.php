<?php

namespace Phifty\ServiceProvider;

use UniversalCache\UniversalCache;
use UniversalCache\ApcuCache;
use CodeGen\Expr\NewObject;
use CodeGen\UserClosure;
use CodeGen\Statement\Statement;
use CodeGen\Expr\MethodCall;
use Phifty\Kernel;

use UniversalCache\FileSystemCache;
use Closure;

class CacheServiceProvider extends ServiceProvider
{
    /**
     * @var Closure This is used for overriding the default service builder.
     */
    protected $builder;

    /**
     * @param Closure $builder is used for overriding the default service builder.
     */
    public function __construct(Closure $builder = null)
    {
        $this->builder = $builder;
    }

    public function getId()
    {
        return 'cache';
    }

    public static function canonicalizeConfig(Kernel $kernel, array $options = array())
    {
        // handle Memcache initialization
        if (isset($options['Memcached'])) {
            if ($options['Memcached'] === true) {
                $options['Memcached'] = [];
            }

            if (isset($options['Memcached']['Servers'])) {
                $options['Memcached']['Servers'] = array_map(function ($item) {
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

        return $options;
    }

    public static function generateNew(Kernel $kernel, array &$options = array())
    {
        $builder = new UserClosure([], ['$kernel']);
        $builder[] = '$cache = new UniversalCache(array());';

        if (extension_loaded('apcu') && isset($options['APC'])) {
            $builder[] = new Statement(new MethodCall('$cache', 'addBackend', [
                new NewObject(ApcuCache::class, [$kernel->getApplicationID()]),
            ]));
        }

        if (extension_loaded('memcached') && isset($options['Memcached'])) {
            if (isset($options['Memcached']['PersistentId'])) {
                $builder[] = '$memcached = '.new NewObject('Memcached', [$options['Memcached']['PersistentId']]).';';
            } else {
                $builder[] = '$memcached = new Memcached;';
            }
            if (isset($options['Memcached']['Servers'])) {
                foreach ($options['Memcached']['Servers'] as $server) {
                    $builder[] = new Statement(new MethodCall('$memcached', 'addServer', $server));
                }
            }
            $builder[] = new Statement(new MethodCall('$cache', 'addBackend', ['$memcached']));
        }

        if (isset($options['FileSystem'])) {
            $builder[] = new Statement(new MethodCall('$cache', 'addBackend', [
                new NewObject(FileSystemCache::class, [$kernel->getCacheDir()]),
            ]));
        }

        $builder[] = 'return $cache;';
        $className = get_called_class();
        return new NewObject($className, [$builder]);
    }

    public function register(Kernel $kernel, array $options = array())
    {
        $kernel->cache = $this->builder ?: function() use ($kernel, $options) {
            $cache = new UniversalCache([]);
            if (extension_loaded('apcu') && isset($options['APC'])) {
                $cache->addBackend(new ApcuCache($kernel->getApplicationID()));
            }

            if (extension_loaded('memcached') && isset($options['Memcached'])) {
                if (isset($options['Memcached']['PersistentId'])) {
                    $memcached = new \Memcached($options['Memcached']['PersistentId']);
                } else {
                    $memcached = new \Memcached();
                }
                if (isset($options['Memcached']['Servers'])) {
                    foreach ($options['Memcached']['Servers'] as $server) {
                        $memcached->addServer($server);
                    }
                }
                $cache->addBackend($memcached);
            }

            if (isset($options['FileSystem'])) {
                $cache->addBackend(new FileSystemCache($kernel->getCacheDir()));
            }

            return $cache;
        };
    }
}
