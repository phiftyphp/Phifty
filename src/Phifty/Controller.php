<?php
namespace Phifty;
use Universal\Http\HttpRequest;
use SerializerKit;
use SerializerKit\YamlSerializer;
use Exception;
use InvalidArgumentException;
use Roller\Controller as BaseController;
use ReflectionObject;

/*
    Synopsis
    $controller = new $class( $this );
*/

class Controller extends BaseController
{

    /**
     * @var HttpRequest request object for cache
     */
    protected $_request;


    /**
     * @var Phifty\View view object cache
     */
    protected $_view;

    public $defaultViewClass;

    public function init()
    {
    }

    public function __get($name)
    {
        if ( 'request' === $name ) {
            if ( $this->_request ) {
                return $this->_request;
            }
            return $this->_request = new HttpRequest;
        } else {
            throw new InvalidArgumentException( $name );
        }
    }

    public function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function getInputContent()
    {
        return file_get_contents('php://input');
    }

    public function getCurrentUser()
    {
        return kernel()->currentUser;
    }

    /**
     * xxx: is not used yet.
     *
     * currentUserCan method
     *
     * provide a permission check.
     */
    public function currentUserCan($user)
    {
        return true;
    }

    /**
     * Create/Get view object with rendering engine options
     *
     * @param array $options
     *
     * @return Phifty\View
     */
    public function view( $options = array() )
    {
        if ($this->_view) {
            if ( $options ) {
                throw new Exception("The View object is already initialized.");
            }
            return $this->_view;
        }
        // call the view object factory from service
        return $this->_view = kernel()->getObject('view',array($this->defaultViewClass));
    }

    /**
     * Create view object with custom view class
     *
     * @param string $class
     * @param array  $options
     */
    public function createView($viewClass = null)
    {
        return kernel()->getObject('view',array($viewClass));
    }

    /**
     * Web utils functions
     * */
    public function redirect($url)
    {
        header( 'Location: ' . $url );
    }

    public function redirectLater($url,$seconds = 1 )
    {
        header( "refresh: $seconds; url=" . $url );
    }

    /* Move this into Agent class */
    public function isMobile()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];

        return preg_match( '/(ipad|iphone|android)/i' ,$agent );
    }

    /*
     * Tell browser dont cache page content
     */
    public function setHeaderNoCache()
    {
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
    }

    /*
     * Set cache expire time
     */
    public function setHeaderCacheTime( $time = null )
    {
        $datestr = gmdate(DATE_RFC822, $time );
        // header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
        header( "Expires: $datestr" );
    }

    /*
     * Render json content
     *
     * @param array $data
     *
     */
    public function renderJson($data)
    {
        /* XXX: dirty hack this for phpunit testing */
        if ( ! CLI_MODE )
            header('Content-type: application/json; charset=UTF-8');

        return json_encode($data);
    }

    public function toJson($data)
    {
        return $this->renderJson($data);
    }

    /*
     * Render yaml
     *
     **/
    public function renderYaml($data)
    {
        if ( ! CLI_MODE )
            header('Content-type: application/yaml; charset=UTF-8;');
        $yaml = new YamlSerializer;

        return $yaml->encode($data);
    }

    public function toYaml($data)
    {
        return $this->renderYaml( $data );
    }

    /**
     * Render page content
     *
     *     $this->renderPage( 'ViewPageClass' , array(
     *          'i18n' => 1,
     *          'layout' => 'layout.html',
     *          'content' => 'content.html' ) );
     *
     */
    public function renderPage( $viewClass , $options = array() , $args = array() )
    {
        $page = new $viewClass( $options );
        $page->setArgs( $args );

        return $page->render();
    }

    /*
     * Render template directly.
     *
     * @param string $template template path, template name
     * @param array  $args     template arguments
     *
     */
    public function render( $template , $args = array() , $engineOpts = array()  )
    {
        $view = $this->view( $engineOpts );
        $view->assign( $args );
        return $view->render( $template );
    }

    public function forbidden($msg = null)
    {
        /* XXX: dirty hack this for phpunit testing */
        if ( ! CLI_MODE )
            header('HTTP/1.1 403 Forbidden');
        if ( $msg ) echo $msg;
        else       echo "403 Forbidden";
        exit(0);
    }


    public function runAction($action, $vars = array() )
    {
        $method = $action . 'Action';
        if ( ! method_exists($this,$method) ) {
            throw new Exception("Controller method $method does not exist.");
        }

        // Trigger the before action
        $this->before();

        $ro = new ReflectionObject( $this );
        $rm = $ro->getMethod($method);

        // apply vars to function arguments
        $parameters = $rm->getParameters();
        $arguments = array();
        foreach ($parameters as $param) {
            if ( isset( $vars[ $param->getName() ] ) ) {
                $arguments[] = $vars[ $param->getName() ];
            }
        }
        $ret = call_user_func_array( array($this,$method) , $arguments );

        // Trigger the after action
        $this->after();
        return $ret;
    }


    /**
     * forward to another controller
     *
     *
     *  return $this->forward( '\OAuthPlugin\Controller\AuthenticationErrorPage','index',array(
     *      'vars' => array(
     *          'message' => $e->lastResponse
     *      )
     *  ));
     */
    public function forward($controller, $action = 'index' , $parameters = array())
    {
        if ( is_string($controller) ) {
            $controller = new $controller;
        }
        $ret = $controller->runAction($action, $parameters);
        return $ret;
    }

    /**
     * check if the controller action exists
     *
     * @param  string  $action action name
     * @return boolean
     */
    public function hasAction($action)
    {
        if ( method_exists($this,$action . 'Action') ) {
            return $action . 'Action';
        }
        return false;
    }

}
