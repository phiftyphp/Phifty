<?php

namespace Phifty\ServiceProvider;

use MongoClient;
use Exception;
use Phifty\Kernel;

class MongodbServiceProvider extends ServiceProvider
{
    public function getId()
    {
        return 'mongodb';
    }

    /**
     * DSN:
     *      $m = new MongoClient("mongodb://username:password@/tmp/mongo-27017.sock:0/foo");
     *      $m = new MongoClient("mongodb:///tmp/mongo-27017.sock");
     *      $m = new MongoClient("mongodb://${username}:${password}@localhost/blog");.
     */
    public function register(Kernel $kernel, array $options = array())
    {
        if (!extension_loaded('mongo')) {
            throw new Exception('mongo extension is required.');
        }

        $kernel->mongo = function () use ($kernel, $options) {
            $conn = null;
            $db = null;
            if (isset($options['DSN'])) {
                $conn = new MongoClient($options['DSN']);
            } else {
                $conn = new MongoClient();
            }

            if (isset($options['Database'])) {
                $dbname = $options['Database'];
                $db = $conn->{ $dbname };
            }

            return (object) array(
                'connection' => $conn,
                'database' => $db,
            );
        };
    }
}
