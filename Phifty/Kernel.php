<?php

namespace {

    global $kernel;

    /**
        * kernel() is a global shorter helper function to get Phifty\Kernel instance.
        *
        * Initialize kernel instance, classloader, bundles and services.
        *
        * @return Phifty\Kernel
        */
    function kernel()
    {
        global $kernel;
        return $kernel;
    }

}

namespace Phifty {

use Universal\Container\ObjectContainer;
use Phifty\ServiceProvider\ServiceProvider;
use Exception;
use ConfigKit\ConfigLoader;
use Phifty\Environment\CommandLine;
use Phifty\Environment\Production;
use Phifty\Environment\Development;

/*
Types for environment

abstract class Environment {}
class Development extends Environment { }
class Staging     extends Environment { }
class Production  extends Environment { }
*/

class Kernel extends ObjectContainer
{
    /* framework version */
    const FRAMEWORK_ID = 'phifty';

    const VERSION = '2.6.0';

    public $applicationID;

    public $applicationName;

    public $applicationUUID;

    public $frameworkDir;

    public $frameworkAppDir;

    public $frameworkBundleDir;

    public $cacheDir;

    public $rootDir;  // application root dir

    public $rootAppDir;   // application dir (./app)

    public $rootBundleDir;

    public $webroot;

    /**
     * @var boolean is in development mode ? 
     */
    public $isDev = true;

    /**
     * @var boolean is in commandl line mode ? 
     */
    public $isCli = false;

    public $environment;

    /**
     * @param ServiceProvider[string serviceId]
     */
    protected $services = [];

    protected $app;

    /**
     * private properties will not be exported in AppKernel
     */
    private $configLoader;

    public function __construct(ConfigLoader $configLoader, $environment)
    {
        // define framework environment
        $this->configLoader = $configLoader;
        $this->environment  = $environment;
        $this->isDev = $this->environment === 'development';
        $this->isCli = PHP_SAPI === 'cli';
    }

    public function getApp()
    {
        return \App\App::getInstance($this, []);
    }

    public function boot()
    {
        foreach ($this->services as $spId => $sp) {
            $sp->boot($this);
        }
    }

    /**
     * Create the dynamic kernel.
     *
     * TODO: extract path parameters to config.
     */
    public static function dynamic(ConfigLoader $configLoader, $environment = null, $appRoot = PH_APP_ROOT)
    {
        if (!$environment) {
            $environment = getenv("PHIFTY_ENV") ?: 'development';
        }

        $kernel = new static($configLoader, $environment);

        // build path info
        $kernel->frameworkDir       = self::getFrameworkRoot();
        $kernel->frameworkAppDir    = self::getFrameworkRoot() . DIRECTORY_SEPARATOR . 'app';

        $kernel->rootDir            = $appRoot;
        $kernel->rootAppDir         = $appRoot . DIRECTORY_SEPARATOR . 'app';

        $kernel->webroot            = $appRoot . DIRECTORY_SEPARATOR . 'webroot';
        $kernel->cacheDir           = $appRoot . DIRECTORY_SEPARATOR . 'cache';

        $kernel->applicationUUID = $configLoader->framework->ApplicationUUID;
        $kernel->applicationID   = $configLoader->framework->ApplicationID;
        $kernel->applicationName = $configLoader->framework->ApplicationName;
        $kernel->configLoader    = $configLoader;
        return $kernel;
    }

    /**
     * Dynamically get the framework root dir.
     *
     * @return path
     */
    public static function getFrameworkRoot()
    {
        return dirname(__DIR__);
    }


    /**
     * TODO: A better place to put this method
     */
    public function getHost()
    {
        if ($domain = $this->config->get('framework','Domain')) {
            return $domain;
        }
        if (isset($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        }
        throw new Exception("Domain is not configured in config file.");
    }


    /**
     * @version 3.1.0
     */
    public function buildUrl($path, array $params = null)
    {
        $url = $this->getBaseUrl() . $path;
        if ($params) {
            $url .= '?' . http_build_query($params);
        }
        return $url;
    }

    public function getBaseUrl()
    {
        $scheme = $this->config->get('framework','SSL') ? 'https://' : 'http://';
        $port = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 80) ? ":" . $_SERVER['SERVER_PORT'] : "";
        return $scheme . $this->getHost() . $port;
    }

    public function getHostBaseUrl()
    {
        return $this->getBaseUrl();
    }

    public function getSystemMail()
    {
        $mailConfig = $this->config->get('framework','Mail');
        if (isset($mailConfig['System'])) {
            $mail = $mailConfig['System'];
            if( preg_match( '#"?(.*?)"??\s+<(.*?)>#i' , $mail ,$regs ) ) {
                return array( $regs[2] => $regs[1] );
            } else {
                return array(
                    $mail => $this->getApplicationName(),
                );
            }
        }
        // the default mailer
        return array(
            'no-reply@' . $this->getHost() => $this->getApplicationName(),
        );
    }

    public function getAdminMail()
    {
        $mailConfig = $this->config->get('framework','Mail');
        if (isset($mailConfig['Admin'])) {
            $mail = $mailConfig['Admin'];
            if (preg_match( '#"?(.*?)"??\s+<(.*?)>#i' , $mail ,$regs )) {
                return array(
                    /* address => name */
                    $regs[2] => $regs[1],
                );
            } else {
                return array( $mail => 'Administrator');
            }
        }
        throw new Exception('The Email address of Administrator is not defined.');
    }



    public function getVersion()
    {
        return self::VERSION;
    }

    public function getCacheDir()
    {
        return $this->cacheDir;
    }



    /**
     * Get application UUID from config
     *
     * @return string Application UUID
     */
    public function getApplicationUUID()
    {
        return $this->ApplicationUUID;
    }

    public function getApplicationID()
    {
        return $this->applicationID;
    }

    /**
     * Get current application name from config
     *
     * @return string Application name
     */
    public function getApplicationName()
    {
        return $this->applicationName;
    }


    /**
     * Register service object into this Kernel object
     *
     * @param ServiceProvider $service
     */
    public function registerServiceProvider(ServiceProvider $service, array $options = array())
    {
        $service->register($this , $options);
        $this->services[$service->getId()] = $service;
    }


    public function getServices()
    {
        return $this->services;
    }

    /**
     * Get service object by its identifier
     *
     * @param  string  $id
     * @return Service object
     */
    public function service($id)
    {
        if (isset($this->services[ $id ])) {
            return $this->services[ $id ];
        }
    }

    /**
     * Get plugin object from plugin service
     *
     * backward-compatible
     *
     * @param string $name
     */
    public function plugin($name)
    {
        return $this->bundles->get($name);
    }



    /**
     * Since we are migrating plugin to bundle.
     *
     * @param string $name
     */
    public function bundle($name, $lookup = false)
    {
        return $this->bundles->get($name);
    }

    /**
     * return framework id
     */
    public function getFrameworkId()
    {
        return self::FRAMEWORK_ID;
    }

    public static function getInstance()
    {
        static $one;
        if ( $one ) {
            return $one;
        }
        return $one = new static;
    }


    /**
     * Handle request from rewrite rule or php-fpm
     */
    public function handle($pathinfo)
    {
        try {
            // allow origin: https://developer.mozilla.org/en-US/docs/HTTP/Access_control_CORS
            header( 'Access-Control-Allow-Origin: http://' . $_SERVER['HTTP_HOST'] );
            $this->event->trigger('phifty.before_path_dispatch');
            if( $r = $this->router->dispatch( $pathinfo ) ) {
                $this->event->trigger('phifty.before_page');
                echo $r->run();
                $this->event->trigger('phifty.after_page');
            } else {
                // header('HTTP/1.0 404 Not Found');
                echo "<h3>Page not found.</h3>";
            }
        } catch (Exception $e) {
            if ($this->isDev ) {
                if( class_exists('CoreBundle\\Controller\\ExceptionController',true) ) {
                    $controller = new \CoreBundle\Controller\ExceptionController;
                    echo $controller->indexAction($e);
                } else {
                    // simply throw exception
                    throw $e;
                }
            } else {
                @header('HTTP/1.1 500 Internal Server Error');
                die($e->getMessage());
            }
        }
    }
}

}
