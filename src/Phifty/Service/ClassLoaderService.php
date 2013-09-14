<?php
namespace Phifty\Service;

class ClassLoaderService
    implements ServiceInterface
{
    public $classloader;

    public function __construct($classloader)
    {
        $this->classloader = $classloader;
    }

    public function getId() { return 'classloader'; }

    public function setClassLoader($classloader)
    {
        $this->classloader = $classloader;
    }

    public function register($kernel,$options = array())
    {
        $self = $this;
        $kernel->classloader = function() use ($self) {
            return $self->classloader;
        };
    }
}
