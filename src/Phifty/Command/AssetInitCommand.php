<?php
namespace Phifty\Command;
use CLIFramework\Command;

class AssetInitCommand extends AssetBaseCommand
{
    public function execute()
    {
        $kernel = kernel();
        $config = $this->getAssetConfig();
        $loader = $this->getAssetLoader();

        $this->logger->info("Finding assets from applications...");
        foreach ($kernel->applications as $application) {
            $this->registerBundleAssets($application);
        }

        $this->logger->info("Finding assets from bundles...");
        foreach ($kernel->bundles as $bundle) {
            $this->registerBundleAssets($bundle);
        }
        $loader->saveEntries();
    }
}
