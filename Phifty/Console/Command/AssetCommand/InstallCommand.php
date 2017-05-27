<?php
namespace Phifty\Console\Command\AssetCommand;

use CLIFramework\Command;
use AssetKit\Installer;
use AssetKit\LinkInstaller;
use Phifty\Bundle;

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
        $opts->add('l|link', 'use symbolic link');
    }


    protected function installBundleAssets(Bundle $bundle, Installer $installer)
    {
        $loader = $this->getAssetLoader();
        $this->logger->debug("Installing " . get_class($bundle) . " assets...");


        $rootAssetEntryFile = PH_ROOT . DIRECTORY_SEPARATOR . '.asset-entries.json';
        $rootNodeModules = PH_ROOT . DIRECTORY_SEPARATOR . 'node_modules';
        $hasRootAssetEntryFile = file_exists($rootAssetEntryFile);
        $hasRootNodeModule = file_exists($rootNodeModules);

        // getAssets supports assets defined in config file.
        $assetNames = $bundle->getAssets();
        $assets = $loader->loadAssets($assetNames);
        if (!empty($assets)) {
            foreach ($assets as $asset) {
                // create symlink with the asset json files
                $this->logger->debug("Installing {$asset->name} ...");
                $installer->install($asset);

                if ($hasRootAssetEntryFile) {
                    $target = $asset->getSourceDir() . DIRECTORY_SEPARATOR . '.asset-entries.json';
                    // destroy the original symlink
                    if (file_exists($target)) {
                        unlink($target);
                    }
                    $this->logger->debug("Linking $rootAssetEntryFile to $target");
                    symlink($rootAssetEntryFile, $target);
                }
                if ($hasRootNodeModule) {
                    $target = $asset->getSourceDir() . DIRECTORY_SEPARATOR . 'node_modules';
                    // destroy the original symlink
                    if (file_exists($target)) {
                        unlink($target);
                    }
                    $this->logger->debug("Linking $rootNodeModules to $target");
                    symlink($rootNodeModules, $target);
                }
            }
            $this->logger->info(get_class($bundle) . ': ' . count($assets) . " assets installed.");
        }
    }

    public function execute()
    {
        $options = $this->options;
        $config = $this->getAssetConfig();

        $loader = $this->getAssetLoader();
        $installer = $this->options->link ? new LinkInstaller($config, $this->logger) : new Installer($config, $this->logger);
        $kernel = kernel();

        $this->logger->debug("Installing App assets...");
        if ($app = $kernel->getApp()) {
            $this->installBundleAssets($app, $installer);
        }

        if ($kernel->bundles) {
            foreach ($kernel->bundles as $bundle) {
                $this->installBundleAssets($bundle, $installer);
            }
        }


        $compiledDir = $config->getCompiledDir();
        if (! file_exists($compiledDir)) {
            $this->logger->info("Creating asset compiled dir: $compiledDir");
            $this->logger->info("Please chmod this directory as you need.");
            mkdir($compiledDir, 0777, true);
        }

        $this->logger->info("Done");
    }
}
