<?php
namespace Phifty\Command;
use CLIFramework\Command;

class ServerCommand extends Command
{
    public function brief() { return 'run http server'; }

    public function options($opts)
    {
        $opts->add('h|host:','host');
        $opts->add('p|port:','port');
    }

    public function execute()
    {
        while (@ob_end_flush());

        $php  = $_SERVER['_'];
        $host = $this->options->host ?: 'localhost';
        $port = $this->options->port ?: '8000';
        chdir(PH_APP_ROOT . DIRECTORY_SEPARATOR . 'webroot');
        if ( extension_loaded('pcntl') ) {
            pcntl_exec($php, array('-S', "$host:$port", 'index.php'));
        } else {
            $this->logger->info("Starting server at http://$host:$port");
            passthru($php . ' ' . join(' ',array('-S', "$host:$port", 'index.php')));
        }
    }
}
