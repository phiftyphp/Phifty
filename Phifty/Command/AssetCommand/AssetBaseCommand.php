<?php
namespace Phifty\Command\AssetCommand;
use CLIFramework\Command;
use AssetKit\AssetLoader;
use AssetKit\AssetConfig;
use AssetKit\ResourceUpdater;

class AssetBaseCommand extends Command
{

    public function getAssetConfig()
    {
        return kernel()->asset->config;
    }

    public function getAssetLoader()
    {
        return kernel()->asset->loader;
    }


    public function showBundleAssets($bundle)
    {
        $config = $this->getAssetConfig();
        $loader = $this->getAssetLoader();
        $this->logger->debug("---> " . get_class($bundle));
        $cwd = getcwd();
        foreach ($bundle->getAssetDirs() as $dir ) {
            if (!file_exists($dir)) {
                continue;
            }
            $manifestFile = $dir . DIRECTORY_SEPARATOR . 'manifest.yml';
            if (!file_exists($manifestFile)) {
                continue;
            }
            $realdir = substr($dir, strlen($cwd) + 1 );
            $this->logger->writeln(get_class($bundle) . ' ' . $realdir);
        }

    }

    public function registerBundleAssets($bundle)
    {
        $config = $this->getAssetConfig();
        $loader = $this->getAssetLoader();
        $this->logger->debug("---> " . get_class($bundle));
        $cwd = getcwd();
        foreach ($bundle->getAssetDirs() as $dir ) {

            if (!file_exists($dir)) {
                $this->logger->warn("$dir doesn't exist", 1);
                continue;
            }

            $manifestFile = $dir . DIRECTORY_SEPARATOR . 'manifest.yml';
            if (!file_exists($manifestFile)) {
                $this->logger->warn("manifest file $manifestFile not found.", 1);
                continue;
            }
            $dir = substr($dir, strlen($cwd) + 1 );
            $this->logger->debug("Checking asset dir $dir");
            if ( $asset = $loader->register(realpath($dir)) ) {
                $this->logger->debug( "Found asset {$asset->name} @ $dir");
                $this->updateAssetResource($asset);
            }
        }
    }

    public function updateAssetResource($asset)
    {
        $updater = new ResourceUpdater;
        $updater->update($asset);
    }
}
