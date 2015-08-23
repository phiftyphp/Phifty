<?php
namespace Phifty\ServiceProvider;

class CoreServiceProvider
    implements ServiceProvider
{
    public function getId() { return 'core'; }

    public function register($kernel, $options = array() )
    {

    }
}
