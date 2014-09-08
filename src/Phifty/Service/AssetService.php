<?php
namespace Phifty\Service;
use AssetKit;
use AssetKit\AssetConfig;
use AssetKit\AssetLoader;
use AssetKit\AssetCompiler;
use AssetKit\AssetRender;
use AssetKit\Cache;
use UniversalCache\ApcCache;
use UniversalCache\FileSystemCache;
use UniversalCache\UniversalCache;
use Exception;

class AssetService
    implements ServiceInterface
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
        $kernel->asset = function() use ($kernel) {
            $assetFile = PH_APP_ROOT . DIRECTORY_SEPARATOR . '.assetkit.php';
            $config = new AssetConfig( $assetFile ,
                $kernel->environment === 'production'
                ? array( 'environment' => AssetConfig::PRODUCTION )
                : array( 'environment' => AssetConfig::DEVELOPMENT )
            );

            $cache = new UniversalCache(array(  
                new ApcCache(array( 'namespace' => $config->getNamespace() )),
                new FileSystemCache(array( 'cache_dir' => $kernel->getCacheDir() )),
            ));
            $config->setCache($cache);
            $config->setRoot(PH_APP_ROOT);

            $loader   = new AssetLoader($config);
            $render   = new AssetRender($config,$loader);
            // $render->force();
            $compiler = $render->getCompiler();
            $compiler->defaultJsCompressor = 'uglifyjs';
            $compiler->defaultCssCompressor = null;
            // $compiler->enableProductionFstatCheck();

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
