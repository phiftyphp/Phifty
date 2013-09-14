<?php
namespace Phifty\Service;

abstract class BaseService
{
    public $options;

    abstract public function getId();

    /**
     * register service
     *
     * XXX: we should set options in constructor
     */
    abstract public function register($kernel, $options = array());
}
