<?php
/*
 * This file is part of the Phifty package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace Phifty\Routing\Route;

use Exception;
use ReflectionObject;
use Phifty\Routing\Route;

class ControllerRoute extends Route
{

    public function evaluate()
    {
        $class  = $this->get('controller');
        $method = $this->get('method');

        $controller = new $class( $this );
        $controller->before();

        if ( ! $method || ! method_exists($controller,$method) ) {
            if ( $action = $this->get('action') )
                $method = $action . 'Action';
            elseif ( method_exists($controller,'run') )
                $method = 'run';
            elseif ( method_exists($controller,'indexAction') )
                $method = 'indexAction';
        }

        if ( method_exists($controller,$method) ) {
            $vars = $this->getVars();
            $ro = new ReflectionObject( $controller );
            $rm = $ro->getMethod($method);
            $parameters = $rm->getParameters();
            $arguments = array();
            foreach ($parameters as $param) {
                if ( isset( $vars[ $param->getName() ] ) ) {
                    $arguments[] = $vars[ $param->getName() ];
                } else {
                    $arguments[] = $this->getDefault( $param->getName() );
                }
            }
            $content = call_user_func_array( array($controller,$method) , $arguments );
        } else {
            header('HTTP/1.0 403');
            throw new Exception( "Controller action method $method of $class not found." );
        }

        $controller->after();

        return $content;
    }
}
