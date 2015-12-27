<?php
namespace Phifty;

class Logger
{
    public $logDir;
    public $logFile;
    private $fp;

    public function __construct($logDir,$prefix = '')
    {
        $this->logDir = $logDir;

        if ( ! file_exists( $this->logDir ) )
            mkdir( $this->logDir , 0755 , true );

        // $this->logFile = $logDir . DIRECTORY_SEPARATOR . $prefix . date('Y.m.d-Hi') . '.log';
        $this->logFile = $logDir . DIRECTORY_SEPARATOR . $prefix . date('Y.m.d') . '_' . time() . '.log';
        $this->fp = fopen( $this->logFile , 'a+' )
            or die("Can not open log file.");
    }

    public function info( $msg )
    {
        fwrite( $this->fp , $msg . "\n" );
    }

    public function close()
    {
        fclose( $this->fp );
        $this->fp = null;
    }

    public function __destruct()
    {
        if ( $this->fp !== null )
            $this->close();
    }
}
