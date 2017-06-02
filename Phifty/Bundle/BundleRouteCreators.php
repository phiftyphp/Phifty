<?php

namespace Phifty\Bundle;

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
        $router = $this->kernel->mux;
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

            $router->add($path, array($class,$action), $options);

        } else if (is_array($args)) {

            // route to template controller ?
            if (isset($args['template'])) {
                $options['args'] = array(
                    'template' => $args['template'],
                    'template_args' => (isset($args['args']) ? $args['args'] : null),
                );
                $router->add($path, '\Phifty\Routing\TemplateController', $options);
            } elseif (isset($args['controller'])) { // route to normal controller ?

                $router->add($path, $args['controller'], $options);
            } elseif (isset($args[0]) && count($args) == 2) { // simply treat it as a callback

                $router->add($path, $args, $options);
            } else {
                throw new LogicException('Unsupport route argument.');
            }
            // throw new LogicException("Unsupported route argument.");
        }
    }


    /**
     * Route a path to template.
     *
     *    $this->page('/about.html', 'about.html' ,array( 'name' => 'foo' ));
     *
     * @param string $path
     * @param string $template file
     */
    public function page($path, $template, $args = array())
    {
        $router = $this->kernel->mux;
        $router->add($path, [
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
