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
class AssetCommand extends Command
{
    public function brief() { return 'register and install assets'; }

    public function options($opts)
    {
        $init = new AssetCommand\InstallCommand;
        $init->logger = $this->logger;
        $init->options($opts);
    }

    public function init()
    {
        $this->command('init');
        $this->command('list');
        $this->command('install');
    }

    public function execute()
    {
        $app = Console::getInstance();

        $init = new AssetCommand\InitCommand;
        $init->application = $app;
        $init->options = $this->options;
        $init->executeWrapper(array());

        $install = new AssetCommand\InstallCommand;
        $install->application = $app;
        $install->options = $this->options;
        $install->logger = $this->logger;
        $install->executeWrapper(array());
    }
}
