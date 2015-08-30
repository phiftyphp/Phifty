<?php
namespace Phifty\Command;
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

    public function registerBundleAssets($bundle)
    {
        $config = $this->getAssetConfig();
        $loader = $this->getAssetLoader();
        $this->logger->info( ' - ' . get_class($bundle) );
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
            if ( $asset = $loader->register(realpath($dir)) ) {
                $this->logger->info( "Found asset {$asset->name}" ,1 );
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
