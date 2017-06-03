<?php

namespace Phifty;

use ArrayObject;

use Universal\Http\HttpRequest;

use InvalidArgumentException;

use Phifty\Web;
use Phifty\Kernel;

class View extends ArrayObject
{
    protected $kernel;

    public function __construct(Kernel $kernel)
    {
        parent::__construct([], ArrayObject::ARRAY_AS_PROPS);
        $this->kernel = $kernel;

        // register args
        $this['Kernel']      = $kernel;
        $this['Request']     = new HttpRequest;
        $kernel->event->trigger('view.init', $this);
        $this->init();
    }

    public function init()
    {

    }

    /*
     * Assign template variable
     *
     * ->assign( array( key => value , key2 => value2 ) );
     * ->assign( key , value );
     *
     */
    public function assign()
    {
        $args = func_get_args();
        if ( is_array( $args[0] ) ) {
            foreach ($args[0] as $k => $v) {
                $this[ $k ] = $v;
            }
        } else if ( count($args) == 2 ) {
            list($name,$value) = $args;
            $this[ $name ] = $value;
        } else {
            throw new InvalidArgumentException( "Unknown assignment of " . __CLASS__ );
        }
    }

    /*
     * Get template arguments
     *
     * @return array template arguments
     */
    public function getArgs()
    {
        return $this;
    }

    /*
     * Setup template arguments
     *
     * @param array $args
     */
    public function setArgs(array $args)
    {
        $this->exchangeArray($args);
    }

    /*
     * Default render method, can be overrided from View\Engine\Twig or View\Engine\Smarty
     *
     * Render template file.
     * @param string $template template name
     */
    public function render($template)
    {
        $env = $this->kernel->twig->env;
        $args = $this->getArrayCopy();
        return $env->loadTemplate($template)->render($args);
    }

    public function __toString()
    {
        return $this->render();
    }
}
