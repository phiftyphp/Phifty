<?php
/**
 * This file is part of the Phifty package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
/*
define('PH_APP_ROOT', dirname(__DIR__));
define('PH_ROOT', dirname(__DIR__));
define('CLI', true);
*/

// FIXME: always unlink the bootstrap before we test it, can we ?
/*
require 'vendor/autoload.php';
if (file_exists('bootstrap.php')) {
    unlink('bootstrap.php');
}

$app = new \Phifty\Console\Application;
$app->run(['phifty', 'bootstrap']);
 */
require 'bootstrap.php';
