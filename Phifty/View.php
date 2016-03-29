<?php
namespace Phifty;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Universal\Http\HttpRequest;
use InvalidArgumentException;
use Phifty\Web;
use Phifty\Kernel;
use Phifty\View\Engine;

class View implements ArrayAccess, IteratorAggregate
{
    /**
     * @var array template args
     */
    protected $args = array();

    protected $kernel;

    protected $engine;

    public function __construct(Kernel $kernel, $engineOpts = null)
    {
        $this->kernel = $kernel;
        $this->engine = Engine::createEngine($this->kernel, $engineOpts);
        $this->init();

        // register args
        $this->args['Kernel']      = $kernel;
        $this->args['Request'] = new HttpRequest;

        // helper functions
        // TODO: refactor to event
        $this->args['Web']         = new Web;

        kernel()->event->trigger('view.init', $this);
    }

    public function init()
    {

    }

    public function __set($name , $value)
    {
        $this->args[$name] = $value;
    }

    public function __get($name)
    {
        if (isset($this->args[$name])) {
            return $this->args[ $name ];
        }
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
                $this->args[ $k ] = $v;
            }
        } elseif ( count($args) == 2 ) {
            list($name,$value) = $args;
            $this->args[ $name ] = $value;
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
        return $this->args;
    }

    /*
     * Setup template arguments
     *
     * @param array $args
     */
    public function setArgs($args)
    {
        $this->args = $args;
    }

    public function getEngine()
    {
        return $this->engine;
    }

    /*
     * Default render method, can be overrided from View\Engine\Twig or View\Engine\Smarty
     *
     * Render template file.
     * @param string $template template name
     */
    public function render($template)
    {
        return $this->engine->render( $template , $this->args );
    }

    /*
     * Render template from string
     * @param string $stringTemplate template content
     * */
    public function renderString( $stringTemplate )
    {
        return $this->engine->renderString( $stringTemplate , $this->args );
    }

    /*
     * Call render method to render
     */
    public function __toString()
    {
        return $this->render();
    }

    public function offsetSet($name,$value)
    {
        $this->args[ $name ] = $value;
    }

    public function offsetExists($name)
    {
        return isset($this->args[ $name ]);
    }

    public function offsetGet($name)
    {
        return $this->args[ $name ];
    }

    public function offsetUnset($name)
    {
        unset($this->args[$name]);
    }

    public function getIterator()
    {
        return new ArrayIterator( $this->args );
    }

}
