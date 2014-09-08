<?php
namespace Phifty\Command;
use CLIFramework\Command;
use AssetKit\Installer;
use AssetKit\LinkInstaller;

/**
 * When running asset:init command, we should simply register app/plugin assets
 * into .assetkit file.
 *
 * Then, By running asset install command, phifty will install assets into webroot.
 */
class AssetInstallCommand extends AssetBaseCommand
{
    public function options($opts)
    {
        $opts->add('l|link','use symbolic link');
    }

    public function execute()
    {
        $options = $this->options;
        $config = $this->getAssetConfig();

        $installer = $options->link
                ? new LinkInstaller
                : new Installer;

        $installer->logger = $this->logger;
        $loader = $this->getAssetLoader();
        $kernel = kernel();


        $this->logger->info("Installing assets from applications...");
        foreach ($kernel->applications as $application) {
            $assetNames = $application->assets();
            $assets = $loader->loadAssets($assetNames);
            foreach ($assets as $asset) {
                $this->logger->info("Installing {$asset->name} ...");
                $installer->install( $asset );
            }
        }

        $this->logger->info("Installing assets from bundles...");
        foreach ($kernel->bundles as $plugin) {
            $assetNames = $plugin->assets();
            $assets = $loader->loadAssets($assetNames);
            foreach ($assets as $asset) {
                $this->logger->info("Installing {$asset->name} ...");
                $installer->install( $asset );
            }
        }


        $compiledDir = $config->getCompiledDir();
        if ( ! file_exists($compiledDir) ) {
            $this->logger->info("Creating asset compiled dir: $compiledDir");
            $this->logger->info("Please chmod this directory as you need.");
            mkdir($compiledDir,0777,true);
        }

        $this->logger->info("Done");
    }
}
