<?php
namespace Phifty;
use Phifty\Kernel;
use Phifty\Locale;
use Phifty\Web;
use Universal\Container\ObjectContainer;
use Phifty\ServiceProvider\ServiceProvider;
use Exception;
use ConfigKit\ConfigLoader;

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

    public $rootAppDir;   // application dir (./applications)

    public $rootBundleDir;

    public $webroot;

    /* boolean: is in development mode ? */
    public $isDev = true;


    public $environment = 'development';


    /**
     * @param ServiceProvider
     */
    protected $services = array();

    /**
     * application object pool
     *
     * app class name => app object
     *
     * @deprecated
     */
    protected $applications = array();

    protected $app;

    private $configLoader;

    public function __construct($environment = null)
    {
        // define framework environment
        $this->environment  = $environment ?: getenv('PHIFTY_ENV') ?: 'development';
        $this->isDev = $this->environment === 'development';
    }

    /**
     * To run prepare method, please define the PH_ROOT and PH_APP_ROOT first.
     */
    public function prepare(ConfigLoader $configLoader) 
    {
        // build path info
        $this->frameworkDir       = PH_APP_ROOT;
        $this->frameworkAppDir    = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'applications';
        $this->rootDir            = PH_APP_ROOT;      // Application root.
        $this->rootAppDir         = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'applications';
        $this->webroot            = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'webroot';
        $this->cacheDir           = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'cache';

        $this->applicationUUID = $configLoader->framework->ApplicationUUID;
        $this->applicationID   = $configLoader->framework->ApplicationID;
        $this->applicationName = $configLoader->framework->ApplicationName;

        $this->configLoader = $configLoader;
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


    public function getBaseUrl()
    {
        $scheme = $this->config->get('framework','SSL') ? 'https://' : 'http://';
        $port = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 80) ? ":" . $_SERVER['SERVER_PORT'] : "";
        return $scheme . $this->getHost() . $port;
    }

    public function getHostBaseUrl() {
        return $this->getBaseUrl();
    }

    public function getSystemMail() {
        $mailConfig = $this->config->get('framework','Mail');
        if ( isset($mailConfig['System']) ) {
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

    public function getAdminMail() {
        $mailConfig = $this->config->get('framework','Mail');
        if ( isset($mailConfig['Admin']) ) {
            $mail = $mailConfig['Admin'];
            if( preg_match( '#"?(.*?)"??\s+<(.*?)>#i' , $mail ,$regs ) ) {
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
    public function registerService(ServiceProvider $service, $options = array() )
    {
        $service->register($this , $options);
        $this->services[$service->getId()] = $service;
    }

    /**
     * Run initialize after services were registered.
     */
    public function init()
    {
        $this->event->trigger('phifty.before_init');
        $self = $this;

        $this->web = function() use ($self) {
            return new \Phifty\Web( $self );
        };

        // Turn off all error reporting
        if ($this->isDev || CLI) {
            \Phifty\Environment\Development::init($this);
        } else {
            \Phifty\Environment\Production::init($this);
        }

        if (CLI) {
            \Phifty\Environment\CommandLine::init($this);
        }

        if ( isset($this->session) ) {
            $this->session;
        }
        if ( isset($this->locale) ) {
            $this->locale;
        }

        $app = \App\App::getInstance($this, [ ]);
        $app->init();

        $this->bundles->init();
        $this->event->trigger('phifty.after_init');
    }

    public function getApp()
    {
        return $this->app;
    }

    /**
     * Get service object by its identifier
     *
     * @param  string  $id
     * @return Service object
     */
    public function service($id)
    {
        if ( isset($this->services[ $id ] ) ) {
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
        return $this->bundles->get( $name );
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
        }
        // Cache Twig Template Error
        /*
        catch ( Twig_Error_Runtime $e ) {
            # twig error exception
        
        }
        */
        catch ( Exception $e ) {
            if ($this->isDev ) 
            {
                if( class_exists('CoreBundle\\Controller\\ExceptionController',true) ) {
                    $controller = new \CoreBundle\Controller\ExceptionController;
                    echo $controller->indexAction($e);
                } else {
                    // simply throw exception
                    throw $e;
                }
            }
            else {
                @header('HTTP/1.1 500 Internal Server Error');
                die($e->getMessage());
            }
        }
        catch ( \Roller\Exception\RouteException $e ) {
            @header('HTTP/1.1 403');
            die( $e->getMessage() );
        }

    }


}
