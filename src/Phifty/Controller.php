<?php
namespace Phifty;
use Universal\Http\HttpRequest;
use Exception;
use InvalidArgumentException;
use ReflectionObject;
use Symfony\Component\Yaml\Yaml;
use Pux\Controller\Controller as PuxController;
use Pux\Expandable;

class Controller extends PuxController
{
    /**
     * @var Phifty\View view object cache
     */
    protected $_view;

    public $defaultViewClass;

    public function __get($name)
    {
        if ('request' === $name) {
            return $this->getRequest();
        } else {
            throw new InvalidArgumentException( $name );
        }
    }

    public function getCurrentUser()
    {
        return kernel()->currentUser;
    }

    /**
     * xxx: is not used yet.
     *
     * You may customize the permission check
     */
    public function currentUserCan($user)
    {
        return true;
    }


    /**
     * some response header util methods
     */
    public function setHeader($field, $value)
    {
        $this->response[1][ $field ] = $value;
    }


    /**
     * response header util method let you append headers.
     *
     * @param string $field
     * @param string $value
     */
    public function appendHeader($field, $value)
    {
        $this->response[1][] = [$field => $value];
    }



    /**
     * Create/Get view object with rendering engine options
     *
     * @param array $options
     *
     * @return Phifty\View
     */
    public function view(array $options = array())
    {
        if ($this->_view) {
            if (!empty($options)) {
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
        $this->setHeader('Location', $url);
    }

    public function redirectLater($url,$seconds = 1 )
    {
        $this->setHeader('Refresh', "$seconds; url=$url");
    }

    /* Move this into Agent class */
    public function isMobile()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        return preg_match( '/(ipad|iphone|android)/i' ,$agent );
    }

    /**
     * Tell browser dont cache page content
     */
    public function setHeaderNoCache()
    {
        // require HTTP/1.1
        $this->setHeader("Cache-Control", "no-cache, must-revalidate");
    }

    /*
     * Set cache expire time
     */
    public function setHeaderCacheTime( $time = null )
    {
        $datestr = gmdate(DATE_RFC822, $time );
        // header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
        header("Expires: $datestr");
    }

    public function toJson($data, $encodeFlags = null)
    {
        if (! CLI_MODE) {
            header('Content-type: application/json; charset=UTF-8');
        }
        return parent::toJson($data, $encodeFlags);
    }

    public function toYaml($data, $encodeFlags = null)
    {
        if (! CLI_MODE) {
            header('Content-type: application/yaml; charset=UTF-8;');
        }

        // If we've loaded the yaml extension, we should use it directly.
        if (extension_loaded('yaml') ){
            return yaml_emit($data, $encodeFlags);
        }
        return Yaml::dump($data, $encodeFlags);
    }

    /**
     * Render template directly.
     *
     * @param string $template template path, template name
     * @param array  $args     template arguments
     * @return string rendered result
     */
    public function render($template, array $args = array(), array $engineOpts = array())
    {
        $view = $this->view( $engineOpts );
        $view->assign( $args );
        return $view->render( $template );
    }

    /**
     * Set HTTP status to 403 forbidden with message
     *
     * @param string $msg
     */
    public function forbidden($msg = '403 Forbidden')
    {
        /* XXX: dirty hack this for phpunit testing */
        if ( ! CLI_MODE ) {
            header('HTTP/1.1 403 Forbidden');
        }
        echo $msg;
        exit(0);
    }



    /**
     * Run controller action
     *
     * @param string $action Action name, the action name should not include "Action" as its suffix.
     * @param array $vars    Action method parameters, which will be applied to the method parameters by their names.
     * @return string        Return execution result in string format.
     */
    public function runAction($action, $vars = array())
    {
        $method = $action . 'Action';
        if ( ! method_exists($this,$method) ) {
            throw new Exception("Controller method $method does not exist.");
        }

        // Trigger the before action
        $this->before();

        $ro = new ReflectionObject( $this );
        $rm = $ro->getMethod($method);

        // Map vars to function arguments
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
     * Forward to another controller action
     *
     * @param string|Controller $controller A controller class name or a controller instance.
     * @param string            $actionName The action name
     * @param array             $parameters Parameters for the action method
     *
     *
     *  return $this->forward('\OAuthPlugin\Controller\AuthenticationErrorPage','index',array(
     *      'vars' => array(
     *          'message' => $e->lastResponse
     *      )
     *  ));
     */
    public function forward($controller, $actionName = 'index' , $parameters = array())
    {
        if (is_string($controller)) {
            $controller = new $controller;
        }
        return $controller->runAction($actionName, $parameters);
    }

    /**
     * Check if the controller action exists
     *
     * @param  string  $action action name
     * @return boolean
     */
    public function hasAction($action)
    {
        if (method_exists($this, $action . 'Action')) {
            return $action . 'Action';
        }
        return false;
    }
}
