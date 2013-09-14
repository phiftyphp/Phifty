<?php
namespace Phifty;
use Phifty\Logger;

abstract class Importer
{
    public $logger;

    public function __construct($logPrefix = 'import-')
    {
        $this->logger = new Logger( 'logs' , $logPrefix );
    }

    public function info( $msg , $pad = 0 )
    {
        $msg = str_repeat(' ',$pad * 4) . $msg;
        echo $msg . "\n";
        $this->logger->info( $msg );
    }

    /* import from a file or a directory */
    abstract public function import($target);

}
