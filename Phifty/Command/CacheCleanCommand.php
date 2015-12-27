<?php
namespace Phifty\Command;
use CLIFramework\Command;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class CacheCleanCommand extends Command
{

    public function execute() 
    {
        if ( file_exists('cache') ) {
            $rit = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('cache'), RecursiveIteratorIterator::CHILD_FIRST); 
            foreach ($rit as $file) { 
                if  ( $file->isFile() ) {
                    $this->logger->info( "Removing " . $file->getPathname() );
                    unlink( $file->getPathname() );
                }
            } 
        }

        if ( extension_loaded('apc') ) {
            apc_clear_cache();
            apc_clear_cache('user');
        }
    }
}



