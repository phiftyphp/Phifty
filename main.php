<?php
define('PH_ROOT', '/Users/c9s/src/work/php/Phifty');
define('PH_APP_ROOT', '/Users/c9s/src/work/php/Phifty');
defined('DS') || define('DS', DIRECTORY_SEPARATOR);
global $composerClassLoader;
$composerClassLoader = require '/Users/c9s/src/work/php/Phifty/vendor/autoload.php';;
require '/Users/c9s/src/work/php/Phifty/vendor/corneltek/universal/src/Universal/Container/ObjectContainer.php';
require '/Users/c9s/src/work/php/Phifty/src/Phifty/Kernel.php';
require '/Users/c9s/src/work/php/Phifty/app/ConfigLoader.php';
require '/Users/c9s/src/work/php/Phifty/vendor/corneltek/universal/src/Universal/ClassLoader/SplClassLoader.php';
global $splClassLoader;
$splClassLoader = new \Universal\ClassLoader\SplClassLoader();
$splClassLoader->useIncludePath(false);
$splClassLoader->register(false);
require '/Users/c9s/src/work/php/Phifty/src/Phifty/Bootstrap.php';
