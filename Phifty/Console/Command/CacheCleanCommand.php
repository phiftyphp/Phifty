<?php
namespace Phifty\Console\Command;
use CLIFramework\Command;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class CacheCleanCommand extends Command
{

    public function execute() 
    {
        if (file_exists('cache')) {
            $this->logger->info("Removing cache files from directory ./cache ...");
            $rit = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('cache'), RecursiveIteratorIterator::CHILD_FIRST); 
            foreach ($rit as $file) { 
                if  ( $file->isFile() ) {
                    $this->logger->info( "Removing " . $file->getPathname() );
                    unlink($file->getPathname());
                }
            } 
        }

        if (extension_loaded('apcu')) {
            $this->logger->info("Clearing apcu cache ...");
            apcu_clear_cache();
        }
    }
}



