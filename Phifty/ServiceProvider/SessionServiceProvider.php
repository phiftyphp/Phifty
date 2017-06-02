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

    public function register(Kernel $kernel, array $options = array())
    {
        // if we have session service provider, call the setup
        // $s = $kernel->session; // build session object and write to the buffer before we write data to the browser.
        if (!$kernel->isCli) {
            // TODO: customize this for $options
            $kernel->session = new Session([
                'state' => new NativeState(),
                'storage' => new NativeStorage(),
            ]);
        }
    }
}
