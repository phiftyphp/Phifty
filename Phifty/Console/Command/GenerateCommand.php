<?php

namespace Phifty\Console\Command;

use CLIFramework\Command;
use GenPHP\Flavor\FlavorLoader;
use GenPHP\GeneratorRunner;

class GenerateCommand extends Command
{
    public function brief() { return 'template generator command'; }

    public function execute($flavor)
    {
        $args = func_get_args();
        array_shift($args);

        $loader = new FlavorLoader(array( dirname(__DIR__) . '/Flavors'));
        if ( $flavor = $loader->load($flavor) ) {
            $generator = $flavor->getGenerator();
            $generator->setLogger($this->logger);
            $runner = new GeneratorRunner;
            $runner->logger = $this->logger;
            $runner->run($generator,$args);
        } else {
            throw new Exception("Flavor $flavor not found.");
        }
        $this->logger->info('Done');
    }
}
