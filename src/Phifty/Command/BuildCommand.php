<?php
namespace Phifty\Command;
use CLIFramework\Command;
use Roller\Dumper\ConsoleDumper;

class BuildCommand extends Command
{
    public function brief() {
        return 'Build component files.';
    }

    public function init()
    {
        $this->command('router', 'Phifty\Command\BuildRouterCommand');
    }

    public function execute()
    {
        $this->logger->info('Available commands: router');
    }

}
