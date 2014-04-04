<?php
/**
 * Front-end script for nginx/apache/fastcgi http server
 *
 * @author c9s <cornelius.howl@gmail.com>
 * @package Phifty
 */
require '../main.php';
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
kernel()->handle($pathinfo);
