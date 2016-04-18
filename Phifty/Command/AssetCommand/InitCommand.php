<?php
namespace Phifty\Command\AssetCommand;
use CLIFramework\Command;

class InitCommand extends AssetBaseCommand
{
    public function execute()
    {
        $kernel = kernel();
        $config = $this->getAssetConfig();
        $loader = $this->getAssetLoader();
        $this->logger->debug("===> Finding assets from applications...");
        if ($app = $kernel->getApp()) {
            $this->registerBundleAssets($app);
        }
        $this->logger->debug("===> Finding assets from bundles...");
        if ($kernel->bundles) {
            foreach ($kernel->bundles as $bundle) {
                $this->registerBundleAssets($bundle);
            }
        }
        $loader->saveEntries();
    }
}
