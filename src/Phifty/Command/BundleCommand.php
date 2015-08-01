<?php
namespace Phifty\Command;
use CLIFramework\Command;
use Phifty\Console;

/**
 * When running asset:init command, we should simply register app/plugin assets
 * into .assetkit file.
 *
 * Then, By running asset:update command, phifty will install assets into webroot.
 *
 *      phifty.php asset init
 *
 *      phifty.php asset update
 */
class BundleCommand extends Command
{
    public function brief() { return 'bundle commands'; }

    public function options($opts)
    {
        $opts->add('t|target-dir:','target directory for finding, storing bundle directories');
    }

    public function init()
    {
        $this->command('get');
        $this->command('sync');
    }

    public function execute()
    {
        $app = Console::getInstance();
        // XXX: list bundles
    }
}



