<?php
namespace Phifty\Service;

interface ServiceRegister
{

    public function getId();

    /**
     * register service
     */
    public function register($kernel, $options = array() );

}
