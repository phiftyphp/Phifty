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
class AssetCommand extends Command
{
    public function brief()
    {
        return 'register and install assets';
    }

    public function options($opts)
    {
        // Create options from install command
        $init = $this->createCommand(AssetCommand\InstallCommand::class);
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
        $app = Application::getInstance();

        $init = $this->createCommand(AssetCommand\InitCommand::class);
        $init->options = $this->getOptions();
        $init->executeWrapper(array());

        $install = $this->createCommand(AssetCommand\InstallCommand::class);
        $install->options = $this->getOptions();
        $install->executeWrapper(array());
    }
}
