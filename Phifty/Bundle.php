<?php

namespace Phifty;

use ReflectionObject;
use Exception;
use ConfigKit\Accessor;
use LogicException;
use Phifty\Kernel;
use Phifty\Controller;
use ConfigKit\ConfigCompiler;

use Phifty\Generator\AppActionGenerator;

use Phifty\Bundle\BundleActionCreators;
use Phifty\Bundle\BundleRouteCreators;

/**
 *  Bundle is the base class of App, Core, {Plugin} class.
 */
class Bundle
{
    use BundleActionCreators;
    use BundleRouteCreators;


    /**
     * @var Phifty\Kernel phifty kernel object
     */
    protected $kernel;


    /**
     * @var array bundle config stash
     */
    public $config;

    /**
     * @var string the bundle class directory, used for caching the locate() result.
     */
    protected $_baseDir;

    /**
     * @var string cached namespace from reflection class
     */
    protected $_namespace;


    /**
     * @var boolean export templates directory to twig file system loader
     *
     * TODO we should let twig loader to get template directories forwardly.
     */
    public $exportTemplates = true;

    /**
     * TODO: force config to be array.
     */
    public function __construct(Kernel $kernel, array $config = null)
    {
        $this->kernel = $kernel;
        if ($config) {
            $this->config = $this->mergeWithDefaultConfig($config);
        } else {
            $this->config = $this->defaultConfig();
        }

        // TODO: currently we are triggering the loadAssets from Phifty\Web
        // $this->kernel->event->register('asset.load', array($this,'loadAssets'));
        //
        // Move the template registration to the TwigServiceProvider

        // we should have twig service
        if ($this->exportTemplates && isset($this->kernel->twig)) {
            // register the loader to events
            $dir = $this->getTemplateDir();
            if (file_exists($dir)) {
                $this->kernel->twig->loader->addPath($dir, $this->getNamespace());
            }
        }
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function mergeWithDefaultConfig(array $config = array())
    {
        return array_merge($this->defaultConfig(), $config);
    }

    public function defaultConfig()
    {
        return array();
    }

    public function boot()
    {

    }

    public function getId()
    {
        return $this->getNamespace();
    }


    /**
     * get the namespace name,
     *
     * for \Product\Application, we get Product.
     *
     * */
    public function getNamespace()
    {
        if ($this->_namespace) {
            return $this->_namespace;
        }
        $object = new ReflectionObject($this);
        return $this->_namespace = $object->getNamespaceName();
    }


    /**
     * Locate bundle dir path.
     */
    public function locate()
    {
        if ($this->_baseDir) {
            return $this->_baseDir;
        }
        $object = new ReflectionObject($this);
        return $this->_baseDir = dirname($object->getFilename());
    }





    /**
     * Get controller object
     *
     * @param string $name Controller Name
     */
    public function getController($name)
    {
        $class = $this->getNamespace() . "\\Controller\\$name";
        return new $class;
    }


    public function getAction($name)
    {
        $class = $this->getNamespace() . "\\Action\\$name";
        return new $class;
    }




    /**
     * Get bundle config
     *
     * @param string $key config key
     * @return mixed
     */
    public function config($key)
    {
        if (isset($this->config[ $key ])) {
            if (is_array($this->config[ $key ])) {
                return new Accessor($this->config[ $key ]);
            }
            return $this->config[ $key ];
        }
        if (strchr($key, '.') !== false) {
            $parts = explode('.', $key);
            $ref = $this->config;
            while ($refKey = array_shift($parts)) {
                if (is_array($ref) && isset($ref[ $refKey ])) {
                    $ref = & $ref[ $refKey ];
                    continue;
                } else {
                    return null;
                }
            }
            if (is_array($ref)) {
                return new Accessor($ref);
            }
            return $ref;
        }
        return null;
    }

    /**
     * Route definition method, users define bundle routes in this method.
     */
    public function routes()
    {
    }

    /**
     * overridable method for defining user action classes.
     */
    public function actions()
    {
    }

    /**
     * Return assets for asset loader.
     *
     * @return array asset names
     */
    public function assets()
    {
        return array();
    }




    /**
     * Returns template directory path.
     *
     * @group path
     * @return path
     */
    public function getTemplateDir()
    {
        return $this->locate() . DIRECTORY_SEPARATOR . 'Templates';
    }


    /**
     * return the model dir
     */
    public function getModelDir()
    {
        return $this->locate() . DIRECTORY_SEPARATOR . 'Model';
    }


    /**
     *
     * @group path
     * @return path
     */
    public function getTranslationDir()
    {
        return $this->locate() . DIRECTORY_SEPARATOR . 'Translation';
    }

    /**
     *
     * @group path
     * @return path
     */
    public function getAssetDir()
    {
        return $this->locate() . DIRECTORY_SEPARATOR . 'Assets';
    }

    /**
     *
     * @group path
     * @return path
     */
    public function getTestDir()
    {
        return $this->locate() . DIRECTORY_SEPARATOR . 'Tests';
    }

    public function getTranslation($locale)
    {
        $file = $this->getTranslationDir() . DIRECTORY_SEPARATOR . "$locale.yml";
        if (file_exists($file)) {
            return ConfigCompiler::parse($file);
        }
        return array();
    }

    public function saveTranslation($locale, $dict)
    {
        $file = $this->getTranslationDir() . DIRECTORY_SEPARATOR . "$locale.yml";
        return ConfigCompiler::write_yaml($file, $dict);
    }



    /**
     * Return schema object array for initializing database.
     *
     * @return Schema[]
     */
    public function getSchemas()
    {
        return [];
    }


    /**
     * Get config directory
     *
     * @return path
     */
    public function getConfigDir()
    {
        return $this->locate() . DIRECTORY_SEPARATOR . 'Config';
    }

    /**
     * Get asset directory list, this is for registering bundle assets.
     *
     * @return path[]
     */
    public function getAssetDirs()
    {
        // XXX: Here we got a absolute path,
        // should return relative path here.
        $assetDir = $this->locate() . DIRECTORY_SEPARATOR . 'Assets';
        if ($list = futil_scanpath_dir($assetDir)) {
            return $list;
        }
        return array();
    }

    /**
     * Get the asset list for loading
     */
    public function getAssets()
    {
        $assetConfig = $this->config('Assets');
        if ($assetConfig) {
            $assets =  $assetConfig->toArray();
        } else {
            // Get the assets provided by Bundle
            $assets = $this->assets();
        }

        // Append the extra assets if we've defined them
        $extraAssetsConfig = $this->config('ExtraAssets');
        if ($extraAssetsConfig) {
            $assets =  array_merge($assets, $assetsConfig->toArray());
        }
        return $assets;
    }


    /**
     * Get the asset loader and load these assets.
     *
     * TODO: move this behavior out.
     */
    public function loadAssets()
    {
        $loader = $this->kernel->asset->loader;
        $assetNames = $this->getAssets();
        if (! empty($assetNames)) {
            $loader->loadAssets($assetNames);
        }
    }

    public static function getInstance(Kernel $kernel = null, $config = [])
    {
        static $instance;
        if (!$instance) {
            return $instance = new static($kernel, $config);
        }
        return $instance;
    }
}
