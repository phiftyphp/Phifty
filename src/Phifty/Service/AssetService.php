<?php
namespace Phifty\Service;
use AssetKit;
use AssetKit\AssetConfig;
use AssetKit\AssetLoader;
use AssetKit\AssetCompiler;
use AssetKit\ProductionAssetCompiler;
use AssetKit\AssetRender;
use AssetKit\Cache;
use AssetKit\CacheFactory;
use UniversalCache\ApcCache;
use UniversalCache\FileSystemCache;
use UniversalCache\UniversalCache;
use Exception;

class AssetService
    implements ServiceRegister
{

    public function getId()
    {
        return 'asset';
    }

    /**
     *
     * $kernel->asset->loader
     * $kernel->asset->writer
     */
    public function register($kernel, $options = array() )
    {
        $kernel->asset = function() use ($kernel, $options) {
            // $assetFile = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'config/assetkit.yml';
            $config = new AssetConfig($options);
            $config->setEnvironment($kernel->environment);
            $config->setNamespace($kernel->getApplicationUUID());
            $cache = new UniversalCache(array(
                new ApcCache(array( 'namespace' => $kernel->getApplicationUUID() )),
                new FileSystemCache(array( 'cache_dir' => $kernel->getCacheDir() )),
            ));
            $config->setCache($cache);
            $config->setRoot(PH_APP_ROOT);

            $loader   = new AssetLoader($config);
            $render   = new AssetRender($config, $loader);
            $compiler = $render->getCompiler();
            $compiler->defaultJsCompressor = 'uglifyjs';
            $compiler->defaultCssCompressor = null;
            return (object) array(
                'loader' => $loader,
                'config' => $config,
                'render' => $render,
                'compiler' => $compiler,
                'cache'    => $cache,
            );
        };
    }
}
