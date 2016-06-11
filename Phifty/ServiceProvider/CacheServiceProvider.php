<?php

namespace Phifty\ServiceProvider;

use UniversalCache\UniversalCache;
use CodeGen\Expr\NewObject;
use CodeGen\UserClosure;
use CodeGen\Statement\Statement;
use CodeGen\Expr\MethodCall;
use Phifty\Kernel;

class CacheServiceProvider extends BaseServiceProvider
{
    public function getId()
    {
        return 'cache';
    }

    public static function canonicalizeConfig(Kernel $kernel, array $options = array())
    {
        // handle Memcache initialization
        if (isset($options['Memcached'])) {
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

        if (extension_loaded('apcu')) {
            $builder[] = new Statement(new MethodCall('$cache', 'addBackend', [
                new NewObject('UniversalCache\ApcuCache', [$kernel->getApplicationID()]),
            ]));
        }

        if (extension_loaded('memcached') && isset($options['Memcached']['Servers'])) {
            if (isset($options['Memcached']['PersistentId'])) {
                $builder[] = '$memcached = '.new NewObject('Memcached', [$options['Memcached']['PersistentId']]).';';
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
                new NewObject('UniversalCache\FileSystemCache', [$kernel->getCacheDir()]),
            ]));
        }

        $builder[] = 'return $cache;';
        $className = get_called_class();

        return new NewObject($className, [$options, $builder]);
    }

    public function register(Kernel $kernel, $options = array())
    {
        $kernel->cache = $this->builder || function () {
            return new UniversalCache(array());
        };
    }
}
