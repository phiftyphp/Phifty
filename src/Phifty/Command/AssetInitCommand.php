<?php
namespace Phifty\Command;
use CLIFramework\Command;

class AssetInitCommand extends AssetBaseCommand
{

    public function execute()
    {
        $kernel = kernel();
        $config = $this->getAssetConfig();

        $ns = $kernel->config->get('framework','ApplicationID');
        $this->logger->info("Setting namespace to {$ns}");
        $config->setNamespace($ns);

        $loader = $this->getAssetLoader();

        $this->logger->info("Finding assets from applications...");
        foreach ($kernel->applications as $application) {
            $this->registerBundleAssets($application);
        }

        $this->logger->info("Finding assets from plugins...");
        foreach ($kernel->plugins as $plugin) {
            $this->registerBundleAssets($plugin);
        }
        $this->getAssetConfig()->save();
    }
}
