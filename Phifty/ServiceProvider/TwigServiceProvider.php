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
use TwigReactDirective\ReactDirectiveExtension;
use PJSON\PJSONEncoder;
use CodeGen\Expr\NewObject;
use Phifty\Kernel;

/**
 * Depends on AssetServiceProvider.
 */
class TwigServiceProvider extends BaseServiceProvider
{
    public function getId()
    {
        return 'Twig';
    }

    public static function canonicalizeConfig(Kernel $k, array $options)
    {
        // Rewrite template directories
        $templateDirs = array();
        if (isset($options['TemplateDirs']) && $options['TemplateDirs']) {
            $templateDirs = array_map(function ($dir) {
                return PH_APP_ROOT.DIRECTORY_SEPARATOR.$dir;
            }, $options['TemplateDirs']);
        }
        // Append fallback template dirs from plugin dir or framework plugin dir.
        $templateDirs[] = PH_APP_ROOT;
        $options['TemplateDirs'] = $templateDirs;

        // Rewrite environment config
        if ($k->isDev) {
            $options['Environment']['debug'] = true;
            $options['Environment']['auto_reload'] = true;
            $options['Environment']['cache'] = $k->cacheDir.DIRECTORY_SEPARATOR.'twig';
        } else {
            // for production
            $options['Environment']['optimizations'] = true;
            $options['Environment']['cache'] = $k->cacheDir.DIRECTORY_SEPARATOR.'twig';
        }

        if (isset($options['Namespaces'])) {
            foreach ($options['Namespaces'] as $name => &$dir) {
                $dir = realpath($dir);
                // $loader->addPath(PH_APP_ROOT . DIRECTORY_SEPARATOR . $dir, $namespace);
                // = array_map('realpath', $options['Namespaces']);
            }
        }

        return $options;
    }

    public static function generateNew(Kernel $k, array &$options = array())
    {
        $className = get_called_class();
        if (isset($options['Environment']['cache'])) {
            $cacheDir = $options['Environment']['cache'];
            if (!file_exists($cacheDir)) {
                @mkdir($cacheDir, 0777);
            }
        }

        return new NewObject($className, []);
    }

    public function boot(Kernel $k)
    {
        foreach ($k->bundles as $bundle) {
            if ($bundle->exportTemplates) {
                // register the loader to events
                $dir = $bundle->getTemplateDir();
                if (file_exists($dir)) {
                    $k->twig->loader->addPath($dir, $bundle->getNamespace());
                }
            }
        }
    }


    public function register(Kernel $k, array $options = array())
    {
        $k->factory('twigLoaderFilesystem', function($k) use ($options) {
            // Create The Filesystem Loader
            $loader = new Twig_Loader_Filesystem($options['TemplateDirs']);

            /*
             * Template namespaces must be added after $loader is initialized.
             */
            if (isset($options['Namespaces'])) {
                foreach ($options['Namespaces'] as $namespace => $dir) {
                    $loader->addPath($dir, $namespace);
                }
            }

            return $loader;
        });

        $k->factory('twigEnvironment', function($k, $loader) use ($options) {
            // http://www.twig-project.org/doc/api.html#environment-options
            $env = new Twig_Environment($loader, $options['Environment']);

            if ($k->isDev) {
                $env->addExtension(new Twig_Extension_Debug());
            } else {
                $env->addExtension(new Twig_Extension_Optimizer());
            }
            // $env->addExtension(new Twig_Extension_Core);
            $env->addExtension(new Twig_Extensions_Extension_Text());
            $env->addExtension(new Twig_Extensions_Extension_I18n());

            // load markdown twig extension
            if (class_exists('Twig_Extension_Markdown', true)) {
                $env->addExtension(new Twig_Extension_Markdown());
            }

            // include assettoolkit extension
            if ($asset = $k->asset) {
                $env->addExtension(new AssetExtension($asset->config, $asset->loader));
            }

            $reactDirExt = new ReactDirectiveExtension();
            $reactDirExt->setJsonEncoder(new PJSONEncoder());
            $env->addExtension($reactDirExt);


            // TODO: we should refactor this
            $exports = [
                'uniqid' => 'uniqid',
                'md5' => 'md5',
                'time' => 'time',
                'sha1' => 'sha1',
                'gettext' => 'gettext',
                '_' => '_',
                'count' => 'count',
                'new' => 'Phifty\View\newObject',
            ];
            foreach ($exports as $export => $func) {
                $env->addFunction(new \Twig_SimpleFunction($export, $func));
            }

            // TODO: make this static
            $zhDate = new Twig_SimpleFilter('zh_date', function ($str) {
                return str_replace(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun',
                                    'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', ],
                                   ['一', '二', '三', '四', '五', '六', '日',
                                    '一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月', ], $str);
            });
            $env->addFilter($zhDate);

            if ($locale = $k->locale) {
                $env->addGlobal('currentLang', $locale->current());
            }
            $env->addGlobal('Kernel', $k);


            // auto-register all native PHP functions as Twig functions
            $env->registerUndefinedFunctionCallback(function ($name) {
                // use functions with prefix 'array_' and 'str'
                if (function_exists($name) && (strpos($name, 'array_') === 0 || strpos($name, 'str') === 0)) {
                    return new Twig_SimpleFunction($name, $name);
                }

                return false;
            });
            return $env;
        });

        $k->twig = function ($k) use ($options) {
            $loader = $k->getObject('twigLoaderFilesystem', [$k]);
            $env = $k->getObject('twigEnvironment', [$k, $loader]);
            return (object) array(
                'loader' => $loader,
                'env' => $env,
            );
        };
    }
}
