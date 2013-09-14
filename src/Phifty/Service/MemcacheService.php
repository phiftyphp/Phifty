<?php
namespace Phifty\Service;
use Memcache;
use Exception;

class MemcacheService
    implements ServiceInterface
{

    public function getId() { return 'Memcache'; }

    public function register( $kernel , $options = array() )
    {
        if ( ! extension_loaded('memcache') ) {
            throw new Exception('memcache extension is required');
        }

        $kernel->memcache = function() use ($options) {
            $memcache = new Memcache;

            if ( isset($options['Servers'] )) {
                foreach ($options['Servers'] as $server) {
                    /**
                        bool Memcache::addServer ( string $host
                            [, int $port = 11211
                            [, bool $persistent
                            [, int $weight
                            [, int $timeout
                            [, int $retry_interval
                            [, bool $status
                            [, callable $failure_callback
                            [, int $timeoutms ]]]]]]]] )
                     */
                    $args = array();
                    foreach ( array('host','port','persistent','weight','timeout','retry_interval','status') as $k ) {
                        if ( isset($server[$k]) ) {
                            $args[] = $server[$k];
                        } else {
                            break;
                        }
                    }
                    if ( false === call_user_func_array( array($memcache,'addServer') , $args ) ) {
                        throw new Exception("Could not connect to memcache.");
                    }
                }
            } else {
                $memcache->addServer('localhost',11211);
            }
            // $memcache->connect('localhost', 11211) or die ("Could not connect");
            return $memcache;
        };
    }
}
