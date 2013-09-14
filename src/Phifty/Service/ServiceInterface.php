<?php
namespace Phifty\Service;

interface ServiceInterface
{

    public function getId();

    /**
     * register service
     */
    public function register($kernel, $options = array() );

}
