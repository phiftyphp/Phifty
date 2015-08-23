<?php
namespace Phifty\ServiceProvider;

interface ServiceProvider
{

    public function getId();

    /**
     * register service
     */
    public function register($kernel, $options = array() );

}
