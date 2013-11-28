<?php
/**
 * php -S localhost:8000 server.php
 */
if (preg_match('/\.(?:png|jpg|jpeg|gif|js|css)$/', $_SERVER["REQUEST_URI"])) {
    return false;    // serve the requested resource as-is.
} else { 
    if( getenv('XHPROF_ENABLE') ) {
        require 'run_xhprof.php';
    }
    else {
        require 'index.php';
    }
}
