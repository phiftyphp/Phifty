<?php
namespace Phifty\Console\Command\BundleCommand;
use CLIFramework\Command;
use Phifty\Console\Application;
use Exception;
use DirectoryIterator;
use GitElephant\Repository;

class BaseCommand extends Command
{
    public function options($opts)
    {
        $this->parent->options($opts);
    }
}






