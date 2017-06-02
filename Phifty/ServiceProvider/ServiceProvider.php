<?php

namespace Phifty\ServiceProvider;

use Phifty\Kernel;

interface ServiceProvider
{
    public function getId();

    /**
     * register service.
     */
    public function register(Kernel $kernel, array $options = array());
}
