<?php
namespace Phifty\Service;
use Mongo;
use Exception;

class MongodbService
    implements ServiceRegister
{

    public function getId() { return 'mongodb'; }

    /**
     *
     * DSN:
     *      $m = new Mongo("mongodb://username:password@/tmp/mongo-27017.sock:0/foo");
     *      $m = new Mongo("mongodb:///tmp/mongo-27017.sock");
     *      $m = new Mongo("mongodb://${username}:${password}@localhost/blog");
     */
    public function register( $kernel , $options = array() )
    {
        if ( ! extension_loaded('mongo') )
            throw new Exception('mongo extension is required.');

        $kernel->mongo = function() use ($kernel,$options) {
            $conn = null;
            $db = null;
            if ( isset($options['DSN']) ) {
                $conn = new Mongo( $options['DSN'] );
            } else {
                $conn = new Mongo;
            }

            if ( isset($options['Database']) ) {
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
