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
        static $config;
        if ($config)

            return $config;
        return $config = new AssetConfig('.assetkit.php');
    }

    public function getAssetLoader()
    {
        return new AssetLoader($this->getAssetConfig());
    }

    public function registerBundleAssets($bundle)
    {
        $config = $this->getAssetConfig();
        $this->logger->info( ' - ' . get_class($bundle) );
        $cwd = getcwd();
        foreach ( $bundle->getAssetDirs() as $dir ) {
            if ( file_exists($dir) ) {
                $dir = substr($dir, strlen($cwd) + 1 );
                if ( $asset = $config->registerAssetFromPath($dir) ) {
                    $this->logger->info( "Found asset {$asset->name}" ,1 );
                    $this->updateAssetResource($asset);
                }
            } else {
                $this->logger->warn("$dir directory not found.", 1);
            }
        }
    }

    public function updateAssetResource($asset)
    {
        $updater = new ResourceUpdater;
        $updater->update($asset);
    }
}
