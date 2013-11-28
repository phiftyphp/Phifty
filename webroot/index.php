<?php
/**
 * Front-end script for nginx/apache/fastcgi http server
 *
 * @author c9s <cornelius.howl@gmail.com>
 * @package Phifty
 */
if (php_sapi_name() == 'cli-server') {
    $uri = $_SERVER['REQUEST_URI'];
    $info = parse_url($uri);
    if (preg_match('/\.(?:png|jpg|jpeg|gif|js|css|pdf|ppt)$/', $info['path'] ))
        return false;    // serve the requested resource as-is.
    $path = ltrim($info['path'],'/');
    if( file_exists($path) )
        return false;
    $pathinfo = $info['path'];
} else {
    $pathinfo = isset($_SERVER['PATH_INFO']) && $_SERVER["PATH_INFO"] ? $_SERVER['PATH_INFO'] : '/';
}

try {
    require '../main.php';

    // allow origin: https://developer.mozilla.org/en-US/docs/HTTP/Access_control_CORS
    header( 'Access-Control-Allow-Origin: http://' . $_SERVER['HTTP_HOST'] );

    $kernel = kernel();
    $kernel->event->trigger('phifty.before_path_dispatch');
    if( $r = $kernel->router->dispatch( $pathinfo ) ) {
        $kernel->event->trigger('phifty.before_page');
        echo $r->run();
        $kernel->event->trigger('phifty.after_page');
    } 
    else {
        // header('HTTP/1.0 404 Not Found');
        echo "<h3>Page not found.</h3>";
    }

}
#  catch ( Twig_Error_Runtime $e ) {
#      # twig error exception
#  
#  }
catch ( Exception $e ) {
    if( kernel()->isDev ) 
    {
        if( class_exists('Core\\Controller\\ExceptionController',true) ) {
            $controller = new Core\Controller\ExceptionController;
            echo $controller->indexAction($e);
        } else {
            // simply throw exception
            throw $e;
        }
    }
    else {
        header('HTTP/1.1 500 Internal Server Error');
        die($e->getMessage());
    }
}
catch ( Roller\Exception\RouteException $e ) {
    header('HTTP/1.1 403');
    die( $e->getMessage() );
}

