<?php
namespace Phifty\Console\Command\AssetCommand;
use CLIFramework\Command;

class ListCommand extends AssetBaseCommand
{
    public function execute()
    {
        $kernel = kernel();
        $config = $this->getAssetConfig();
        $loader = $this->getAssetLoader();
        $this->logger->debug("===> Finding assets from applications...");
        if ($app = $kernel->getApp()) {
            $this->showBundleAssets($app);
        }
        $this->logger->debug("===> Finding assets from bundles...");
        if ($kernel->bundles) {
            foreach ($kernel->bundles as $bundle) {
                $this->showBundleAssets($bundle);
            }
        }
        $loader->saveEntries();
    }
}
