<?php
namespace Phifty\Console\Command;

use CLIFramework\Command;
use Phifty\Console\Application;

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
        $this->command('install');
    }

    public function execute()
    {
        $app = Application::getInstance();
        // TODO: list bundles
    }
}



