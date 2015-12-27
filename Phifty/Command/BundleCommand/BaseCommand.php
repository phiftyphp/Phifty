<?php
namespace Phifty\Command\BundleCommand;
use CLIFramework\Command;
use Phifty\Console;
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






