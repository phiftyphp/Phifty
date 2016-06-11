<?php
namespace Phifty\ServiceProvider;
use Phifty\Kernel;

class ClassLoaderServiceProvider extends BaseServiceProvider
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

    public function register(Kernel $kernel,$options = array())
    {
        $self = $this;
        $kernel->classloader = function() use ($self) {
            return $self->classloader;
        };
    }
}
