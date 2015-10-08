<?php
namespace Phifty\Command\AssetCommand;
use CLIFramework\Command;
use AssetKit\Installer;
use AssetKit\LinkInstaller;
use Phifty\Command\AssetBaseCommand;

/**
 * When running asset:init command, we should simply register app/plugin assets
 * into .assetkit file.
 *
 * Then, By running asset install command, phifty will install assets into webroot.
 */
class InstallCommand extends AssetBaseCommand
{
    public function options($opts)
    {
        $opts->add('l|link','use symbolic link');
    }

    public function execute()
    {
        $options = $this->options;
        $config = $this->getAssetConfig();
        $loader = $this->getAssetLoader();

        $installer = $options->link
                ? new LinkInstaller($config)
                : new Installer($config);

        $installer->logger = $this->logger;
        $loader = $this->getAssetLoader();
        $kernel = kernel();

        $this->logger->info("Installing assets from applications...");
        if ($apps = $kernel->getApps()) {
            foreach ($apps as $application) {
                // getAssets supports assets defined in config file.
                $assetNames = $application->getAssets();
                $assets = $loader->loadAssets($assetNames);
                foreach ($assets as $asset) {
                    $this->logger->info("Installing {$asset->name} ...");
                    $installer->install( $asset );
                }
            }
        }

        $this->logger->info("Installing assets from bundles...");
        if ($kernel->bundles) {
            foreach ($kernel->bundles as $bundle) {
                // getAssets supports assets defined in config file.
                $assetNames = $bundle->getAssets();
                $assets = $loader->loadAssets($assetNames);
                foreach ($assets as $asset) {
                    $this->logger->info("Installing {$asset->name} ...");
                    $installer->install( $asset );
                }
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
