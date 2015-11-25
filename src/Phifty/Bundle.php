<?php
namespace Phifty;
use ReflectionObject;
use Exception;
use ConfigKit\Accessor;
use LogicException;
use Phifty\Kernel;
use Phifty\Controller;

/**
 *  Bundle is the base class of App, Core, {Plugin} class.
 */
class Bundle
{

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


    public $defaultActionTypes = array(
        array('prefix' => 'Create'),
        array('prefix' => 'Update'),
        array('prefix' => 'Delete'),
        array('prefix' => 'BulkDelete')
    );

    public $exportTemplates = true;

    public function __construct(Kernel $kernel, $config = array())
    {
        $this->kernel = $kernel;
        if ($config) {
            if ($config instanceof Accessor) {
                $this->config = $this->mergeWithDefaultConfig($config->toArray());
            } else {
                $this->config = $this->mergeWithDefaultConfig($config);
            }
        } else {
            $this->config = $this->defaultConfig();
        }

        // XXX: currently we are triggering the loadAssets from Phifty\Web
        // $this->kernel->event->register('asset.load', array($this,'loadAssets'));

        // we should have twig service
        if ($this->exportTemplates && isset($this->kernel->twig)) {
            // register the loader to events
            $dir = $this->getTemplateDir();
            if (file_exists($dir)) {
                $this->kernel->twig->loader->addPath($dir, $this->getNamespace() );
            }
        }
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function mergeWithDefaultConfig(array $config = array())
    {
        return array_merge($this->defaultConfig() , $config);
    }

    public function defaultConfig()
    {
        return array();
    }


    public function init()
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
     * Helper method, route path to template.
     *
     *    $this->page('/about.html', 'about.html' ,array( 'name' => 'foo' ));
     *
     * @param string $path
     * @param string $template file
     */
    public function page( $path , $template , $args = array() )
    {
        $router = $this->kernel->router;
        $router->add( $path , array(
            'template' => $template,
            'args' => $args,  // template args
        ));
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
     * Get the model in the namespace of current microapp
     *
     * @param string $name Model Name
     */
    public function getModel( $name )
    {
        $class = $this->getNamespace() . "\\Model\\$name";
        return new $class;
    }


    /**
     * Get controller object
     *
     * @param string $name Controller Name
     */
    public function getController( $name )
    {
        $class = $this->getNamespace() . "\\Controller\\$name";
        return new $class;
    }


    public function getAction( $name )
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
    public function config( $key )
    {
        if ( isset($this->config[ $key ]) ) {
            if ( is_array( $this->config[ $key ] ) ) {
                return new Accessor($this->config[ $key ]);
            }
            return $this->config[ $key ];
        }
        if ( strchr( $key , '.' ) !== false ) {
            $parts = explode( '.' , $key );
            $ref = $this->config;
            while ( $refKey = array_shift( $parts ) ) {
                if ( is_array($ref) && isset($ref[ $refKey ]) ) {
                    $ref = & $ref[ $refKey ];
                    continue;
                } else {
                    return null;
                }
            }
            if ( is_array($ref) ) {
                return new Accessor($ref);
            }
            return $ref;
        }
        return null;
    }



    /**
     * XXX: make this simpler......orz
     *
     *
     * In route method, we can do route with:
     *
     * $this->route('/path/to', array(
     *          'controller' => 'ControllerClass'
     *  ))
     * $this->route('/path/to', 'ControllerClass' );
     *
     * Mapping to actionNameAction method.
     *
     * $this->route('/path/to', 'ControllerClass:actionName' );
     *
     * $this->route('/path/to', '+App\Controller\IndexController:actionName' );
     *
     * $this->route('/path/to', array(
     *          'template' => 'template_file.html',
     *          'args' => array( ... ) )
     * )
     *
     * $this->route('/path/to', array(
     *          'controller' => 'ControllerClass',
     * ))
     *
     * TODO: improve the performance here.
     */
    public function route($path, $args, array $options = array())
    {
        $router = $this->kernel->rootMux;
        if (!$router) {
            return false;
        }

        // if args is string, it's a controller:action spec
        if (is_string($args)) {
            // Extract action method name out, and set default to run method.
            //    FooController:index => array(FooController, indexAction)
            $class = null;

            // the default action method name
            $action = 'indexAction';
            if (false !== ($pos = strrpos($args,':'))) {
                list($class,$action) = explode(':',$args);
                if (false === strrpos( $action , 'Action' )) {
                    $action .= 'Action';
                }
            } else {
                $class = $args;
            }

            // Convert controlelr class name to full-qualified name
            // If it's not full-qualified classname, we should prepend our base namespace.
            if ($class[0] === '+' || $class[0] === '\\') {
                $class = substr( $class , 1 );
            } else {
                $class = $this->getNamespace() . "\\Controller\\$class";
            }

            if (! method_exists($class,$action) ) {
                // FIXME, it's broken if class is not loaded.
                // throw new Exception("Controller action <$class:$action>' does not exist.");
            }

            $router->add($path, array($class,$action), $options);

        } else if (is_array($args)) {

            // route to template controller ?
            if (isset($args['template']) ) {
                $options['args'] = array(
                    'template' => $args['template'],
                    'template_args' => ( isset($args['args']) ? $args['args'] : null),
                );
                $router->add( $path , '\Phifty\Routing\TemplateController' , $options );

            } else if ( isset($args['controller']) ) { // route to normal controller ?

                $router->add( $path , $args['controller'], $options );

            } else if ( isset($args[0]) && count($args) == 2 ) { // simply treat it as a callback

                $router->add( $path , $args , $options );

            } else {

                throw new LogicException('Unsupport route argument.');

            }
        } else {
            throw new LogicException( "Unsupported route argument." );
        }
    }


    /**
     *
     * @param string $path path name
     * @param string $class class name
     */
    public function mount($path, $className)
    {
        $class = $className;
        if (!class_exists($class,true)) {
            $class = $this->getNamespace() . '\\' . $className;
        }
        if (!class_exists($class,true)) {
            $class = $this->getNamespace() . '\\Controller\\' . $className;
        }
        $controller = new $class;
        $this->kernel->rootMux->mount($path, $controller);
    }

    public function expandRoutes($path, $class)
    {
        @trigger_error('expandRoutes() will be deprecated, please use mount() instead', E_USER_DEPRECATED);
        $this->mount($path, $class);
    }

    /**
     * Route definition method, users define bundle routes in this method.
     */
    public function routes() { }


    public function addCRUDAction($modelClass, $types = array() )
    {
        @trigger_error('addCRUDAction will be deprecated, please use addRecordAction instead', E_USER_DEPRECATED);
        return $this->addRecordAction($modelClass, $types );
    }

    /**
     * Register/Generate CRUD actions
     *
     * @param string $modelClass model class
     * @param array  $types action types (Create, Update, Delete, BulkCopy, BulkDelete.....)
     */
    public function addRecordAction($modelClass, $types = array() ) {
        if ( empty($types) ) {
            $types = $this->defaultActionTypes;
        }

        $self = $this;
        $this->kernel->event->register('phifty.before_action', function() use ($self, $types, $modelClass) {
            $self->kernel->action->registerAction('RecordActionTemplate', array(
                'namespace' => $self->getNamespace(),
                'model' => $modelClass,
                'types' => (array) $types,
            ));
        });
    }

    /**
     * Register/Generate update ordering action
     *
     * @param string $modelClass model class
     */
    public function addUpdateOrderingAction($modelClass)
    {
        $self = $this;
        $this->kernel->event->register('phifty.before_action', function() use ($self, $modelClass) {
            $self->kernel->action->registerAction('UpdateOrderingRecordActionTemplate', array(
                'namespace' => $self->getNamespace(),
                'model' => $modelClass,
            ));
        });
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
        $file = $this->getTranslationDir() . DIRECTORY_SEPARATOR . $locale . '.yml';
        if ( file_exists($file) ) {
            return yaml_parse( file_get_contents( $file ) );
        }
        return array();
    }

    public function saveTranslation($locale, $dict)
    {
        $file = $this->getTranslationDir() . DIRECTORY_SEPARATOR . $locale . '.yml';
        return file_put_contents($file, yaml_emit($dict, YAML_UTF8_ENCODING) );
    }



    /**
     * Return schema object array for initializing database.
     *
     * @return Schema[]
     */
    public function getSchemas() {
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
        if ( $list = futil_scanpath_dir($assetDir) ) {
            return $list;
        }
        return array();
    }

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
     * Return assets for asset loader.
     *
     * @return array asset names
     */
    public function assets()
    {
        return array();
    }


    /**
     * Get the asset loader and load these assets.
     */
    public function loadAssets()
    {
        $loader = $this->kernel->asset->loader;
        $assetNames = $this->getAssets();
        if (! empty($assetNames) ) {
            $loader->loadAssets($assetNames);
        }
    }

    public static function getInstance(Kernel $kernel = null, $config = array())
    {
        static $instance;
        if ( $instance ) {
            return $instance;
        }
        return $instance = new static($kernel, $config);
    }

}
