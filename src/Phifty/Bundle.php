<?php
namespace Phifty;
use ReflectionObject;
use Exception;
use ConfigKit\Accessor;
use LogicException;
use Phifty\Kernel;

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
            $this->setConfig($this->mergeWithDefaultConfig($config));
        } else {
            $this->setConfig($this->defaultConfig());
        }

        $this->kernel = kernel();
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

    public function mergeWithDefaultConfig( $config = array() )
    {
        return array_merge( $this->defaultConfig() , $config ?: array() );
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
        if ( $this->_namespace )
            return $this->_namespace;
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
    public function mount($path, $class)
    {
        // TODO: reduce the autoload checking cost.
        if (!class_exists($class,true)) {
            $class = $this->getNamespace() . '\\' . $class;
        }
        $controller = new $class;
        $this->kernel->mux->mount($path, $controller);
    }

    /**
     * Route definition method, users define bundle routes in this method.
     */
    public function routes() { }


    public function addCRUDAction( $model, $types = array() )
    {
        @trigger_error('addCRUDAction will be deprecated, please use addRecordAction instead', E_USER_DEPRECATED);
        return $this->addRecordAction( $model, $types );
    }

    /**
     * Register/Generate CRUD actions
     *
     * @param string $model model class
     * @param array  $types action types (Create, Update, Delete, BulkCopy, BulkDelete.....)
     */
    public function addRecordAction( $model, $types = array() ) {
        if ( empty($types) ) {
            $types = $this->defaultActionTypes;
        }

        $self = $this;
        $this->kernel->event->register('phifty.before_action', function() use ($self, $types, $model) {
            $self->kernel->action->registerAction('RecordActionTemplate', array(
                'namespace' => $self->getNamespace(),
                'model' => $model,
                'types' => (array) $types
            ));
        });
    }

    /**
     * Register/Generate update ordering action
     *
     * @param string $model model class
     */
    public function addUpdateOrderingAction($model) {
        $self = $this;
        $this->kernel->event->register('phifty.before_action', function() use ($self, $model) {
            $self->kernel->action->registerAction('UpdateOrderingRecordActionTemplate', array(
                'namespace' => $self->getNamespace(),
                'model' => $model
            ));
        });
    }

    /**
     * Returns template directory path.
     */
    public function getTemplateDir()
    {
        return $this->locate() . DIRECTORY_SEPARATOR . 'Templates';
    }

    public function getTranslationDir()
    {
        return $this->locate() . DIRECTORY_SEPARATOR . 'Translation';
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
     * Get config directory
     */
    public function getConfigDir()
    {
        return $this->locate() . DIRECTORY_SEPARATOR . 'Config';
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
     * Get asset directory list, this is for registering bundle assets.
     *
     * @return string[]
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

    /**
     * Return assets for asset loader.
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
        $assetNames = $this->assets();
        if ( ! empty($assetNames) ) {
            $loader->loadAssets($assetNames);
        }
    }

    public static function getInstance($kernel = null, $config = array())
    {
        static $instance;
        if ( $instance ) {
            return $instance;
        }
        return $instance = new static($kernel, $config);
    }

}
