<?php
namespace Phifty\Service;
use Phifty\ComposerConfigBridge;
use PredisClient;
use Exception;

class RedisService
    implements ServiceInterface, ComposerConfigBridge
{
    public function getId() { return 'Redis'; }

    public function register($kernel, $options = array() )
    {
        $kernel->redis = function() use ($kernel, $options) {
            /*
             $redis = new PredisClient([
                "scheme" => "tcp",
                "host" => "127.0.0.1",
                "port" => 6379,
             ]);
             * */
            try {
                if ( empty($options) ) {
                    $redis = new PredisClient();
                } else {
                    $redis = new PredisClient(array_merge([
                        "scheme" => "tcp",
                        "host" => "127.0.0.1",
                        "port" => 6379,
                    ],$options));
                }
            } catch (Exception $e) {
                die("Couldn't connected to Redis: " . $e->getMessage());
            }
            return $redis;
        };
    }

    public function getComposerDependency() 
    {
        return ["predis/predis" => "@stable"];
    }
}



