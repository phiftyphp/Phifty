<?php
namespace Phifty\Command;
use Phifty\FileUtils;
use Phifty\Plugin\Plugin;
use CLIFramework\Command;

/*
 * Export plugin web dirs to app webroot.
 */
class ExportCommand extends Command
{

    public function usage()
    {
        return 'export';
    }

    public function brief()
    {
        return 'Export application/plugin web paths to webroot/.';
    }

    public function execute()
    {
        $kernel       = kernel();
        $webroot      = $kernel->webroot;
        foreach ( kernel()->plugins as $plugin ) {
            // Exporting Web directory
        }
    }
}
