<?php
namespace Phifty\Environment;

class CommandLine
{
    public static function init($kernel)
    {
        if ($kernel->isCLI) {
            ini_set('output_buffering ', '0');
            ini_set('implicit_flush', '1');
            ob_implicit_flush(true);
        } else {
            ob_start();
            $s = $kernel->session; // build session object
        }

    }
}
