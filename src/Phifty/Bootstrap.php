<?php
global $kernel;
/**
 * kernel() is a global shorter helper function to get Phifty\Kernel instance.
 *
 * Initialize kernel instance, classloader, bundles and services.
 *
 * @return Phifty\Kernel
 */
function kernel()
{
    global $kernel;
    return $kernel;
}
