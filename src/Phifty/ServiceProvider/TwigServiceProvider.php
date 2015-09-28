<?php
namespace Phifty\ServiceProvider;
use Twig_Environment;
use Twig_Loader_Filesystem;
use Twig_SimpleFunction;
use Twig_SimpleFilter;

use Twig_Extension_Core;
use Twig_Extension_Debug;
use Twig_Extension_Optimizer;
use Twig_Extensions_Extension_Text;
use Twig_Extensions_Extension_I18n;
use Twig_Extension_Markdown;
use AssetKit\Extension\Twig\AssetExtension;

use CodeGen\Expr\NewObject;
use Phifty\Kernel;


/**
 * Depends on AssetServiceProvider
 */

class TwigServiceProvider extends BaseServiceProvider
{

    public function getId()
    {
        return 'Twig';
    }

    static public function generateNew(Kernel $kernel, array & $options = array())
    {
        $className = get_called_class();

        // preprocess twig configs

        $templateDirs = array();
        if (isset($options['TemplateDirs']) && $options['TemplateDirs']) {
            $templateDirs = array_map(function($dir) {
                return PH_APP_ROOT . DIRECTORY_SEPARATOR . $dir;
            }, $options['TemplateDirs']);
        }

        // append fallback template dirs from plugin dir or framework plugin dir.
        $templateDirs[] = PH_APP_ROOT;
        $options['TemplateDirs'] = $templateDirs;

        if ($kernel->isDev) {
            $envOptions['debug'] = true;
            $envOptions['auto_reload'] = true;
        } else {
            // for production
            $envOptions['optimizations'] = true;
            $envOptions['cache'] = $kernel->cacheDir . DIRECTORY_SEPARATOR . 'twig';
            if (!file_exists($envOptions['cache'])) {
                @mkdir($envOptions['cache'], 0777);
            }
        }

        // override from config
        if (isset($options['Environment']) && $options['Environment']) {
            $envOptions = array_merge($envOptions , $options['Environment'] );
        }
        $options['Environment'] = $envOptions;
        return new NewObject($className, [$options]);
    }

    public function register($kernel, $options = array() )
    {
        $kernel->twig = function() use($kernel, $options) {

            // create the filesystem loader
            $loader   = new Twig_Loader_Filesystem($this->config['TemplateDirs']);

            /**
             * Template namespaces must be added after $loader is initialized.
             */
            if (isset($options['Namespaces'])) {
                foreach ($options['Namespaces'] as $namespace => $dir) {
                    $loader->addPath(PH_APP_ROOT . DIRECTORY_SEPARATOR . $dir, $namespace);
                }
            }

            // http://www.twig-project.org/doc/api.html#environment-options
            $env = new Twig_Environment($loader, $options['Environment']);

            if ($kernel->isDev) {
                $env->addExtension(new Twig_Extension_Debug);
            } else {
                $env->addExtension(new Twig_Extension_Optimizer);
            }
            $env->addExtension(new Twig_Extension_Core);
            $env->addExtension(new Twig_Extensions_Extension_Text);
            $env->addExtension(new Twig_Extensions_Extension_I18n);

            // load markdown twig extension
            if (class_exists('Twig_Extension_Markdown',true)) {
                $env->addExtension( new Twig_Extension_Markdown);
            }


            // include assettoolkit extension
            if ($asset = kernel()->asset) {
                $assetExt = new AssetExtension();
                $assetExt->setAssetConfig( kernel()->asset->config );
                $assetExt->setAssetLoader( kernel()->asset->loader );
                $env->addExtension($assetExt);
            }

            // TODO: we should refactor this
            $exports = array(
                'uniqid' => 'uniqid',
                'md5' => 'md5',
                'time' => 'time',
                'sha1' => 'sha1',
                'gettext' => 'gettext',
                '_' => '_',
                'count' => 'count',
                'new' => 'Phifty\View\newObject',
            );
            foreach ($exports as $export => $func) {
                $env->addFunction(new Twig_SimpleFunction($export, $func));
            }

            $zhDate = new Twig_SimpleFilter('zh_date', function ($str) {
                return str_replace(['Mon','Tue','Wed','Thu','Fri','Sat','Sun',
                                    'Jan','Feb','Mar','Apr','May','Jun','July','Aug','Sep','Oct','Nov','Dec'],
                                   ['一','二','三','四','五','六','日',
                                    '一月','二月','三月','四月','五月','六月','七月','八月','九月','十月','十一月','十二月'], $str);
            });
            $env->addFilter($zhDate);

            // kernel()->event->trigger('phifty.service.twig', $env );
            if (kernel()->locale) {
                $env->addGlobal('currentLang', kernel()->locale->current());
            }
            $env->addGlobal('Kernel', kernel());

            // auto-register all native PHP functions as Twig functions
            $env->registerUndefinedFunctionCallback(function($name) {
                // use functions with prefix 'array_' and 'str'
                if (function_exists($name) && ( strpos($name,'array_') === 0 || strpos($name,'str') === 0 ) ) {
                    return new Twig_SimpleFunction($name, $name);
                }
                return false;
            });

            return (object) array(
                'loader' => $loader,
                'env' => $env,
            );
        };
    }
}
