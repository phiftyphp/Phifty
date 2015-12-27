<?php
namespace Phifty\Command;
use CLIFramework\Command;
use Roller\Dumper\ConsoleDumper;

class RouterCommand extends Command
{
    public function brief() {
        return 'List routing paths';
    }

    public function execute()
    {
        $router = kernel()->router;
        $router->compile();
        $dumper = new ConsoleDumper;
        $dumper->dump( $router->routes );
    }
}
