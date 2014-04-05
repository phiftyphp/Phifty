<?php
namespace Phifty\Command;
use CLIFramework\Command;
use Roller\Dumper\ConsoleDumper;

class BuildRouterCommand extends Command
{
    public function brief() {
        return 'Build router';
    }

    public function execute()
    {
        $this->logger->info('Building router...');
        // foreach 
    }
}
