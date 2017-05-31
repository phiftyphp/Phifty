<?php

namespace Phifty\ServiceProvider;

use SessionKit;
use Phifty\Kernel;
use SessionKit\Session;
use SessionKit\State\NativeState;
use SessionKit\Storage\NativeStorage;

class SessionServiceProvider extends BaseServiceProvider
{
    public function getId()
    {
        return 'Session';
    }

    public function register(Kernel $kernel, $options = array())
    {
        // XXX: customize this for $options
        $kernel->session = function () {
            return new Session([
                'state' => new NativeState(),
                'storage' => new NativeStorage(),
            ]);
        };
    }
}
