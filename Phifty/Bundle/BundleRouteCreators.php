<?php

namespace Phifty\Bundle;

use LogicException;
use Phifty\Routing\TemplateController;

trait BundleRouteCreators
{

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
        $mux = $this->kernel->mux;
        if (!$mux) {
            throw new \LogicException('mux service is not enabled.');
        }

        // if args is string, it's a controller:action spec
        if (is_string($args)) {
            // Extract action method name out, and set default to run method.
            //    FooController:index => array(FooController, indexAction)
            $class = null;

            // the default action method name
            $action = 'indexAction';
            if (false !== ($pos = strrpos($args, ':'))) {
                list($class, $action) = explode(':', $args);
                if (false === strrpos($action, 'Action')) {
                    $action .= 'Action';
                }
            } else {
                $class = $args;
            }

            // Convert controlelr class name to full-qualified name
            // If it's not full-qualified classname, we should prepend our base namespace.
            if ($class[0] === '+' || $class[0] === '\\') {
                $class = substr($class, 1);
            } else {
                $class = $this->getNamespace() . "\\Controller\\$class";
            }

            if (! method_exists($class, $action)) {
                // FIXME, it's broken if class is not loaded.
                // throw new Exception("Controller action <$class:$action>' does not exist.");
            }

            $mux->any($path, [$class, $action] , $options);

        } else if (is_array($args)) {

            $controller = [TemplateController::class, 'templateAction'];
            if (isset($args['controller'])) {
                $controller = [$args['controller'], 'templateAction'];
            } else if (isset($args[0]) && count($args) == 2) {
                $controller = $args;
            }

            $options = self::buildTemplateRouteOptions($args, $options);
            $mux->add($path, $controller, $options);
        } else {

            throw new \LogicException("invalid route handler: " . var_export($args, true));

        }
    }

    protected static function buildTemplateRouteOptions(array $args, array $options)
    {
        if (!isset($args['template'])) {
            throw new \Exception("'template' is not configured.");
        }

        $options['args'] = [
            'template' => $args['template'],
            'template_args' => (isset($args['args']) ? $args['args'] : null),
        ];

        return $options;
    }



    /**
     * Route a path to template.
     *
     *    $this->page('/about.html', 'about.html' ,array( 'name' => 'foo' ));
     *
     * @param string $path
     * @param string $template file
     */
    public function page($path, $template, array $args = array())
    {
        $this->route($path, [
            'template' => $template,
            'args' => $args,  // template args
        ]);
    }


    /**
     *
     * @param string $path path name
     * @param string $class class name
     */
    public function mount($path, $className)
    {
        $class = $className;
        if (!class_exists($class, true)) {
            $class = $this->getNamespace() . '\\' . $className;
        }
        if (!class_exists($class, true)) {
            $class = $this->getNamespace() . '\\Controller\\' . $className;
        }
        // FIXME: Constructing controller requires environment and response varaibles
        $controller = new $class;
        $this->kernel->mux->mount($path, $controller);
    }

    public function expandRoute($path, $class)
    {
        @trigger_error('expandRoutes() is deprecated, please use mount() instead', E_USER_DEPRECATED);
        $this->mount($path, $class);
    }

    public function expandRoutes($path, $class)
    {
        @trigger_error('expandRoutes() is deprecated, please use mount() instead', E_USER_DEPRECATED);
        $this->mount($path, $class);
    }


}
