<?php
namespace Phifty\Command\AssetCommand;
use CLIFramework\Command;
use Phifty\Command\AssetBaseCommand;

class InitCommand extends AssetBaseCommand
{

    public function execute()
    {
        $kernel = kernel();
        $config = $this->getAssetConfig();
        $loader = $this->getAssetLoader();
        $this->logger->info("Finding assets from applications...");
        if ($kernel->applications) {
            foreach ($kernel->applications as $application) {
                $this->registerBundleAssets($application);
            }
        }
        $this->logger->info("Finding assets from bundles...");

        if ($kernel->bundles) {
            foreach ($kernel->bundles as $bundle) {
                $this->registerBundleAssets($bundle);
            }
        }
        $loader->saveEntries();
    }
}
