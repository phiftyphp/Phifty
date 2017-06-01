<?php
namespace Phifty\Environment;

use Phifty\Kernel;

class CommandLine
{
    public static function init(Kernel $kernel)
    {
        ini_set('output_buffering ', '0');
        ini_set('implicit_flush', '1');
        ob_implicit_flush(true);
    }
}
