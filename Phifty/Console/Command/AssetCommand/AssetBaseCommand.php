<?php

namespace Phifty\Console\Command\AssetCommand;

use CLIFramework\Command;
use AssetKit\AssetLoader;
use AssetKit\AssetConfig;
use AssetKit\ResourceUpdater;
use Phifty\Bundle;

class AssetBaseCommand extends Command
{
    protected function getAssetConfig()
    {
        return kernel()->asset->config;
    }

    protected function getAssetLoader()
    {
        return kernel()->asset->loader;
    }

    protected function showBundleAssets(Bundle $bundle)
    {
        $config = $this->getAssetConfig();
        $loader = $this->getAssetLoader();
        $this->logger->debug("---> " . get_class($bundle));
        $cwd = getcwd();
        foreach ($bundle->getAssetDirs() as $dir) {
            if (!file_exists($dir)) {
                continue;
            }
            $manifestFile = $dir . DIRECTORY_SEPARATOR . 'manifest.yml';
            if (!file_exists($manifestFile)) {
                continue;
            }
            $realdir = substr($dir, strlen($cwd) + 1);
            $this->logger->writeln(get_class($bundle) . ' ' . $realdir);
        }
    }

    protected function registerBundleAssets(Bundle $bundle)
    {
        $config = $this->getAssetConfig();
        $loader = $this->getAssetLoader();
        $this->logger->debug("---> " . get_class($bundle));
        $cwd = getcwd();
        foreach ($bundle->getAssetDirs() as $dir) {
            if (!file_exists($dir)) {
                $this->logger->warn("$dir doesn't exist", 1);
                continue;
            }

            $manifestFile = $dir . DIRECTORY_SEPARATOR . 'manifest.yml';
            if (!file_exists($manifestFile)) {
                $this->logger->warn("manifest file $manifestFile not found.", 1);
                continue;
            }
            $dir = substr($dir, strlen($cwd) + 1);
            $this->logger->debug("Checking asset dir $dir");
            if ($asset = $loader->register(realpath($dir))) {
                $this->logger->debug("Found asset {$asset->name} @ $dir");
                $this->updateAssetResource($asset);
            }
        }
    }

    protected function updateAssetResource($asset)
    {
        $updater = new ResourceUpdater;
        $updater->update($asset);
    }
}
